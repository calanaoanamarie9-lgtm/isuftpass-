<?php

namespace App\Http\Controllers\Student;

use App\Enums\AppointmentStatus;
use App\Enums\Office;
use App\Http\Controllers\Controller;
use App\Mail\AppointmentBooked;
use App\Models\Appointment;
use App\Notifications\AppointmentBookedNotification;
use App\Notifications\AppointmentCancelledNotification;
use App\Support\SafeMailer;
use App\Support\SlotAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * My Appointments: upcoming, completed, and cancelled.
     */
    public function index(Request $request): View
    {
        $tab = in_array($request->query('tab'), ['upcoming', 'completed', 'cancelled'], true)
            ? $request->query('tab')
            : 'upcoming';

        $appointments = Auth::user()->appointments()
            ->when($tab === 'upcoming', fn ($q) => $q->upcoming()->orderBy('date')->orderBy('time_slot'))
            ->when($tab === 'completed', fn ($q) => $q->where('status', AppointmentStatus::COMPLETED->value)->latest())
            ->when($tab === 'cancelled', fn ($q) => $q->where('status', AppointmentStatus::CANCELLED->value)->latest())
            ->paginate(10)
            ->withQueryString();

        return view('student.appointments.index', [
            'appointments' => $appointments,
            'tab' => $tab,
        ]);
    }

    /**
     * Appointment QR pass — scannable proof of booking for the office / gate.
     */
    public function qr(Appointment $appointment): View
    {
        Gate::authorize('view', $appointment);

        if (! $appointment->qr_token) {
            $appointment->forceFill(['qr_token' => (string) \Illuminate\Support\Str::uuid()])->save();
        }

        $user = Auth::user();

        // Encode a LAN-reachable URL so scanning opens the verification page.
        $payload = \App\Support\QrUrl::to('/verify/appointment/' . $appointment->qr_token);

        $qrCode = (new \Endroid\QrCode\QrCode($payload));

        return view('student.appointments.qr', [
            'appointment' => $appointment,
            'qrCodeDataUri' => (new \Endroid\QrCode\Writer\SvgWriter())->write($qrCode)->getDataUri(),
            'student' => $user,
            'profile' => $user->studentProfile,
            'avatar' => $user->studentProfile?->avatar ? \Illuminate\Support\Facades\Storage::url($user->studentProfile->avatar) : null,
        ]);
    }

    /**
     * Download a complete pass-card SVG for this appointment.
     */
    public function downloadQr(Appointment $appointment): \Symfony\Component\HttpFoundation\Response
    {
        Gate::authorize('view', $appointment);

        $user = Auth::user();
        $profile = $user->studentProfile;

        $svg = \App\Support\QrPassCard::svg([
            'sectionLabel' => 'Appointment QR',
            'refCode' => $appointment->reference_code,
            'caption' => 'Present this QR code to authorized personnel.',
            'name' => $user->name,
            'studentId' => $profile?->student_id ?? '',
            'course' => $profile?->course ?? '',
            'yearLevel' => $profile?->year_level ?? '',
            'qrPayload' => \App\Support\QrUrl::to('/verify/appointment/' . $appointment->qr_token),
        ]);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="isufstpass-appointment-' . $appointment->reference_code . '.svg"');
    }

    public function create(Request $request): View
    {
        return view('student.appointments.create', [
            'offices' => Office::toSelect(),
            'timeSlots' => Appointment::TIME_SLOTS,
            'slotLimits' => Appointment::SLOT_LIMITS,

            // Pre-filled when the student arrived from the consultation
            // catalogue: ?office=Accounting&purpose=Payment Concerns
            'preselect' => $request->only(['office', 'purpose']),
        ]);
    }

    /**
     * JSON endpoint: remaining seats per time slot for an office + date,
     * driven by the slot availability & capacity rules.
     */
    public function slots(Request $request)
    {
        $data = $request->validate([
            'office' => ['required', 'in:' . implode(',', Office::toSelectKeys())],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        return response()->json(Appointment::availableSlots($data['office'], $data['date']));
    }

    /**
     * JSON endpoint: per-day availability map for a month (calendar coloring).
     */
    public function availability(Request $request)
    {
        $data = $request->validate([
            'office' => ['required', 'in:' . implode(',', Office::toSelectKeys())],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        return response()->json(app(SlotAvailabilityService::class)->monthOverview($data['office'], $data['month']));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'office' => ['required', 'in:' . implode(',', Office::toSelectKeys())],
            'purpose' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', 'in:' . implode(',', Appointment::TIME_SLOTS)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $check = app(SlotAvailabilityService::class)
            ->checkForOffice($data['office'], $data['date'], $data['time_slot']);

        if ($check['status'] === \App\Models\SlotAvailability::STATUS_BLOCKED) {
            abort(422, 'The selected time slot is closed for booking. Choose another schedule.');
        }

        $this->ensureNoDuplicateBooking($data);

        // Beyond-capacity bookings are accepted but flagged For Reschedule.
        $overflow = ! $check['available'];

        $appointment = Auth::user()->appointments()->create([
            ...$data,
            'status' => $overflow
                ? AppointmentStatus::FOR_RESCHEDULE->value
                : AppointmentStatus::PENDING->value,
            'reschedule_reason' => $overflow ? 'Schedule capacity has been reached.' : null,
        ]);

        SafeMailer::send($appointment->user, new AppointmentBooked($appointment));
        $appointment->user->notify(new AppointmentBookedNotification($appointment));

        return redirect()
            ->route('student.appointments.index')
            ->with(
                'status',
                $overflow
                    ? 'The chosen slot is full. Your appointment was recorded as For Reschedule — the Registrar will move it to the next available slot.'
                    : 'Appointment booked. A confirmation email has been sent.'
            );
    }

    /**
     * Cancel an upcoming appointment.
     */
    public function cancel(Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        if (! $appointment->isCancellable()) {
            return back()->with('error', 'This appointment can no longer be cancelled.');
        }

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ]);

        $appointment->user->notify(new AppointmentCancelledNotification($appointment));

        return back()->with('status', 'Appointment cancelled.');
    }

    /**
     * Reschedule (change date / time slot), keeping the same office and purpose.
     */
    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        if (! $appointment->isReschedulable()) {
            return back()->with('error', 'This appointment can no longer be rescheduled.');
        }

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', 'in:' . implode(',', Appointment::TIME_SLOTS)],
        ]);

        $this->ensureSlotAvailable($appointment->office, $data['date'], $data['time_slot'], $appointment->id);

        $appointment->update([
            ...$data,
            'status' => AppointmentStatus::PENDING->value,
            'confirmed_at' => null,
        ]);

        return back()->with('status', 'Appointment rescheduled. Wait for re-confirmation.');
    }

    /**
     * Reject when the office/time slot is blocked or already fully booked.
     */
    protected function ensureSlotAvailable(string $office, string $date, string $timeSlot, ?int $ignoreId = null): void
    {
        app(SlotAvailabilityService::class)->assertBookable($office, $date, $timeSlot, $ignoreId);
    }

    /**
     * Edit form for an upcoming appointment (pre-filled with current values).
     */
    public function edit(Appointment $appointment): View
    {
        Gate::authorize('update', $appointment);

        if (! $appointment->isUpcoming()) {
            abort(403, 'Only upcoming appointments can be edited.');
        }

        return view('student.appointments.create', [
            'appointment' => $appointment,
            'offices' => Office::toSelect(),
            'timeSlots' => Appointment::TIME_SLOTS,
            'slotLimits' => Appointment::SLOT_LIMITS,
        ]);
    }

    /**
     * Update an upcoming appointment (office, purpose, schedule, notes).
     */
    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        if (! $appointment->isUpcoming()) {
            return back()->with('error', 'This appointment can no longer be edited.');
        }

        $data = $request->validate([
            'office' => ['required', 'in:' . implode(',', Office::toSelectKeys())],
            'purpose' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', 'in:' . implode(',', Appointment::TIME_SLOTS)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $scheduleChanged = $appointment->date->toDateString() !== $data['date']
            || $appointment->time_slot !== $data['time_slot'];
        $officeChanged = $appointment->office !== $data['office'];

        if ($scheduleChanged) {
            $this->ensureSlotAvailable($data['office'], $data['date'], $data['time_slot'], $appointment->id);
        }

        if ($officeChanged || $scheduleChanged) {
            $this->ensureNoDuplicateBooking($data, $appointment->id);
        }

        $appointment->fill($data);

        // A changed schedule requires re-confirmation.
        if ($scheduleChanged) {
            $appointment->status = AppointmentStatus::PENDING->value;
            $appointment->confirmed_at = null;
        }

        $appointment->save();

        return redirect()
            ->route('student.appointments.index')
            ->with('status', 'Appointment updated.');
    }

    /**
     * A student cannot hold two active bookings for the same workday.
     */
    protected function ensureNoDuplicateBooking(array $data, ?int $ignoreId = null): void
    {
        $exists = Auth::user()->appointments()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->whereDate('date', $data['date'])
            ->where('office', $data['office'])
            ->whereIn('status', AppointmentStatus::schedulableValues())
            ->exists();

        if ($exists) {
            abort(422, 'You already have an active appointment with this office on the same day.');
        }
    }
}