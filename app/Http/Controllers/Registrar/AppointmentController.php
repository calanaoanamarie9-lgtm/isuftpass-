<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\AppointmentStatus;
use App\Enums\Office;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveScheduleRequest;
use App\Mail\AppointmentApproved;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Notifications\AppointmentApprovedNotification;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentRescheduledNotification;
use App\Support\AppointmentList;
use App\Support\AuditLogger;
use App\Support\SafeMailer;
use App\Support\TimeSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * Staff management list of student appointments for the account's office.
     *
     * Supports filtering by user classification (student/alumni/parent/guest)
     * via the ?user_type= query parameter.
     */
    public function index(Request $request): View
    {
        return view('registrar.appointments.index', [
            ...AppointmentList::for(request()->user(), $request),
            'prefix' => 'registrar',
        ]);
    }

    public function show(Request $request, Appointment $appointment): View
    {
        Gate::authorize('view', $appointment);

        return view('registrar.appointments.show', [
            'appointment' => $appointment->load('user'),
            'prefix' => $this->prefix(),
        ]);
    }

    /**
     * The routes the page answers to, named for whoever is reading it.
     *
     * The same pages are read by the registrar and by the eight office
     * workspaces, and each of them has its own set of routes - so the page
     * takes the prefix of the route it was opened on, and every link and
     * every fetch it makes stays inside that account's own group.
     */
    private function prefix(): string
    {
        $name = request()->route()?->getName() ?? '';

        return Str::before($name, '.') ?: 'registrar';
    }

    public function printAll()
    {
        return redirect()->route('registrar.appointments.index');
    }

    /**
     * Availability checker (JSON): open time intervals for an office + date.
     * Excludes the appointment being rescheduled from the count.
     */
    public function slots(Request $request)
    {
        $data = $request->validate([
            'office' => ['required', Rule::in(Office::toSelectKeys())],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'ignore_id' => ['nullable', 'integer'],
        ]);

        // Every workspace now reaches this endpoint, so it no longer suffices
        // that the caller is staff: only the registrar (and the System Admin)
        // reads across offices. A department or office account may read
        // capacity for its own office — enforced by the policy.
        Gate::authorize('readOffice', [Appointment::class, $data['office']]);

        return response()->json(Appointment::availableSlots($data['office'], $data['date'], $data['ignore_id'] ?? null));
    }

    /**
     * Registrar-triggered reschedule: pick a new date and an open time slot.
     * The system re-checks capacity, records the original date, and emails the student.
     */
    public function reschedule(Request $request, Appointment $appointment): View
    {
        Gate::authorize('manage', $appointment);

        return view('registrar.appointments.reschedule', [
            'appointment' => $appointment->load('user'),
            'offices' => Office::toSelectKeys(),
        ]);
    }

    public function updateReschedule(Request $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        // Rescheduling someone else's office is the registrar's (or the
        // System Admin's) call. Workspace accounts reach this route too, so
        // the policy pins them to their own office — the appointment list
        // they came from is already scoped that way.
        Gate::authorize('manage', $appointment);

        abort_if(
            in_array($appointment->status, [
                AppointmentStatus::COMPLETED->value,
                AppointmentStatus::CANCELLED->value,
                AppointmentStatus::NO_SHOW->value,
            ], true),
            422,
            'This appointment can no longer be rescheduled.'
        );

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['nullable', 'date_format:H:i'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'time_slot' => ['nullable', Rule::in(TimeSlots::forOffice($appointment->office))],
            'reschedule_reason' => ['required', 'string', 'max:500'],
        ]);

        $hours = TimeSlots::hoursFor($appointment->office);

        // The window the student is told to turn up in: an hour, not one of
        // the office's numbered slots. The registrar answers "come at 8:40,
        // and you are done by 9:00", and the range is that.
        $start = $data['start_time'] ?? $data['time'] ?? null;
        $end = $data['end_time'] ?? null;

        if ($start !== null) {
            if ($start < $hours['open'] || $start > $hours['close']) {
                throw ValidationException::withMessages([
                    'start_time' => 'The time has to sit inside office hours ('
                        .self::clock($hours['open']).' - '.self::clock($hours['close']).').',
                ]);
            }

            if ($end !== null && $end <= $start) {
                throw ValidationException::withMessages([
                    'end_time' => 'The window has to run forward - the end must be after the start.',
                ]);
            }

            if ($end !== null && $end > $hours['close']) {
                throw ValidationException::withMessages([
                    'end_time' => 'The window has to sit inside office hours ('
                        .self::clock($hours['open']).' - '.self::clock($hours['close']).').',
                ]);
            }
        }

        // The office's own hour that holds the window is the seat the
        // booking takes, so it still counts against that hour's capacity.
        $slot = $data['time_slot'] ?? ($start !== null
            ? $this->slotFor($appointment->office, $start)
            : $appointment->time_slot);

        if ($slot !== null) {
            $remaining = Appointment::remainingSlots($appointment->office, $data['date'], $slot, $appointment->id);

            if ($remaining < 1) {
                throw ValidationException::withMessages([
                    // A slot the request named is the slot's own fault; one
                    // the booking carried is the day that is wrong.
                    $data['time_slot'] !== null ? 'time_slot' : 'date' => 'The '.$slot.' slot is already full on that date. Pick another day.',
                ]);
            }
        }

        $appointment->update([
            'original_date' => $appointment->original_date ?? $appointment->date,
            'original_time_slot' => $appointment->original_time_slot ?? $appointment->time_slot,
            'date' => $data['date'],
            'time_slot' => $slot,
            'start_time' => $start ?? $appointment->start_time,
            'end_time' => $end ?? $appointment->end_time,
            'confirmed_time' => $start !== null ? self::clock($start) : $appointment->confirmed_time,
            'reschedule_reason' => $data['reschedule_reason'],
            'status' => AppointmentStatus::RESCHEDULED->value,
            'confirmed_at' => null,
            'rescheduled_at' => now(),
        ]);

        $oldSchedule = ($appointment->original_date ?? $appointment->date)->format('F j, Y')
            .' · '
            .($appointment->original_time_slot ?? $appointment->time_slot);

        SafeMailer::send($appointment->user, new AppointmentRescheduled($appointment, $oldSchedule));
        $appointment->user->notify(new AppointmentRescheduledNotification($appointment));

        AuditLogger::log('appointment.rescheduled', 'Rescheduled appointment '.$appointment->reference_code.' to '.$data['date'].' ('.($slot ?? 'hour to be set').').');

        // The registrar works from the appointment record itself; each of the
        // eight workspaces has its own appointment list instead. Sending a
        // workspace to registrar.appointments.show would land it on a page its
        // role cannot open — it would 403 one screen after succeeding — so an
        // office account goes back to its own list, and everyone else to the
        // record they just changed.
        $redirect = route('registrar.appointments.show', $appointment);

        if (in_array(auth()->user()->role, ['department', 'ssc', 'accounting', 'library', 'office'], true)) {
            $office = strtolower(auth()->user()->officeScope());

            foreach ([$office, 'workspace'] as $candidate) {
                if (Route::has($candidate.'.appointments.index')) {
                    $redirect = route($candidate.'.appointments.index');

                    break;
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'redirect' => $redirect,
                'message' => 'Appointment rescheduled. The student has been notified.',
            ]);
        }

        return redirect()
            ->to($redirect)
            ->with('status', 'Appointment rescheduled. The student has been notified by email and in the system.');
    }

    /**
     * Approve a pending / for-reschedule appointment, guarding slot capacity.
     *
     * The student asked for a slot; approving is the office answering with the
     * time it actually wants them there. The registrar picks that time here,
     * and the student is told it by email and in the system — an approval they
     * never hear about is not an approval.
     */
    public function confirm(ApproveScheduleRequest $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        // Workspace accounts reach this route too — the policy pins them to
        // their own office; only the registrar and the System Admin confirm
        // across offices. The FormRequest checks this same ability before
        // validation runs; the line here is what the method itself defends.
        Gate::authorize('manage', $appointment);

        // "Edit" on an already-approved booking reopens this same form to
        // move the window, so a confirmed appointment may be answered again.
        // Every other state stays locked.
        abort_if(
            ! in_array($appointment->status, [
                AppointmentStatus::PENDING->value,
                AppointmentStatus::FOR_RESCHEDULE->value,
                AppointmentStatus::CONFIRMED->value,
            ], true),
            422,
            'Only pending, for-reschedule or approved appointments can be approved.'
        );

        $data = $request->validated();

        // The registrar page sends a whole window and the day to hold it on;
        // the office dashboards still send the single legacy time. Both
        // reduce to a start, an optional end, and a date — the date the
        // appointment already carries when no new one was asked for.
        $start = $data['start_time'] ?? $data['confirmed_time'];
        $end = $data['end_time'] ?? null;
        $date = $data['scheduled_date'] ?? $appointment->date->toDateString();
        $alreadyApproved = $appointment->status === AppointmentStatus::CONFIRMED->value;

        // The hour the office gives the booking decides which of its hours
        // it sits in, and that hour is the seat it takes: a student asks
        // for a day, and the office answers with an hour, so the hour is
        // where the capacity is weighed. An appointment answering itself
        // never blocks its own edit, so it is left out of the count.
        $slot = $this->slotFor($appointment->office, $start) ?? $appointment->time_slot;

        if ($slot !== null) {
            $remaining = Appointment::remainingSlots(
                $appointment->office,
                $date,
                $slot,
                $alreadyApproved ? $appointment->id : null
            );

            if ($remaining < 1) {
                $full = 'The time slot is already at maximum capacity. Reschedule this appointment to an open slot first.';

                if ($request->wantsJson()) {
                    return response()->json(['message' => $full], 422);
                }

                return back()->with('error', $full);
            }
        }

        $appointment->update([
            'status' => AppointmentStatus::CONFIRMED->value,
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            // The hour the booking now sits in, so the day's seats are
            // counted against it from here on.
            'time_slot' => $slot,
            // The pass card, both emails and every notification read this one
            // string as the time to arrive, so it stays the start of the
            // window — the range lives beside it.
            'confirmed_time' => self::clock($start),
            'confirmed_at' => now(),
            'approved_by' => $request->user()?->id,
        ]);

        // The student booked a date and a hoped-for slot; this is the office
        // telling them when to actually arrive. An edit sends it again — the
        // time they were told about has moved.
        SafeMailer::send($appointment->user, new AppointmentApproved($appointment));
        $appointment->user->notify(new AppointmentApprovedNotification($appointment));

        $answer = $end !== null
            ? 'between '.self::clock($start).' and '.self::clock($end)
            : 'at '.$appointment->confirmed_time;

        AuditLogger::log(
            'appointment.confirmed',
            'Confirmed appointment '.$appointment->reference_code.' for '.$appointment->date->toDateString()
                .', student to come '.$answer.'.'
        );

        $status = 'Appointment approved. '.$appointment->user->name.' has been told to come '.$answer.'.';

        // The approve popover submits by fetch and reads the answer as JSON,
        // so it can show its own success alert instead of a redirect flash.
        if ($request->wantsJson()) {
            return response()->json([
                'redirect' => route('registrar.appointments.show', $appointment),
                'message' => $status,
            ]);
        }

        return back()->with('status', $status);
    }

    /**
     * An H:i clock time spelled the way the office reads it: 09:00 -> 9:00 AM.
     */
    private static function clock(string $time): string
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return sprintf('%d:%02d %s', $hour % 12 ?: 12, $minute, $hour < 12 ? 'AM' : 'PM');
    }

    /**
     * The office's own hour that holds a given clock: the slot the booking
     * sits in, and so the seat it takes on that day.
     *
     * An office whose hours do not cover that clock gets none - the
     * booking still has a time, it simply does not take up one of the
     * office's numbered hours.
     */
    private function slotFor(string $office, string $start): ?string
    {
        foreach (TimeSlots::forOffice($office) as $label) {
            [$from, $to] = array_pad(explode(' - ', $label, 2), 2, '');

            $fromTime = self::twentyFour($from);
            $toTime = self::twentyFour($to);

            if ($fromTime === null || $toTime === null) {
                continue;
            }

            if ($start >= $fromTime && $start < $toTime) {
                return $label;
            }
        }

        return null;
    }

    /**
     * A clock the office reads ("9:00 AM") as the H:i the system stores
     * ("09:00"). Null when the label is not a clock at all.
     */
    private static function twentyFour(string $label): ?string
    {
        if (! preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', trim($label), $matches)) {
            return null;
        }

        $hour = ((int) $matches[1]) % 12 + (strtoupper($matches[3]) === 'PM' ? 12 : 0);

        return sprintf('%02d:%02d', $hour, (int) $matches[2]);
    }

    /**
     * Registrar-side cancellation of any active appointment.
     */
    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        // The registrar group's middleware also admits department accounts,
        // so the policy decides: the registrar and the System Admin cancel
        // anywhere, an office account only its own bookings.
        Gate::authorize('manage', $appointment);

        abort_if(
            in_array($appointment->status, [
                AppointmentStatus::COMPLETED->value,
                AppointmentStatus::CANCELLED->value,
            ], true),
            422,
            'This appointment can no longer be cancelled.'
        );

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ]);

        $appointment->user->notify(new AppointmentCancelledNotification($appointment));

        AuditLogger::log('appointment.cancelled', 'Cancelled appointment '.$appointment->reference_code.'.');

        return back()->with('status', 'Appointment cancelled. The student has been notified.');
    }
}
