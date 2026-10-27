<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\DocumentRequestStatus;
use App\Http\Controllers\Controller;
use App\Mail\DocumentRequestStatusUpdate;
use App\Models\DocumentRequest;
use App\Notifications\DocumentRequestStatusNotification;
use App\Support\AuditLogger;
use App\Support\RegistrarNotifier;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function pending(Request $request): View
    {
        // The queue is the approved-but-unpaid backlog. Payment comes after
        // the registrar's approval, so a request still awaiting it never
        // shows up here.
        $query = DocumentRequest::query()
            ->with(['user.studentProfile', 'documents'])
            ->where('status', DocumentRequestStatus::FOR_SIGNATURE->value)
            ->unpaid()
            ->latest();

        $search = trim((string) $request->query('q'));

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('request_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        return view('cashier.payments.pending', [
            'requests' => $query->paginate(12)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function record(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        if ($documentRequest->isPaid()) {
            return back()->with('error', 'Payment for this request has already been recorded.');
        }

        // Approval comes first: the registrar signs off before the cashier
        // collects, so a request still waiting on that approval cannot be
        // paid here.
        if ($documentRequest->status !== DocumentRequestStatus::FOR_SIGNATURE->value) {
            return back()->with('error', sprintf(
                '%s has not been approved by the registrar yet, so payment cannot be recorded. Ask the registrar to approve it first.',
                $documentRequest->request_number,
            ));
        }

        $validated = $request->validate([
            'or_number' => ['required', 'string', 'max:50', 'unique:document_requests,or_number'],
        ]);

        $attributes = [
            'paid_at' => now(),
            'or_number' => strtoupper($validated['or_number']),

            // The cashier's OR is what confirms payment, so the request
            // parks at PAID (the AWAITING_OR_VALIDATION value). It does NOT
            // move on to processing here: the registrar must explicitly
            // click "Start Processing" to begin credential preparation.
            'status' => DocumentRequestStatus::AWAITING_OR_VALIDATION->value,
        ];

        $documentRequest->update($attributes);

        $statusLabel = DocumentRequestStatus::tryFrom($documentRequest->status)?->label()
            ?? ucfirst(str_replace('_', ' ', $documentRequest->status));

        AuditLogger::log('payment.recorded', 'Recorded payment for request '.$documentRequest->request_number.' (OR No. '.$documentRequest->or_number.', ₱'.number_format($documentRequest->totalFee(), 2).'). Status is now '.$statusLabel.'.');

        $documentRequest->user->notify(new DocumentRequestStatusNotification(
            $documentRequest,
            'Payment confirmed',
            'Your payment has been confirmed (OR No. '.$documentRequest->or_number.'). The Registrar\'s Office will start processing your credential.',
        ));

        SafeMailer::send(
            $documentRequest->user,
            new DocumentRequestStatusUpdate(
                $documentRequest,
                'Payment Confirmed',
                'Your payment of ₱'.number_format($documentRequest->totalFee(), 2).' (OR No. '.$documentRequest->or_number.') has been recorded and confirmed by the Cashier\'s Office. The Registrar\'s Office will now proceed with processing your requested document.'
            )
        );

        // The registrar must know the instant the OR is recorded — it is the
        // signal that the request is paid and ready for them to start
        // processing.
        RegistrarNotifier::alert(
            $documentRequest,
            'Payment recorded — ready to start processing',
            'Payment of ₱'.number_format($documentRequest->totalFee(), 2).' (OR No. '.$documentRequest->or_number.') has been recorded. The request is now Paid — click "Start Processing" to begin credential processing.'
        );

        return redirect()
            ->route('cashier.payments.receipt', $documentRequest)
            ->with('status', 'Payment recorded for '.$documentRequest->request_number.'. The request is now marked Paid — the Registrar can start processing it.');
    }

    /**
     * The Official Receipt issued for a recorded payment — printed and
     * handed to the student. Recording it marks the request Paid; the
     * registrar starts processing from there. Reachable from the moment of
     * recording and reprinted later from the payment history.
     */
    public function receipt(DocumentRequest $documentRequest): View
    {
        abort_unless($documentRequest->isPaid(), 404);

        return view('cashier.payments.receipt', [
            'request' => $documentRequest->load(['user', 'documents']),
            'cashier' => Auth::user(),
        ]);
    }

    public function history(Request $request): View
    {
        $period = in_array($request->query('period'), ['week', 'month', 'year', 'custom'], true)
            ? $request->query('period')
            : null;

        $from = trim((string) $request->query('from')) ?: null;
        $to = trim((string) $request->query('to')) ?: null;

        $query = DocumentRequest::query()
            ->with(['user.studentProfile', 'documents'])
            ->paid()
            ->latest('paid_at');
        $this->applyPeriodFilter($query, $period, $from, $to);

        $search = trim((string) $request->query('q'));

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('request_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $totalQuery = DocumentRequest::query()->paid()->with('documents');
        $this->applyPeriodFilter($totalQuery, $period, $from, $to);
        $totalCollected = $totalQuery->get()->sum(fn ($r) => $r->totalFee());

        return view('cashier.payments.history', [
            'requests' => $query->paginate(12)->withQueryString(),
            'search' => $search,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'totalCollected' => $totalCollected,
        ]);
    }

    private function applyPeriodFilter($query, ?string $period, ?string $from, ?string $to): void
    {
        if ($period === 'custom' && ($from || $to)) {
            if ($from) {
                try {
                    $query->where('paid_at', '>=', Carbon::parse($from)->startOfDay());
                } catch (\Throwable) {
                }
            }
            if ($to) {
                try {
                    $query->where('paid_at', '<=', Carbon::parse($to)->endOfDay());
                } catch (\Throwable) {
                }
            }

            return;
        }

        if ($period === 'week') {
            $query->where('paid_at', '>=', now()->startOfWeek());
        } elseif ($period === 'month') {
            $query->where('paid_at', '>=', now()->startOfMonth());
        } elseif ($period === 'year') {
            $query->where('paid_at', '>=', now()->startOfYear());
        }
    }
}
