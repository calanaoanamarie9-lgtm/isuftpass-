<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Support\QrPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QrVerifyController extends Controller
{
    public function index(Request $request): View
    {
        $result = null;
        $documentRequest = null;
        $appointment = null;
        $showTransactionLists = false;
        $query = trim((string) $request->query('q'));

        if ($query !== '') {
            ['pass' => $passToken, 'claim' => $claimToken, 'appointment' => $appointmentToken] = QrPayload::extract($query);

            // A transaction token resolves to ITS transaction alone: the page
            // shows that single Digital Claim Pass / appointment card and
            // nothing else from the student's history.
            if ($claimToken) {
                $documentRequest = DocumentRequest::query()
                    ->with(['user.studentProfile', 'documents'])
                    ->where('claim_token', $claimToken)
                    ->first();
            }

            if (! $documentRequest && $appointmentToken) {
                $appointment = $this->findAppointment($appointmentToken);
            }

            // An identity pass scan shows the student card only - no dump of
            // their documents and appointments.
            if (! $documentRequest && ! $appointment && $passToken) {
                $result = User::query()
                    ->with('studentProfile')
                    ->where('role', 'student')
                    ->whereHas('studentProfile', fn ($p) => $p->where('pass_token', $passToken))
                    ->first();
            }

            // A bare UUID may also belong to an appointment QR.
            if (! $result && ! $documentRequest && ! $appointment && $passToken === $query) {
                $appointment = $this->findAppointment($passToken);
            }

            if (! $result && ! $claimToken && ! $appointmentToken && ! $passToken) {
                // Plain text (or unknown payload): a deliberate name / email
                // search - the only result that may list recent transactions.
                $result = User::query()
                    ->with([
                        'studentProfile',
                        'documentRequests' => fn ($q) => $q->latest()->limit(3),
                        'appointments' => fn ($q) => $q->latest()->limit(3),
                    ])
                    ->where('role', 'student')
                    ->where(fn ($w) => $w
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%"))
                    ->first();
                $showTransactionLists = $result !== null;
            }
        }

        return view('registrar.qr-verification.index', [
            'student' => $result,
            'documentRequest' => $documentRequest,
            'appointment' => $appointment,
            'query' => $query,
            'showTransactionLists' => $showTransactionLists,
        ]);
    }

    /**
     * The booking a scan names: the QR token when the value is a uuid, or
     * the reference number printed on the pass. qr_token is a uuid column,
     * so a typed reference must never be compared against it.
     */
    private function findAppointment(string $token): ?Appointment
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
}
