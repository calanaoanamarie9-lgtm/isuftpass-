<?php

namespace App\Http\Controllers\Guard;

use App\Enums\AppointmentStatus;
use App\Enums\DocumentRequestStatus;
use App\Enums\Office;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\GateLog;
use App\Models\StudentProfile;
use App\Models\Visitor;
use App\Support\AppointmentSweep;
use App\Support\CampusTime;
use App\Support\GateLogList;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The campus security desk.
 *
 * One page does the work: a guard logs in and the scanner is already
 * waiting. Whatever the QR turns out to be — a Visitor Gate Pass, a Digital
 * Claim Pass, an appointment visit or a Digital ID — the desk resolves it to
 * that one record and nothing else, then writes the entry or exit that the
 * scan represents.
 *
 * The guard never reaches a public verification route: visitor passes in
 * particular are private to the desk, so /verify/visitor/{token} does not
 * exist outside this authenticated scanner.
 */
class GateController extends Controller
{
    /**
     * Scanner first, day's access log underneath.
     */
    public function dashboard(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('guard.dashboard', [
            'gateName' => $this->gateName(),
            'logs' => GateLogList::today($search),
            'search' => $search,
            'insideCount' => GateLogList::insideCount(),
            'todayCount' => GateLogList::todayCount(),
        ]);
    }

