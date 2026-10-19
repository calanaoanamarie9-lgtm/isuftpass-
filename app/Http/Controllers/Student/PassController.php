<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PassController extends Controller
{
    /**
     * My Digital Student ID — container page listing every active
     * transaction QR. Each QR belongs only to its own transaction;
     * there is no standalone identity/main QR code.
     */
    public function show(): View
    {
        $user = Auth::user();

        // One scannable QR per active document request.
        $documentRequests = $user->documentRequests()
            ->active()
            ->latest()
            ->get()
            ->map(fn ($request) => $request->setAttribute(
                'qr_data_uri',
                $this->makeQr(\App\Support\QrUrl::to('/verify/document/' . $request->claim_token))
            ));

        // One scannable QR per upcoming appointment.
        $appointments = $user->appointments()
            ->upcoming()
            ->orderBy('date')
            ->orderBy('time_slot')
            ->get()
            ->map(fn ($appointment) => $appointment->setAttribute(
                'qr_data_uri',
                $this->makeQr(\App\Support\QrUrl::to('/verify/appointment/' . $appointment->qr_token))
            ));

        return view('student.pass', [
            'student' => $user,
            'profile' => $user->studentProfile,
            'avatar' => $user->studentProfile?->avatar_url,
            'documentRequests' => $documentRequests,
            'appointments' => $appointments,
        ]);
    }

    private function makeQr(string $payload): string
    {
        return (new SvgWriter())->write(new QrCode($payload))->getDataUri();
    }
}