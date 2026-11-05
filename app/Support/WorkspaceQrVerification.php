<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * What an office desk sees when it verifies a pass.
 *
 * The registrar's station answers for the whole university, so its lookup can
 * name any student and any transaction. A department or office answers only
 * for its own queue, and this class keeps that boundary while still letting
 * the desk recognise what was handed to it: the identity on a student's
 * digital ID, the booking behind an appointment pass, or a claim slip that
 * belongs to the Registrar and cannot be released here.
 *
 * Nothing is written here. A scan is a question — the arrival itself is
 * recorded by QrCheckInController, when the desk says so.
 */
class WorkspaceQrVerification
{
    /** Statuses a desk may accept a student into, same as the scan endpoint. */
    private const CHECKABLE = [
        AppointmentStatus::PENDING->value,
        AppointmentStatus::CONFIRMED->value,
    ];

    /**
     * The whole page: the header's office, the query, and every card that can
     * answer it.
     *
     * @return array{
     *     office: string,
     *     query: string,
     *     student: User|null,
     *     appointment: Appointment|null,
     *     claimSlip: DocumentRequest|null,
     *     officeAppointments: Collection<int, Appointment>,
     *     checkIns: array<int, array<string, string>>
     * }
     */
    public static function page(User $user, Request $request): array
    {
        $office = $user->officeScope();
        $query = trim((string) $request->query('q'));

        $student = null;
        $appointment = null;
        $claimSlip = null;

        if ($query !== '') {
            ['pass' => $passToken, 'claim' => $claimToken, 'appointment' => $appointmentToken] = QrPayload::extract($query);

            if ($claimToken) {
                $claimSlip = DocumentRequest::query()
                    ->with('documents')
                    ->where('claim_token', $claimToken)
                    ->first();
            } elseif ($appointmentToken) {
                $appointment = self::findAppointment($appointmentToken);
            } elseif ($passToken) {
                // A bare token may be either the appointment's qr_token or
                // the student's pass_token, and both are uuids: try the
                // booking first, then the identity.
                $appointment = self::findAppointment($passToken);

                if (! $appointment) {
                    $student = self::studentByPass($passToken);
                    $appointment = $student ? self::todayFor($student, $office) : null;
                }
            } else {
                // Plain text is a deliberate name / email / student number
                // search — the only result that may list a student's queue.
                $student = self::searchStudent($query);
                $appointment = $student ? self::todayFor($student, $office) : null;
            }

            // An appointment pass names its own student, so the identity card
            // and the booking card can be read together.
            if ($appointment) {
                $student = $appointment->user;
            }
        }

        return [
            'office' => $office,
            'query' => $query,
            'student' => $student,
            'appointment' => $appointment,
            'claimSlip' => $claimSlip,
            'officeAppointments' => $student
                ? self::officeAppointments($student, $office)
                : collect(),
            'checkIns' => CheckInList::today($office),
        ];
    }

    /**
     * This student's visit at this office today, preferring the ones a desk can
     * act on so a cancelled morning row never hides a confirmed afternoon one.
     *
     * The scan endpoint records arrivals through the same lookup: a pass that
     * the desk could see here is a pass it can check in there.
     */
    public static function todayFor(User $student, string $office): ?Appointment
    {
        $today = fn () => $student->appointments()
            ->with('user.studentProfile')
            ->where('office', $office)
            ->whereDate('date', now()->toDateString());

        return $today()
            ->whereIn('status', [...self::CHECKABLE, AppointmentStatus::CHECKED_IN->value])
            ->orderBy('time_slot')
            ->first()
            ?? $today()->orderBy('time_slot')->first();
    }

    /**
     * Whether the desk may record this booking's arrival right now. The
     * endpoint re-checks every one of these before it writes anything.
     */
    public static function canCheckIn(Appointment $appointment, string $office): bool
    {
        return $appointment->office === $office
            && $appointment->date->isToday()
            && $appointment->status !== AppointmentStatus::CHECKED_IN->value
            && in_array($appointment->status, self::CHECKABLE, true);
    }

    /**
     * The booking a scan names: the QR token when the value is a uuid, or
     * the reference number printed on the pass. qr_token is a uuid column,
     * so a typed reference must never be compared against it.
     */
    private static function findAppointment(string $token): ?Appointment
    {
        return Appointment::query()
            ->with(['user.studentProfile'])
            ->where(function ($query) use ($token) {
                $query->where('reference_code', $token);

                if (Str::isUuid($token)) {
                    $query->orWhere('qr_token', $token);
                }
            })
            ->first();
    }

    private static function studentByPass(string $token): ?User
    {
        if (! Str::isUuid($token)) {
            return null;
        }

        return StudentProfile::query()
            ->where('pass_token', $token)
            ->whereHas('user', fn ($query) => $query->where('role', 'student'))
            ->with('user.studentProfile')
            ->first()
            ?->user;
    }

    private static function searchStudent(string $term): ?User
    {
        return User::query()
            ->with('studentProfile')
            ->where('role', 'student')
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhereHas('studentProfile', fn ($profile) => $profile->where('student_id', 'like', "%{$term}%")))
            ->first();
    }

    /**
     * This office's history with the student, newest first, so the desk can
     * see whether today's visit is their first.
     */
    private static function officeAppointments(User $student, string $office): Collection
    {
        return $student->appointments()
            ->where('office', $office)
            ->orderByDesc('date')
            ->orderByDesc('time_slot')
            ->limit(5)
            ->get();
    }
}
