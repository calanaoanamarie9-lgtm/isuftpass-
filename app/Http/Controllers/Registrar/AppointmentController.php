<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\AppointmentStatus;
use App\Enums\Office;
use App\Http\Controllers\Controller;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Notifications\AppointmentRescheduledNotification;
use App\Support\AuditLogger;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->latest()
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
            'time_slot' => ['required', Rule::in(Appointment::TIME_SLOTS)],
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

        if ($request->wantsJson()) {
            return response()->json([
                'redirect' => route('registrar.appointments.show', $appointment),
                'message' => 'Appointment rescheduled. The student has been notified.',
            ]);
        }

        return redirect()
            ->route('registrar.appointments.show', $appointment)
            ->with('status', 'Appointment rescheduled. The student has been notified by email and in the system.');
    }

    /**
     * Approve a pending / for-reschedule appointment, guarding slot capacity.
     */
    public function confirm(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_if(
            ! in_array($appointment->status, [
                AppointmentStatus::PENDING->value,
                AppointmentStatus::FOR_RESCHEDULE->value,
            ], true),
            422,
            'Only pending or for-reschedule appointments can be confirmed.'
        );

        $remaining = Appointment::remainingSlots($appointment->office, $appointment->date->toDateString(), $appointment->time_slot);

        if ($remaining < 1) {
            return back()->with(
                'error',
                'The time slot is already at maximum capacity. Reschedule this appointment to an open slot first.'
            );
        }

        $appointment->update([
            'status' => AppointmentStatus::CONFIRMED->value,
            'confirmed_at' => now(),
        ]);

        AuditLogger::log('appointment.confirmed', 'Confirmed appointment ' . $appointment->reference_code . ' for ' . $appointment->date->toDateString() . ' (' . $appointment->time_slot . ').');

        return back()->with('status', 'Appointment confirmed.');
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