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

            // The registrar approved first and the request is parked at
            // "Approved": recording the payment is exactly what moves it on
            // to "Paid". A cancelled request is never resurrected by a late
            // payment — those never reach this queue.
            'status' => DocumentRequestStatus::PROCESSING->value,
            'processing_at' => $documentRequest->processing_at ?? now(),
        ];

        $documentRequest->update($attributes);

        $statusLabel = DocumentRequestStatus::tryFrom($documentRequest->status)?->label()
            ?? ucfirst(str_replace('_', ' ', $documentRequest->status));

        AuditLogger::log('payment.recorded', 'Recorded payment for request ' . $documentRequest->request_number . ' (OR No. ' . $documentRequest->or_number . ', ₱' . number_format($documentRequest->totalFee(), 2) . '). Status is now ' . $statusLabel . '.');

        $documentRequest->user->notify(new DocumentRequestStatusNotification(
            $documentRequest,
            'Payment recorded',
            'Your payment has been recorded by the Cashier Office. You may claim your document when the status is Ready for Pick-up.',
        ));

        // The registrar cannot see the request move until someone tells them.
        // Approval already happened, so this is the hand-off back to them:
        // payment landed, the request now reads Paid, and the release step
        // in their queue is unlocked.
        RegistrarNotifier::alert(
            $documentRequest,
            'Payment recorded — ready to release',
            'Payment of ₱' . number_format($documentRequest->totalFee(), 2) . ' (OR No. ' . $documentRequest->or_number . ') has been recorded. The request is now Paid and waiting in your queue to be released.'
        );

        return back()->with('status', 'Payment recorded for ' . $documentRequest->request_number . '. The registrar has been notified.');
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
                    $query->where('paid_at', '>=', \Illuminate\Support\Carbon::parse($from)->startOfDay());
                } catch (\Throwable) {
                }
            }
            if ($to) {
                try {
                    $query->where('paid_at', '<=', \Illuminate\Support\Carbon::parse($to)->endOfDay());
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