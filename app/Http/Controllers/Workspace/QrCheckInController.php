<?php

namespace App\Http\Controllers\Workspace;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\AppointmentSweep;
use App\Support\CheckInList;
use App\Support\QrPayload;
use App\Support\WorkspaceQrVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The scan endpoint behind "QR Scanner & Check-in", served to all seven
 * offices and departments: the route is declared once inside $workspaceRoutes,
 * so /cici/qr, /ssc/qr, /library/qr … all POST here and all get the same
 * behaviour.
 *
 * A desk asks three questions in order — whose visit is this, is it booked
 * with THIS office, and may it be marked as arrived right now — and every
 * answer comes back as JSON for the one shared page to render.
 *
 * The office always comes from officeScope(), never from the request, so a
 * scanned token can never move a visit across offices: an SSC desk scanning
 * a CICI appointment is told whose it is, and nothing is written.
 */
class QrCheckInController extends Controller
{
    /**
     * Statuses a desk may accept a student into. Anything else — cancelled,
     * no show, already rescheduled — is reported, never overwritten.
     */
    private const CHECKABLE = [
        AppointmentStatus::PENDING->value,
        AppointmentStatus::CONFIRMED->value,
    ];

    public function checkIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:300'],
        ]);

        $office = auth()->user()->officeScope();
        $token = QrPayload::token($data['token']);

        // Both identifiers this desk understands — the appointment's qr_token
        // and the student's pass_token — are uuid columns, so a typed
        // reference or a stray keystroke is answered here instead of being
        // sent to Postgres as an invalid uuid cast.
        if (! Str::isUuid($token)) {
            return $this->rejected('This QR does not match any appointment or student pass.');
        }

        // An appointment pass carries /verify/appointment/{qr_token}.
        $appointment = Appointment::query()
            ->with('user.studentProfile')
            ->where('qr_token', $token)
            ->first();

        if ($appointment) {
            return $this->checkInAppointment($appointment, $office);
        }

        // The student's digital ID carries /verify/pass/{pass_token} and is
        // presented at the desk just as often, so it resolves to whatever the
        // student booked with this office today.
        $student = StudentProfile::query()
            ->where('pass_token', $token)
            ->whereHas('user', fn ($query) => $query->where('role', 'student'))
            ->with('user.studentProfile')
            ->first()
            ?->user;

        if (! $student instanceof User) {
            return $this->rejected('This QR does not match any appointment or student pass.');
        }

        $appointment = WorkspaceQrVerification::todayFor($student, $office);

        if (! $appointment) {
            return $this->rejected(
                "{$student->name} has no appointment with {$office} today.",
                $student->name
            );
        }

        return $this->checkInAppointment($appointment, $office);
    }

    private function checkInAppointment(Appointment $appointment, string $office): JsonResponse
    {
        $student = $appointment->user->name;

        if ($appointment->office !== $office) {
            return $this->rejected(
                "This visit is booked with {$appointment->office}, not {$office}.",
                $student
            );
        }

        if ($appointment->status === AppointmentStatus::CHECKED_IN->value) {
            $at = $appointment->checked_in_at?->format('g:i A');

            return $this->answered(
                $appointment,
                checkedIn: true,
                message: $at ? "Already checked in at {$at}." : 'Already checked in.',
            );
        }

        // The same close-out the gate does: a window that ran out with no
        // arrival is cancelled before the desk decides what this scan
        // means - the scan is the arrival, and it is late - so a missed
        // visit is reported as cancelled instead of being walked in as if
        // the student were on time.
        AppointmentSweep::cancelIfMissed(
            $appointment,
            'Arrived after the allocated time slot.'
        );

        if (! in_array($appointment->status, self::CHECKABLE, true)) {
            return $this->answered(
                $appointment,
                checkedIn: false,
                message: 'Cannot check in — this visit is '.$appointment->statusLabel().'.',
            );
        }

        if (! $appointment->date->isToday()) {
            $when = $appointment->date->format('M d, Y');

            return $this->answered(
                $appointment,
                checkedIn: false,
                message: $appointment->date->isFuture()
                    ? "Scheduled for {$when} — check in on the appointment date."
                    : "This visit was scheduled for {$when}.",
            );
        }

        $appointment->update([
            'status' => AppointmentStatus::CHECKED_IN->value,
            'checked_in_at' => now(),
        ]);

        return $this->answered(
            $appointment,
            checkedIn: true,
            message: "Checked in at {$appointment->checked_in_at->format('g:i A')}.",
        );
    }

    /**
     * The QR was recognised: the desk can see whose it is, even when the visit
     * itself could not be marked as arrived.
     */
    private function answered(Appointment $appointment, bool $checkedIn, string $message): JsonResponse
    {
        return response()->json([
            'valid' => true,
            'checkedIn' => $checkedIn,
            'message' => $message,
            'student' => $appointment->user->name,
            'studentId' => $appointment->user->studentProfile?->student_id,
            'purpose' => $appointment->purpose,
            'office' => $appointment->office,
            'schedule' => $appointment->date->format('M d, Y').' · '.$appointment->timeToCome(),
            'status' => $appointment->statusLabel(),
            'checkedInAt' => $appointment->checked_in_at?->format('g:i A'),
            'checkIns' => CheckInList::today(auth()->user()->officeScope()),
        ]);
    }

    /**
     * The QR could not be used. $student is only filled when the desk was
     * recognised but had nothing to check the visitor into.
     */
    private function rejected(string $message, ?string $student = null): JsonResponse
    {
        return response()->json([
            'valid' => false,
            'checkedIn' => false,
            'message' => $message,
            'student' => $student,
            'checkIns' => CheckInList::today(auth()->user()->officeScope()),
        ]);
    }
}
