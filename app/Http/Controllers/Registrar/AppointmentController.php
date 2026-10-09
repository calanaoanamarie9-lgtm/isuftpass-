<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\AppointmentStatus;
use App\Enums\Office;
use App\Http\Controllers\Controller;
use App\Mail\AppointmentApproved;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Notifications\AppointmentApprovedNotification;
use App\Notifications\AppointmentRescheduledNotification;
use App\Support\AuditLogger;
use App\Support\SafeMailer;
use App\Support\TimeSlots;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * Staff management list of student appointments for the account's office.
     */
    public function index(): View
    {
        $appointments = Appointment::with('user')
            ->where('office', auth()->user()->officeScope())
            ->firstComeFirstServed()
            ->paginate(12);

        return view('registrar.appointments.index', [
            'appointments' => $appointments,
        ]);
    }

    public function show(Request $request, Appointment $appointment): View
    {
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
        // that the caller is staff: only the registrar reads across offices.
        // A department or office account may read capacity for its own office.
        abort_unless(
            auth()->user()?->role === 'registrar'
                || $data['office'] === auth()->user()->officeScope(),
            403
        );

        return response()->json(Appointment::availableSlots($data['office'], $data['date'], $data['ignore_id'] ?? null));
    }

    /**
     * Registrar-triggered reschedule: pick a new date and an open time slot.
     * The system re-checks capacity, records the original date, and emails the student.
     */
    public function reschedule(Request $request, Appointment $appointment): View
    {
        return view('registrar.appointments.reschedule', [
            'appointment' => $appointment->load('user'),
            'offices' => Office::toSelectKeys(),
        ]);
    }

    public function updateReschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        // Rescheduling someone else's office is the registrar's call. Workspace
        // accounts reach this route too, so pin them to their own office — the
        // appointment list they came from is already scoped that way.
        abort_unless(
            auth()->user()?->role === 'registrar'
                || $appointment->office === auth()->user()->officeScope(),
            403
        );

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
            . ' · '
            . ($appointment->original_time_slot ?? $appointment->time_slot);

        SafeMailer::send($appointment->user, new AppointmentRescheduled($appointment, $oldSchedule));
        $appointment->user->notify(new AppointmentRescheduledNotification($appointment));

        AuditLogger::log('appointment.rescheduled', 'Rescheduled appointment ' . $appointment->reference_code . ' to ' . $data['date'] . ' (' . $data['time_slot'] . ').');

        // The registrar works from the appointment record itself; each of the
        // eight workspaces has its own appointment list instead. Sending a
        // workspace to registrar.appointments.show would land it on a page its
        // role cannot open — it would 403 one screen after succeeding — so go to
        // whichever index actually exists for this account.
        $workspaceIndex = strtolower(auth()->user()->officeScope()) . '.appointments';
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
    public function confirm(Request $request, Appointment $appointment): RedirectResponse
    {
        // Workspace accounts reach this route too — pin them to their own
        // office; only the registrar confirms across offices.
        abort_unless(
            auth()->user()?->role === 'registrar'
                || $appointment->office === auth()->user()->officeScope(),
            403
        );

        abort_if(
            ! in_array($appointment->status, [
                AppointmentStatus::PENDING->value,
                AppointmentStatus::FOR_RESCHEDULE->value,
            ], true),
            422,
            'Only pending or for-reschedule appointments can be confirmed.'
        );

        $hours = TimeSlots::hoursFor($appointment->office);

        $data = $request->validate([
            'confirmed_time' => [
                'required',
                'date_format:H:i',
                'after_or_equal:' . $hours['open'],
                'before:' . $hours['close'],
            ],
        ], [
            'confirmed_time.required' => 'Pick the time you want the student to come.',
            'confirmed_time.date_format' => 'That is not a readable time.',
            'confirmed_time.after_or_equal' => 'The office is not open that early. Choose a time at or after ' . self::clock($hours['open']) . '.',
            'confirmed_time.before' => 'The office is closed by then. Choose a time before ' . self::clock($hours['close']) . '.',
        ]);

        $remaining = Appointment::remainingSlots($appointment->office, $appointment->date->toDateString(), $appointment->time_slot);

        if ($remaining < 1) {
            return back()->with(
                'error',
                'The time slot is already at maximum capacity. Reschedule this appointment to an open slot first.'
            );
        }

        $appointment->update([
            'status' => AppointmentStatus::CONFIRMED->value,
            'confirmed_time' => self::clock($data['confirmed_time']),
            'confirmed_at' => now(),
        ]);

        // The student booked a date and a hoped-for slot; this is the office
        // telling them when to actually arrive.
        SafeMailer::send($appointment->user, new AppointmentApproved($appointment));
        $appointment->user->notify(new AppointmentApprovedNotification($appointment));

        AuditLogger::log(
            'appointment.confirmed',
            'Confirmed appointment ' . $appointment->reference_code . ' for ' . $appointment->date->toDateString()
                . ', student to come at ' . $appointment->confirmed_time . '.'
        );

        return back()->with(
            'status',
            'Appointment approved. ' . $appointment->user->name . ' has been told to come at ' . $appointment->confirmed_time . '.'
        );
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

        $appointment->user->notify(new \App\Notifications\AppointmentCancelledNotification($appointment));

        AuditLogger::log('appointment.cancelled', 'Cancelled appointment ' . $appointment->reference_code . '.');

        return back()->with('status', 'Appointment cancelled. The student has been notified.');
    }
}