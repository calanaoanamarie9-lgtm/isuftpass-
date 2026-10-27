<?php

namespace App\Http\Controllers\Student;

use App\Enums\AppointmentStatus;
use App\Enums\Office;
use App\Http\Controllers\Controller;
use App\Mail\AppointmentBooked;
use App\Mail\AppointmentCancelled;
use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\SlotAvailability;
use App\Models\User;
use App\Notifications\AppointmentBookedNotification;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentOfficeNotification;
use App\Support\AppointmentPurposes;
use App\Support\QrPassCard;
use App\Support\QrUrl;
use App\Support\RegistrarNotifier;
use App\Support\SafeMailer;
use Illuminate\Support\Facades\Notification;
use App\Support\SlotAvailabilityService;
use App\Support\TimeSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

        // The Purpose options are built live from each office's active
        // consultation services, so the first render already matches what
        // the /appointments/services endpoint below will answer — no flash
        // of one list before the fetch lands. "Other" is closed with every
        // office, and an office with no rows of its own falls back to its
        // curated list.
        return view('student.appointments.create', [
            'offices' => $offices,
            'slotLimits' => Appointment::SLOT_LIMITS,
            'purposes' => AppointmentPurposes::toActiveSelectMap(array_keys($offices)),

            // Role-specific requester details (parent's child, guest's ID).
            'requester' => $user,

            // Pre-filled when the student arrived from the consultation
            // catalogue: ?office=Accounting&purpose=Payment Concerns
            'preselect' => $request->only(['office', 'purpose']),
        ]);
    }

    /**
     * JSON endpoint: the active consultation services offered by one office,
     * which is what the booking form's Purpose dropdown is rebuilt from when
     * the student changes office.
     *
     * Each office's own panel owns these rows, so adding, renaming,
     * deactivating or deleting a service is visible here on the very next
     * request — no hardcoded list to keep in step. An office without rows
     * answers with its curated fallback instead of an empty list.
     */
    public function getServicesByOffice(Request $request)
    {
        $data = $request->validate([
            'office' => ['required', 'in:'.implode(',', Office::toSelectKeys())],
        ]);

        return response()->json([
            'office' => $data['office'],
            'services' => AppointmentPurposes::activeServices($data['office']),
        ]);
    }

    /**
     * JSON endpoint: remaining seats per time slot for an office + date,
     * driven by the slot availability & capacity rules.
     */
    public function slots(Request $request)
    {
        $data = $request->validate([
            'office' => ['required', 'in:'.implode(',', Office::toSelectKeys())],
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
            // Read-only: the calendar asks this for whichever office the
            // form carries, including a booking still sitting with a closed
            // office.
            'office' => ['required', 'in:'.implode(',', Office::toSelectKeys())],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        return response()->json(app(SlotAvailabilityService::class)->monthOverview($data['office'], $data['month']));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $office = (string) $request->input('office');

        $user = Auth::user();

        $data = $request->validate([
            'office' => ['required', 'in:'.implode(',', Office::bookableKeys())],
            'purpose' => ['required', 'string', 'max:255'],
            // The hidden "Please specify purpose" box is emptied by the
            // TrimStrings / ConvertEmptyStringsToNull middleware, so a chosen
            // preset arrives here as null - it must stay nullable. "Other"
            // still demands text: see resolvePurpose().
            'purpose_other' => ['required_if:purpose,'.AppointmentPurposes::OTHER, 'nullable', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            // The student asks for a day; the office answers with the hour
            // when it approves, so no slot is asked for here. One that
            // arrives from an older form is still read, so a booking made
            // before the change keeps the hour it was given.
            'time_slot' => [
                'nullable',
                'in:'.implode(',', TimeSlots::forOffice($office)),
            ],
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

        // The student asks for a day — the office answers with the hour when
        // it approves — so there is no requested slot to weigh for capacity
        // here. A booking made from an older form may still name one.
        $timeSlot = $data['time_slot'] ?? null;

        $check = $timeSlot === null
            ? null
            : app(SlotAvailabilityService::class)->checkForOffice($data['office'], $data['date'], $timeSlot);

        if ($check && $check['status'] === SlotAvailability::STATUS_BLOCKED) {
            abort(422, 'The selected time slot is closed for booking. Choose another schedule.');
        }

        $this->ensureNoDuplicateBooking($data);

        // Beyond-capacity bookings are accepted but flagged For Reschedule.
        $overflow = $check !== null && ! $check['available'];

        $appointment = Auth::user()->appointments()->create([
            ...$data,
            'time_slot' => $timeSlot,
            'status' => $overflow
                ? AppointmentStatus::FOR_RESCHEDULE->value
                : AppointmentStatus::PENDING->value,
            'reschedule_reason' => $overflow ? 'Schedule capacity has been reached.' : null,
        ]);

        SafeMailer::send($appointment->user, new AppointmentBooked($appointment));
        $appointment->user->notify(new AppointmentBookedNotification($appointment));

        // Send office notification so the desk sees the new booking on their queue.
        // Alert both the targeted office/department staff and the Registrar.
        $recipients = User::query()
            ->where('is_active', true)
            ->where('approval_status', User::APPROVAL_APPROVED)
            ->where(function ($query) use ($appointment) {
                $query->where('office', $appointment->office)
                    ->orWhere('role', User::ROLE_REGISTRAR);
            })
            ->get();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new AppointmentOfficeNotification($appointment));
        }

        // The registrar owns the appointment pipeline: a new booking is
        // their cue to confirm — or to move it when the slot overflowed.
        RegistrarNotifier::appointment(
            $appointment,
            $overflow ? 'Appointment needs rescheduling' : 'New appointment booked',
            $overflow
                ? sprintf(
                    "%s's booking with %s on %s could not fit the chosen slot and needs rescheduling.",
                    $appointment->user->name,
                    $appointment->office,
                    $appointment->date->format('M j, Y'),
                )
                : sprintf(
                    '%s booked with %s on %s at %s. It is waiting for your confirmation.',
                    $appointment->user->name,
                    $appointment->office,
                    $appointment->date->format('M j, Y'),
                    $appointment->timeToCome(),
                )
        );

        // Flash first: the rating modal opens before the browser leaves this
        // page, and the confirmation still has to greet them on the page
        // they land on afterwards.
        $redirect = redirect()
            ->route('student.appointments.index')
            ->with(
                'status',
                $overflow
                    ? 'The chosen slot is full. Your appointment was recorded as For Reschedule — the Registrar will move it to the next available slot.'
                    : 'Appointment booked. A confirmation email has been sent.'
            );

        // The form posts in the background so the rating modal can open
        // before the redirect. A plain (no-JavaScript) submit still gets
        // the ordinary redirect.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'feedback' => [
                    'transactionType' => Feedback::TYPE_APPOINTMENT,
                    'transactionId' => $appointment->id,
                    'redirect' => route('student.appointments.index'),
                ],
            ]);
        }

        return $redirect;
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
        SafeMailer::send($appointment->user, new AppointmentCancelled($appointment));

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
            'time_slot' => [
                'nullable',
                Rule::requiredIf(! Office::setsTimeOnApproval($appointment->office)),
                'in:'.implode(',', TimeSlots::forOffice($appointment->office)),
            ],
        ]);

        $timeSlot = $data['time_slot'] ?? null;

        if ($timeSlot !== null) {
            $this->ensureSlotAvailable($appointment->office, $data['date'], $timeSlot, $appointment->id);
        }

        $appointment->update([
            'date' => $data['date'],
            'time_slot' => $timeSlot,
            'status' => AppointmentStatus::PENDING->value,
            'confirmed_time' => null,
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
            'slotLimits' => Appointment::SLOT_LIMITS,

            // Same live source as create(): the edit form's Purpose dropdown
            // is rebuilt from the office's own panel services, never from a
            // curated list — a service the office has removed must not come
            // back here while editing.
            'purposes' => AppointmentPurposes::toActiveSelectMap(array_keys($offices)),
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

        $office = (string) $request->input('office');

        $data = $request->validate([
            // A booking already made with the Cashier's office (before it
            // stopped taking appointments) can still be edited; no new one can
            // be moved to it.
            'office' => ['required', 'in:'.implode(',', array_unique([...Office::bookableKeys(), $appointment->office]))],
            'purpose' => ['required', 'string', 'max:255'],
            // Same as store(): a preset purpose submits the emptied (null)
            // specify box, so the rule stays nullable while resolvePurpose()
            // still demands text when "Other" is chosen.
            'purpose_other' => ['required_if:purpose,'.AppointmentPurposes::OTHER, 'nullable', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            // The student asks for a day; the office answers with the hour
            // when it approves, so no slot is asked for here. One that
            // arrives from an older form is still read, so a booking made
            // before the change keeps the hour it was given.
            'time_slot' => [
                'nullable',
                'in:'.implode(',', TimeSlots::forOffice($office)),
            ],
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

        // The student edits a day, not an hour: the hour the office gave the
        // booking stays with it, and a day that moves sends the booking back
        // for a fresh answer.
        $timeSlot = $data['time_slot'] ?? $appointment->time_slot;

        $scheduleChanged = $appointment->date->toDateString() !== $data['date'];
        $officeChanged = $appointment->office !== $data['office'];

        if ($scheduleChanged && $timeSlot !== null) {
            $this->ensureSlotAvailable($data['office'], $data['date'], $timeSlot, $appointment->id);
        }

        if ($officeChanged || $scheduleChanged) {
            $this->ensureNoDuplicateBooking($data, $appointment->id);
        }

        $appointment->fill($data);
        $appointment->time_slot = $timeSlot;

        // A changed schedule requires re-confirmation.
        if ($scheduleChanged) {
            $appointment->status = AppointmentStatus::PENDING->value;
            $appointment->confirmed_time = null;
            $appointment->confirmed_at = null;
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
