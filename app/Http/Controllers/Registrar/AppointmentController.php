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
use App\Support\AuditLogger;
use App\Support\SafeMailer;
use App\Support\TimeSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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
        Gate::authorize('viewAny', Appointment::class);

        // The filter speaks the office's language: "Approved" is the label the
        // page prints for a stored "confirmed" status, so the option is turned
        // back into the value the column actually holds.
        $statusFilter = $request->query('status');
        $statusFilter = is_string($statusFilter) ? $statusFilter : null;

        $userTypeFilter = $request->query('user_type');
        $userTypeFilter = is_string($userTypeFilter) ? $userTypeFilter : null;

        $status = [
            'pending' => AppointmentStatus::PENDING->value,
            'approved' => AppointmentStatus::CONFIRMED->value,
            'rescheduled' => AppointmentStatus::RESCHEDULED->value,
            'cancelled' => AppointmentStatus::CANCELLED->value,
        ][$statusFilter] ?? null;

        // The System Admin answers for every office, so their appointment
        // list is not narrowed to one - the list names the office on each
        // row. A registrar or an office account still sees only its own.
        $appointmentsQuery = Appointment::with('user')
            ->when(
                auth()->user()->role !== 'admin',
                fn ($query) => $query->where('office', auth()->user()->officeScope())
            )
            ->when($status, fn ($query) => $query->where('status', $status));

        // Applicant classification filter. "Student" is every account that
        // signed up as a student (registration_type is left null for them);
        // alumni / parent / guest were typed into registration_type at signup.
        $userTypes = [
            'student' => 'Student',
            'alumni' => 'Alumni',
            'parent' => 'Parent',
            'guest' => 'Guest',
        ];

        $userTypeFilter = is_string($userTypeFilter) && array_key_exists($userTypeFilter, $userTypes)
            ? $userTypeFilter
            : null;

        if ($userTypeFilter === 'student') {
            $appointmentsQuery->whereHas('user', fn ($q) => $q->whereNull('registration_type'));
        } elseif ($userTypeFilter !== null) {
            // "Parent" also covers the legacy 'guardian' registration_type.
            $registrations = $userTypeFilter === 'parent' ? ['parent', 'guardian'] : [$userTypeFilter];
            $appointmentsQuery->whereHas('user', fn ($q) => $q->whereIn('registration_type', $registrations));
        }

        $appointments = $appointmentsQuery
            ->firstComeFirstServed()
            ->paginate(12)
            ->withQueryString();

        return view('registrar.appointments.index', [
            'appointments' => $appointments,
            'statusFilter' => $statusFilter,
            'userTypeFilter' => $userTypeFilter,
        ]);
    }

    public function show(Request $request, Appointment $appointment): View
    {
        Gate::authorize('view', $appointment);

        return view('registrar.appointments.show', [
            'appointment' => $appointment->load('user'),
        ]);
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
            'time_slot' => ['required', Rule::in(TimeSlots::forOffice($appointment->office))],
            'reschedule_reason' => ['required', 'string', 'max:500'],
        ]);

        $remaining = Appointment::remainingSlots($appointment->office, $data['date'], $data['time_slot'], $appointment->id);

        if ($remaining < 1) {
            throw ValidationException::withMessages([
                'time_slot' => 'The selected time slot is fully booked. Pick another open slot.',
            ]);
        }

        $appointment->update([
            'original_date' => $appointment->original_date ?? $appointment->date,
            'original_time_slot' => $appointment->original_time_slot ?? $appointment->time_slot,
            'date' => $data['date'],
            'time_slot' => $data['time_slot'],
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

        AuditLogger::log('appointment.rescheduled', 'Rescheduled appointment '.$appointment->reference_code.' to '.$data['date'].' ('.$data['time_slot'].').');

        // The registrar works from the appointment record itself; each of the
        // eight workspaces has its own appointment list instead. Sending a
        // workspace to registrar.appointments.show would land it on a page its
        // role cannot open — it would 403 one screen after succeeding — so go to
        // whichever index actually exists for this account.
        $workspaceIndex = strtolower(auth()->user()->officeScope()).'.appointments';
        $redirect = Route::has($workspaceIndex)
            ? route($workspaceIndex)
            : route('registrar.appointments.show', $appointment);

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

        // Offices that set the time themselves (the Registrar) carry no
        // requested slot, so there is no slot capacity to weigh here — the
        // day's own queue handles that. An appointment answering itself never
        // blocks its own edit, so it is left out of the count.
        if ($appointment->time_slot !== null) {
            $remaining = Appointment::remainingSlots(
                $appointment->office,
                $date,
                $appointment->time_slot,
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
