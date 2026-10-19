<?php

namespace App\Http\Controllers\Student;

use App\Enums\AppointmentStatus;
use App\Enums\Office;
use App\Http\Controllers\Controller;
use App\Mail\AppointmentBooked;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentBookedNotification;
use App\Notifications\AppointmentCancelledNotification;
use App\Support\AppointmentPurposes;
use App\Support\QrPassCard;
use App\Support\QrUrl;
use App\Support\RegistrarNotifier;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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
            ->when($tab === 'upcoming', fn ($q) => $q->upcoming()->orderBy('date')->orderBy('created_at'))
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
            $appointment->forceFill(['qr_token' => (string) Str::uuid()])->save();
        }

        $user = Auth::user();

        // The card builds its own QR from Appointment::passQrDataUri() — the
        // same method the scanned page and the registrar's station render,
        // so every surface prints a byte-identical code for this booking.
        return view('student.appointments.qr', [
            'appointment' => $appointment,
            'student' => $user,
            'profile' => $user->studentProfile,
            'avatar' => $user->studentProfile?->avatar_url,
        ]);
    }

    /**
     * Download the complete pass card for this appointment.
     *
     * PNG is what this serves by default. A phone cannot open an .svg —
     * no gallery or viewer claims image/svg+xml, so Android downloads the
     * file and then refuses it with "Couldn't open file". PNG opens in
     * every phone's photo viewer, which is where the QR is actually shown.
     *
     * ?format=svg still returns the vector original for print and scaling.
     */
    public function downloadQr(Request $request, Appointment $appointment): Response
    {
        Gate::authorize('view', $appointment);

        $user = Auth::user();
        $profile = $user->studentProfile;

        $options = [
            'sectionLabel' => 'Appointment QR',
            'refCode' => $appointment->reference_code,
            'caption' => 'Present this QR code to authorized personnel.',
            'name' => $user->name,
            'studentId' => $profile?->student_id ?? '',
            'course' => $profile?->course ?? '',
            'yearLevel' => $profile?->year_level ?? '',
            'qrPayload' => QrUrl::to('/verify/appointment/'.$appointment->qr_token),
        ];

        if ($request->query('format') === 'svg') {
            return response(QrPassCard::svg($options))
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', 'attachment; filename="isufstpass-appointment-'.$appointment->reference_code.'.svg"');
        }

        $png = QrPassCard::png($options);

        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Content-Length', (string) strlen($png))
            ->header('Content-Disposition', 'attachment; filename="isufstpass-appointment-'.$appointment->reference_code.'.png"');
    }

    public function create(Request $request): View
    {
        $user = Auth::user();
        $offices = $user->bookableOffices();

        return view('student.appointments.create', [
            'offices' => $offices,
            'purposes' => AppointmentPurposes::toSelectMap(array_keys($offices)),

            // Role-specific requester details (parent's child, guest's ID).
            'requester' => $user,

            // Pre-filled when the student arrived from the consultation
            // catalogue: ?office=Accounting&purpose=Payment Concerns
            'preselect' => $request->only(['office', 'purpose']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $office = (string) $request->input('office');

        $user = Auth::user();

        $data = $request->validate([
            'office' => ['required', 'in:'.implode(',', Office::toSelectKeys())],
            'purpose' => ['required', 'string', 'max:255'],
            // The hidden "Please specify purpose" box is emptied by the
            // TrimStrings / ConvertEmptyStringsToNull middleware, so a chosen
            // preset arrives here as null - it must stay nullable. "Other"
            // still demands text: see resolvePurpose().
            'purpose_other' => ['required_if:purpose,'.AppointmentPurposes::OTHER, 'nullable', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ] + $this->requesterRules($user), [
            'purpose_other.required_if' => 'Please specify your purpose.',
        ]);

        // A student cannot book an office that is outside their role's reach.
        Gate::authorize('book-appointment', $data['office']);

        // "Other" stores the purpose the student typed; a chosen preset is kept as-is.
        $data['purpose'] = $this->resolvePurpose($data);
        unset($data['purpose_other']);

        // The child's Student ID (parent) / valid ID (guest) belong on the
        // account, not the appointment row.
        $extras = array_intersect_key($data, array_flip(['student_id', 'valid_id']));
        $data = array_diff_key($data, $extras);
        $this->persistRequesterDetails($user, $extras);

        $this->ensureNoDuplicateBooking($data);

        // A booking names a day; the office answers with the window it wants
        // the student there when it approves.
        $appointment = Auth::user()->appointments()->create([
            ...$data,
            'status' => AppointmentStatus::PENDING->value,
        ]);

        SafeMailer::send($appointment->user, new AppointmentBooked($appointment));
        $appointment->user->notify(new AppointmentBookedNotification($appointment));

        // The registrar owns the appointment pipeline: a new booking is
        // their cue to confirm.
        RegistrarNotifier::appointment(
            $appointment,
            'New appointment booked',
            sprintf(
                '%s booked with %s on %s. It is waiting for your confirmation.',
                $appointment->user->name,
                $appointment->office,
                $appointment->date->format('M j, Y'),
            )
        );

        return redirect()
            ->route('student.appointments.index')
            ->with('status', 'Appointment booked. A confirmation email has been sent.');
    }

    /**
     * Cancel an upcoming appointment.
     */
    public function cancel(Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        if (! $appointment->isModifiableByStudent()) {
            return back()->with('error', 'This appointment can no longer be cancelled once the office has approved it.');
        }

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ]);

        $appointment->user->notify(new AppointmentCancelledNotification($appointment));

        return back()->with('status', 'Appointment cancelled.');
    }

    /**
     * Reschedule (change date), keeping the same office and purpose.
     */
    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        if (! $appointment->isReschedulable()) {
            return back()->with('error', 'This appointment can no longer be rescheduled.');
        }

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $appointment->update([
            'date' => $data['date'],
            'status' => AppointmentStatus::PENDING->value,
            // The old window is void — a moved booking waits for a new answer.
            'confirmed_time' => null,
            'confirmed_at' => null,
            'start_time' => null,
            'end_time' => null,
        ]);

        return back()->with('status', 'Appointment rescheduled. Wait for re-confirmation.');
    }

    /**
     * "Other" stores the purpose the student typed; a chosen preset is kept
     * as-is.
     *
     * The rule on purpose_other must stay `nullable` (the hidden box submits
     * as null whenever a preset is chosen), and Laravel skips the implicit
     * `required_if` for null values on nullable fields - so "Other" with no
     * typed text gets its own check here, right before the booking is
     * written.
     *
     * @param  array<string, mixed>  $data
     */
    protected function resolvePurpose(array $data): string
    {
        if ($data['purpose'] !== AppointmentPurposes::OTHER) {
            return $data['purpose'];
        }

        $typed = trim((string) ($data['purpose_other'] ?? ''));

        if ($typed === '') {
            throw ValidationException::withMessages([
                'purpose_other' => 'Please specify your purpose.',
            ]);
        }

        return $typed;
    }

    /**
     * Edit form for an upcoming appointment (pre-filled with current values).
     */
    public function edit(Appointment $appointment): View
    {
        Gate::authorize('update', $appointment);

        if (! $appointment->isModifiableByStudent()) {
            abort(403, 'This appointment can no longer be edited once the office has approved it.');
        }

        $user = Auth::user();

        $offices = $user->bookableOffices();

        // Keep the appointment's own office selectable so a booking made
        // under another role can still be edited.
        if (! array_key_exists($appointment->office, $offices)) {
            $offices = [$appointment->office => $appointment->office] + $offices;
        }

        return view('student.appointments.create', [
            'appointment' => $appointment,
            'offices' => $offices,
            'purposes' => AppointmentPurposes::toSelectMap(array_keys($offices)),
            'requester' => $user,
        ]);
    }

    /**
     * Update an upcoming appointment (office, purpose, schedule, notes).
     */
    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        if (! $appointment->isModifiableByStudent()) {
            return back()->with('error', 'This appointment can no longer be edited once the office has approved it.');
        }

        $data = $request->validate([
            'office' => ['required', 'in:'.implode(',', Office::toSelectKeys())],
            'purpose' => ['required', 'string', 'max:255'],
            // Same as store(): a preset purpose submits the emptied (null)
            // specify box, so the rule stays nullable while resolvePurpose()
            // still demands text when "Other" is chosen.
            'purpose_other' => ['required_if:purpose,'.AppointmentPurposes::OTHER, 'nullable', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'purpose_other.required_if' => 'Please specify your purpose.',
        ]);

        // Only a *change* of office is re-authorised, so an existing booking
        // made under another role does not lock its owner out.
        if ($appointment->office !== $data['office']) {
            Gate::authorize('book-appointment', $data['office']);
        }

        // "Other" stores the purpose the student typed; a chosen preset is kept as-is.
        $data['purpose'] = $this->resolvePurpose($data);
        unset($data['purpose_other']);

        $scheduleChanged = $appointment->date->toDateString() !== $data['date'];
        $officeChanged = $appointment->office !== $data['office'];

        if ($officeChanged || $scheduleChanged) {
            $this->ensureNoDuplicateBooking($data, $appointment->id);
        }

        $appointment->fill($data);

        // A changed schedule requires re-confirmation.
        if ($scheduleChanged) {
            $appointment->status = AppointmentStatus::PENDING->value;
            $appointment->confirmed_time = null;
            $appointment->confirmed_at = null;
            $appointment->start_time = null;
            $appointment->end_time = null;
        }

        $appointment->save();

        return redirect()
            ->route('student.appointments.index')
            ->with('status', 'Appointment updated.');
    }

    /**
     * Extra booking rules a public account type must satisfy: a parent must
     * give the child's Student ID, a guest a valid ID. Only demanded when the
     * account does not already hold the value.
     *
     * @return array<string, array<int, string>>
     */
    protected function requesterRules(User $user): array
    {
        $rules = [];

        if ($user->classification() === 'parent' && blank($user->student_id)) {
            $rules['student_id'] = ['required', 'string', 'max:50'];
        }

        if ($user->classification() === 'guest' && blank($user->valid_id)) {
            $rules['valid_id'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Store the child's Student ID / valid ID on the account the first time it
     * is supplied at booking.
     *
     * @param  array<string, mixed>  $extras
     */
    protected function persistRequesterDetails(User $user, array $extras): void
    {
        $updates = [];

        if (isset($extras['student_id']) && blank($user->student_id)) {
            $updates['student_id'] = $extras['student_id'];
        }

        if (isset($extras['valid_id']) && blank($user->valid_id)) {
            $updates['valid_id'] = $extras['valid_id'];
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
        }
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
