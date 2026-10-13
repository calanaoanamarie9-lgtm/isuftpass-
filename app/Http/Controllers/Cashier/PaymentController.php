<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\DocumentRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Notifications\DocumentRequestStatusNotification;
use App\Support\AuditLogger;
use App\Support\RegistrarNotifier;
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

            // Cashier records payment → the request moves straight to
            // PROCESSING. The Official Receipt is authoritative; no
            // separate student submission or registrar validation step is
            // required. This matches the "automatic paid → processing" flow.
            'status' => DocumentRequestStatus::PROCESSING->value,
            'processing_at' => now(),
            'or_validated_at' => now(),
        ];

        $documentRequest->update($attributes);

        $statusLabel = DocumentRequestStatus::tryFrom($documentRequest->status)?->label()
            ?? ucfirst(str_replace('_', ' ', $documentRequest->status));

        AuditLogger::log('payment.recorded', 'Recorded payment for request '.$documentRequest->request_number.' (OR No. '.$documentRequest->or_number.', ₱'.number_format($documentRequest->totalFee(), 2).'). Status is now '.$statusLabel.'.');

        $documentRequest->user->notify(new DocumentRequestStatusNotification(
            $documentRequest,
            'Payment recorded — processing started',
            'Your payment has been recorded (OR No. '.$documentRequest->or_number.'). Your credential is now being prepared for release.',
        ));

        // Notify the registrar that payment was recorded and the request
        // has moved directly to processing — no OR validation step needed.
        RegistrarNotifier::alert(
            $documentRequest,
            'Payment recorded — moved to processing',
            'Payment of ₱'.number_format($documentRequest->totalFee(), 2).' (OR No. '.$documentRequest->or_number.') has been recorded. The request is now under processing.'
        );

        return redirect()
            ->route('cashier.payments.receipt', $documentRequest)
            ->with('status', 'Payment recorded for '.$documentRequest->request_number.'. Print the Official Receipt for the student; they present it to the Registrar for validation.');
    }

    /**
     * The Official Receipt issued for a recorded payment — printed and
     * handed to the student, who presents it to the Registrar for
     * validation. Reachable from the moment of recording and reprinted
     * later from the payment history.
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