    /**
     * Resolve one scanned token to the single pass it belongs to, verify it,
     * and — only when it is valid — write the entry/exit the scan means.
     */
    public function scan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:2048'],
        ]);

        $token = $this->extractToken($data['token']);

        if ($token === '') {
            return $this->rejected('Unrecognised QR code — nothing to verify.');
        }

        $resolution = $this->resolve($token);

        if ($resolution === null) {
            return $this->rejected('No pass found for this QR code.');
        }

        if (! $resolution['valid']) {
            return response()->json([
                'valid' => false,
                'pass' => $resolution['pass'],
                'outcome' => null,
                'message' => $resolution['message'],
                'logs' => GateLogList::today(),
                'stats' => $this->stats(),
            ]);
        }

        $outcome = $this->record($resolution['log']);

        return response()->json([
            'valid' => true,
            'pass' => $resolution['pass'],
            'outcome' => $outcome,
            'message' => $outcome['message'],
            'logs' => GateLogList::today(),
            'stats' => $this->stats(),
        ]);
    }

    /**
     * Passes the desk issued, and the form that issues a new one.
     */
    public function visitors(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $visitors = Visitor::query()
            ->when($search !== '', function ($query) use ($search) {
                $needle = '%'.strtolower($search).'%';

                $query->where(function ($inner) use ($needle) {
                    $inner->whereRaw("lower(coalesce(name, '')) like ?", [$needle])
                        ->orWhereRaw("lower(coalesce(reference_code, '')) like ?", [$needle]);
                });
            })
            ->latest()
            ->limit(50)
            ->get();

        return view('guard.visitors', [
            'gateName' => $this->gateName(),
            'visitors' => $visitors,
            'search' => $search,
            'insideCount' => GateLogList::insideCount(),
            'todayCount' => GateLogList::todayCount(),
            'destinations' => Office::toSelect(),
        ]);
    }

    /**
     * Issue a Visitor Gate Pass at the desk.
     */
    public function storeVisitor(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'id_number' => ['nullable', 'string', 'max:60'],
            'purpose' => ['required', 'string', 'max:255'],
            'destination_office' => ['required', 'string', Rule::in(Office::toSelectKeys())],
            'host_name' => ['nullable', 'string', 'max:255'],
            'valid_until' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $photo = $request->hasFile('photo')
            ? $request->file('photo')->store('visitors', config('filesystems.avatar', 'public'))
            : null;

        $visitor = Visitor::create([
            'name' => $data['name'],
            'contact_number' => $data['contact_number'] ?? null,
            'id_number' => $data['id_number'] ?? null,
            'purpose' => $data['purpose'],
            'destination_office' => $data['destination_office'],
            'host_name' => $data['host_name'] ?? null,
            'valid_until' => $data['valid_until'] ?? CampusTime::today(),
            'photo' => $photo,
            'status' => Visitor::STATUS_ACTIVE,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('guard.visitors.show', $visitor)
            ->with('status', 'Visitor Gate Pass '.$visitor->reference_code.' issued to '.$visitor->name.'.');
    }

    /**
     * The pass itself — the card the visitor holds up at the gate.
     */
    public function showVisitor(Visitor $visitor): View
    {
        return view('guard.visitor', [
            'visitor' => $visitor,
            'gateName' => $this->gateName(),
            'qrDataUri' => $this->makeQr($visitor->qrPayload()),
        ]);
    }

    public function revokeVisitor(Request $request, Visitor $visitor): RedirectResponse
    {
        $visitor->update(['status' => Visitor::STATUS_REVOKED]);

        return back()->with('status', 'Visitor Gate Pass '.$visitor->reference_code.' was revoked.');
    }

    /*
    |--------------------------------------------------------------------------
    | Resolution
    |--------------------------------------------------------------------------
    */

    /**
     * The four things a gate QR can be, in the order the desk cares about
     * them. Each branch returns a pass whose payload describes exactly the
     * one row the token belongs to.
     *
     * @return array{valid: bool, message: string, pass: array<string, mixed>, log: array<string, mixed>}|null
     */
    private function resolve(string $token): ?array
    {
        $visitor = Visitor::where('token', $token)->first();

        if ($visitor) {
            return $this->visitorPass($visitor);
        }

        $document = DocumentRequest::with(['user.studentProfile', 'documents'])
            ->where('claim_token', $token)
            ->first();

        if ($document) {
            return $this->documentPass($document);
        }

        $appointment = Appointment::with('user.studentProfile')
            ->where('qr_token', $token)
            ->first();

        if ($appointment) {
            return $this->appointmentPass($appointment);
        }

        $profile = StudentProfile::with('user')
            ->where('pass_token', $token)
            ->first();

        if ($profile) {
            return $this->studentPass($profile);
        }

        return null;
    }

    /**
     * @return array{valid: bool, message: string, pass: array<string, mixed>, log: array<string, mixed>}
     */
    private function visitorPass(Visitor $visitor): array
    {
        return $this->pass(
            type: 'visitor',
            name: $visitor->name,
            personId: $visitor->id_number ?: $visitor->reference_code,
            photoUrl: $visitor->photoUrl(),
            destination: $visitor->destination_office,
            purpose: $visitor->purpose,
            valid: $visitor->isValid(),
            statusLabel: $visitor->statusLabel(),
            statusTone: $visitor->isValid() ? 'valid' : 'invalid',
            reference: $visitor->reference_code,
            log: ['pass_type' => 'visitor', 'pass_id' => $visitor->id],
            message: $visitor->isValid()
                ? null
                : 'This Visitor Gate Pass is '.strtolower($visitor->statusLabel()).' and cannot be used for entry.',
        );
    }

    /**
     * @return array{valid: bool, message: string, pass: array<string, mixed>, log: array<string, mixed>}
     */
    private function documentPass(DocumentRequest $document): array
    {
        $status = DocumentRequestStatus::tryFrom((string) $document->status);

        // A claim pass earns entry only while the credential is actually
        // waiting at the releasing office; anything earlier is still moving
        // through the pipeline and anything later is already claimed.
        $ready = $document->status === DocumentRequestStatus::READY_FOR_PICKUP->value;

        $tone = match (true) {
            $ready => 'valid',
            $document->status === DocumentRequestStatus::CANCELLED->value => 'invalid',
            default => 'warning',
        };

        return $this->pass(
            type: 'document',
            name: $document->student_name ?: ($document->user?->name ?? 'Student'),
            personId: $document->user?->studentProfile?->student_id ?: $document->request_number,
            photoUrl: $document->user?->studentProfile?->avatar_url ?? $document->user?->avatar_url,
            destination: $document->claiming_office ?? "Registrar's Office",
            purpose: $document->documentsSummary() ?: ($status?->label() ?? 'Document claim'),
            valid: $ready,
            statusLabel: $status?->label() ?? Str::headline((string) $document->status),
            statusTone: $tone,
            reference: $document->request_number,
            log: [
                'pass_type' => 'document',
                'pass_id' => $document->id,
                'user_id' => $document->user_id,
            ],
            message: $ready
                ? null
                : 'This claim pass is not valid for entry — the document is “'.($status?->label() ?? 'not ready').'”.',
        );
    }

    /**
     * @return array{valid: bool, message: string, pass: array<string, mixed>, log: array<string, mixed>}
     */
    private function appointmentPass(Appointment $appointment): array
    {
        // Close the booking out before it is read: a visit whose hour ran
        // out with nobody checked in is cancelled right here, so the desk
        // never shows a pass as good after the appointment stopped being
        // real - even before the scheduled sweep gets to it.
        AppointmentSweep::cancelIfMissed($appointment);

        $status = AppointmentStatus::tryFrom((string) $appointment->status);

        $isToday = $appointment->date?->isToday() ?? false;

        $blocked = in_array($appointment->status, [
            AppointmentStatus::CANCELLED->value,
            AppointmentStatus::NO_SHOW->value,
        ], true);

        $valid = $isToday && ! $blocked;

        $tone = match (true) {
            $blocked => 'invalid',
            $valid => 'valid',
            default => 'warning',
        };

        $label = match (true) {
            $blocked => $status?->label() ?? 'Cancelled',
            $isToday => $status?->label() ?? 'Scheduled Today',
            default => 'Scheduled '.$appointment->date?->format('M j, Y'),
        };

        return $this->pass(
            type: 'appointment',
            name: $appointment->user?->name ?? 'Student',
            personId: $appointment->user?->studentProfile?->student_id ?: $appointment->reference_code,
            photoUrl: $appointment->user?->studentProfile?->avatar_url ?? $appointment->user?->avatar_url,
            destination: $appointment->office,
            purpose: $appointment->purpose,
            valid: $valid,
            statusLabel: $label,
            statusTone: $tone,
            reference: $appointment->reference_code,
            log: [
                'pass_type' => 'appointment',
                'pass_id' => $appointment->id,
                'user_id' => $appointment->user_id,
            ],
            message: match (true) {
                $blocked => 'This appointment is “'.($status?->label() ?? 'cancelled').'” and cannot be used for entry.',
                ! $isToday => 'This appointment is not for today — it is set for '.$appointment->date?->format('M j, Y').'.',
                default => null,
            },
        );
    }

    /**
     * @return array{valid: bool, message: string, pass: array<string, mixed>, log: array<string, mixed>}
     */
    private function studentPass(StudentProfile $profile): array
    {
        $user = $profile->user;

        return $this->pass(
            type: 'student',
            name: $user?->name ?? 'Student',
            personId: $profile->student_id ?: ($user?->student_id ?? '—'),
            photoUrl: $profile->avatar_url ?? $user?->avatar_url,
            destination: 'Campus Access',
            purpose: 'Digital Student ID',
            valid: true,
            statusLabel: 'Valid',
            statusTone: 'valid',
            reference: $profile->student_id ?: (string) $user?->email,
            log: [
                'pass_type' => 'student',
                'pass_id' => $profile->id,
                'user_id' => $profile->user_id,
            ],
        );
    }

    /**
     * Shape every resolved pass the same way, so the scanner renders one card
     * whatever was scanned.
     *
     * @param  array<string, mixed>  $log
     * @return array{valid: bool, message: string, pass: array<string, mixed>, log: array<string, mixed>}
     */
    private function pass(
        string $type,
        string $name,
        ?string $personId,
        ?string $photoUrl,
        ?string $destination,
        ?string $purpose,
        bool $valid,
        string $statusLabel,
        string $statusTone,
        ?string $reference,
        array $log,
        ?string $message = null,
    ): array {
        return [
            'valid' => $valid,
            'message' => $message ?? ($valid ? 'Pass verified.' : 'This pass is not valid for entry.'),
            'pass' => [
                'type' => $type,
                'label' => GateLogList::label($type),
                'name' => $name,
                'personId' => $personId ?: '—',
                'photoUrl' => $photoUrl,
                'initial' => Str::upper(Str::substr($name, 0, 1)),
                'destination' => $destination ?: '—',
                'purpose' => $purpose ?: '—',
                'statusLabel' => $statusLabel,
                'statusTone' => $statusTone,
                'reference' => $reference ?: '—',
            ],
            'log' => $log + [
                'person_name' => $name,
                'reference_code' => $reference,
                'destination' => $destination,
                'purpose' => $purpose,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Entry / exit
    |--------------------------------------------------------------------------
    */

    /**
     * The first scan of a pass today opens an entry session; the next one
     * closes it. One row per visit, so the desk's log reads as an arrival and
     * a departure rather than two unrelated events.
     *
     * @param  array<string, mixed>  $log
     * @return array{direction: string, time: string, timeIn: string|null, timeOut: string|null, message: string}
     */
    private function record(array $log): array
    {
        $guard = Auth::user();
        $now = now();
        $gate = $this->gateName();

        $open = GateLog::query()
            ->where('pass_type', $log['pass_type'])
            ->where('pass_id', $log['pass_id'])
            ->whereNull('time_out')
            ->whereBetween('scanned_at', CampusTime::todayBoundsUtc())
            ->latest('id')
            ->first();

        if ($open) {
            $open->forceFill([
                'time_out' => $now,
                'direction' => 'out',
                'guard_id' => $guard?->id,
                'gate' => $gate,
                'scanned_at' => $now,
            ])->save();

            return [
                'direction' => 'out',
                'time' => CampusTime::display($now),
                'timeIn' => CampusTime::display($open->time_in),
                'timeOut' => CampusTime::display($now),
                'message' => 'Exit recorded at '.CampusTime::display($now).'.',
            ];
        }

        GateLog::create([
            'user_id' => $log['user_id'] ?? null,
            'pass_type' => $log['pass_type'],
            'pass_id' => $log['pass_id'],
            'person_name' => $log['person_name'] ?? null,
            'reference_code' => $log['reference_code'] ?? null,
            'destination' => $log['destination'] ?? null,
            'purpose' => $log['purpose'] ?? null,
            'guard_id' => $guard?->id,
            'direction' => 'in',
            'gate' => $gate,
            'time_in' => $now,
            'scanned_at' => $now,
        ]);

        return [
            'direction' => 'in',
            'time' => CampusTime::display($now),
            'timeIn' => CampusTime::display($now),
            'timeOut' => null,
            'message' => 'Entry recorded at '.CampusTime::display($now).'.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Small helpers
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{inside: int, today: int}
     */
    private function stats(): array
    {
        return [
            'inside' => GateLogList::insideCount(),
            'today' => GateLogList::todayCount(),
        ];
    }

    private function rejected(string $message): JsonResponse
    {
        return response()->json([
            'valid' => false,
            'pass' => null,
            'outcome' => null,
            'message' => $message,
            'logs' => GateLogList::today(),
            'stats' => $this->stats(),
        ]);
    }

    /**
     * Accept the token however it arrives: a bare UUID typed into the USB
     * scanner, or a full verification URL lifted from a phone's screen.
     */
    private function extractToken(string $raw): string
    {
        $raw = trim(str_replace("\r", '', $raw));

        if ($raw === '') {
            return '';
        }

        $path = parse_url($raw, PHP_URL_PATH);

        if (is_string($path) && $path !== '') {
            return basename($path);
        }

        return basename($raw);
    }

    /**
     * The guard's post, shown on every pass and written to the log.
     */
    private function gateName(): string
    {
        return Auth::user()?->office ?: 'Main Gate';
    }

    private function makeQr(string $payload): string
    {
        return (new SvgWriter)->write(new QrCode($payload))->getDataUri();
    }
}
