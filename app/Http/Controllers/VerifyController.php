<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\GateLog;
use App\Models\StudentProfile;
use App\Support\QrUrl;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;

/**
 * Public scan targets encoded inside QR codes.
 *
 * Any phone camera scanning a QR lands here directly, and every QR carries
 * exactly one unique transaction identifier:
 *   /verify/pass/{token}         -> student identity pass (identity only, no
 *                                   transaction list - each document request
 *                                   and appointment has its own QR)
 *   /verify/document/{token}     -> that one request's Digital Claim Pass
 *   /verify/appointment/{token}  -> that one appointment's pass
 * Staff who are logged in are bounced to the registrar verification page.
 */
class VerifyController extends Controller
{
    public function pass(Request $request, string $token)
    {
        $profile = StudentProfile::query()
            ->where('pass_token', $token)
            ->whereHas('user', fn ($q) => $q->where('role', 'student'))
            ->with(['user.studentProfile'])
            ->first();

        if (! $profile) {
            return response()->view('verify.invalid', [
                'message' => 'This QR pass is not registered to any student.',
            ], 404);
        }

        $student = $profile->user;

        $gateLog = GateLog::create([
            'user_id' => $student->id,
            'direction' => 'in',
            'gate' => 'Main Gate',
            'scanned_at' => now(),
        ]);

        if ($this->isStaff($request)) {
            return redirect()->route('registrar.qr.index', ['q' => $token]);
        }

        // Identity only. The pass QR identifies the student, never a
        // transaction: listing their documents/appointments here was the
        // bulk-display bug, because a scan must present the one item its
        // payload points at. Every transaction carries its own QR that opens
        // that single item's page instead.
        return view('verify.pass', [
            'student' => $student,
            'profile' => $profile,
            'scan' => $gateLog,
        ]);
    }

    public function document(Request $request, string $token)
    {
        $documentRequest = DocumentRequest::query()
            ->where('claim_token', $token)
            ->with(['user.studentProfile', 'documents'])
            ->first();

        if (! $documentRequest) {
            return response()->view('verify.invalid', [
                'message' => 'This claim QR does not match any document request.',
            ], 404);
        }

        if ($this->isStaff($request)) {
            return redirect()->route('registrar.qr.index', ['q' => $token]);
        }

        // The scanned page shows the same claim pass — QR included — as the
        // student's view details, so the payload is rebuilt here.
        $qrCodeDataUri = (new SvgWriter)
            ->write(new QrCode(
                QrUrl::to('/verify/document/'.$documentRequest->claim_token)
            ))
            ->getDataUri();

        return view('verify.document', [
            'documentRequest' => $documentRequest,
            'qrCodeDataUri' => $qrCodeDataUri,
        ]);
    }

    /**
     * /verify/appointment/{token} — what the appointment QR encodes.
     *
     * The payload carries this appointment's own qr_token, and the printed
     * card also shows its reference number, so either form resolves to the
     * one booking: the page below is the student's own Appointment
     * Verification Pass, rendered from the same shared card component, so
     * a scanner reads exactly what the student was handed at booking.
     */
    public function appointment(Request $request, string $token)
    {
        $appointment = Appointment::query()
            ->where(fn ($query) => $query
                ->where('qr_token', $token)
                ->orWhere('reference_code', $token))
            ->with(['user.studentProfile'])
            ->first();

        if (! $appointment || ! $appointment->isUpcoming()) {
            return response()->view('verify.invalid', [
                'message' => 'This QR does not match any active appointment.',
            ], 404);
        }

        $student = $appointment->user;

        $gateLog = GateLog::create([
            'user_id' => $student->id,
            'direction' => 'in',
            'gate' => 'Main Gate',
            'scanned_at' => now(),
        ]);

        // Staff scanning lands on the registrar's verification station,
        // which renders this same pass card for the appointment it found.
        if ($this->isStaff($request)) {
            return redirect()->route('registrar.qr.index', ['q' => $token]);
        }

        return view('verify.appointment', [
            'appointment' => $appointment,
            'student' => $student,
            'scan' => $gateLog,
        ]);
    }

    private function isStaff(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && in_array($user->role, ['registrar', 'admin'], true);
    }
}
