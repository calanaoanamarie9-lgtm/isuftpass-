<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\DocumentRequestStatus;
use App\Http\Controllers\Controller;
use App\Mail\DocumentRequestCompleted;
use App\Mail\DocumentRequestReadyForPickup;
use App\Mail\DocumentRequestRejected;
use App\Mail\DocumentRequestStatusUpdate;
use App\Models\DocumentRequest;
use App\Notifications\DocumentRequestStatusNotification;
use App\Notifications\PaymentPendingNotification;
use App\Support\AuditLogger;
use App\Support\CashierNotifier;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentRequestController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'archived' ? 'archived' : 'active';

        $query = DocumentRequest::with(['user', 'documents'])->latest();

        if ($tab === 'archived') {
            $archived = [
                DocumentRequestStatus::COMPLETED->value,
                DocumentRequestStatus::CANCELLED->value,
            ];

            // The dashboard's "Issued Documents" card links straight to the
            // completed ones, so the history tab honours a status filter too.
            $status = $request->query('status');

            $query->whereIn('status', in_array($status, $archived, true) ? [$status] : $archived);
        } elseif ($status = $request->query('status')) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', [
                DocumentRequestStatus::SUBMITTED->value,
                DocumentRequestStatus::AWAITING_OR_VALIDATION->value,
                DocumentRequestStatus::PROCESSING->value,
                DocumentRequestStatus::FOR_SIGNATURE->value,
                DocumentRequestStatus::READY_FOR_PICKUP->value,
            ]);
        }

        // Applicant classification filter. "Student" is every account that
        // signed up as a student (registration_type is left null for them);
        // alumni were typed into registration_type at signup. Parents and
        // guests are not offered here: they have no document-request standing,
        // so filtering by them could only ever empty the list. Their requests
        // stay visible under "All User Types".
        $userTypes = [
            'student' => 'Student',
            'alumni' => 'Alumni',
        ];

        $userType = $request->query('user_type');
        $userType = array_key_exists($userType, $userTypes) ? $userType : null;

        if ($userType === 'student') {
            $query->whereHas('user', fn ($q) => $q->whereNull('registration_type'));
        } elseif ($userType !== null) {
            // "Parent" also covers the legacy 'guardian' registration_type.
            $registrations = $userType === 'parent' ? ['parent', 'guardian'] : [$userType];
            $query->whereHas('user', fn ($q) => $q->whereIn('registration_type', $registrations));
        }

        return view('registrar.document-requests.index', [
            'requests' => $query->paginate(10)->withQueryString(),
            'statuses' => collect(DocumentRequestStatus::cases())
                ->mapWithKeys(fn (DocumentRequestStatus $s) => [$s->value => $s->label()])
                ->all(),
            'userTypes' => $userTypes,
            'filters' => array_filter([
                'status' => $request->query('status'),
                'user_type' => $userType,
            ]),
            'tab' => $tab,
        ]);
    }

    public function show(DocumentRequest $documentRequest): View
    {
        $documentRequest->load(['user.studentProfile', 'documents']);

        return view('registrar.document-requests.show', [
            'request' => $documentRequest,
            'pipeline' => DocumentRequestStatus::pipeline(),
        ]);
    }

    /**
     * Everything past approval is gated on payment: the cashier has to have
     * recorded it (paid_at) before a request can be released or completed.
     * Approval itself deliberately is NOT gated — the registrar signs off
     * first and the cashier collects afterwards. Rejecting stays available
     * only while the request is unpaid, so an unpaid request is never left
     * stuck with no way out; once the money is in, refunding it is the way
     * back.
     *
     * @return RedirectResponse|null the redirect to send while still unpaid
     */
    private function paymentGate(DocumentRequest $documentRequest, string $action): ?RedirectResponse
    {
        if ($documentRequest->isPaid()) {
            return null;
        }

        return back()->with('error', sprintf(
            'Payment for %s has not been recorded yet, so this request cannot be %s. Ask the cashier to record the payment first.',
            $documentRequest->request_number,
            $action,
        ));
    }

    /**
     * Approval hands the request over to the cashier's pending-payments
     * queue — tell the desk it has arrived, and tell the student what is
     * now due of them. It is also where the Order of Payment is issued:
     * the official billing statement the student settles at the Cashier's
     * Office, per the Release of Credentials flow.
     */
    private function onApproved(DocumentRequest $documentRequest): void
    {
        $documentRequest->issueOrderOfPayment();

        $documentRequest->user->notify(new PaymentPendingNotification($documentRequest));

        CashierNotifier::alert(
            $documentRequest,
            'Approved — payment pending',
            sprintf(
                '%s has been approved and is awaiting payment of ₱%s.',
                $documentRequest->student_name,
                number_format($documentRequest->totalFee(), 2),
            )
        );
    }

    public function next(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        abort_if(! $documentRequest->isActive(), 404);

        // Hand-off point: the registrar has approved, and moving it further
        // is the cashier's write. There is nothing left for the registrar
        // to advance until that payment lands.
        if ($documentRequest->status === DocumentRequestStatus::FOR_SIGNATURE->value) {
            return back()->with('error', sprintf(
                '%s is approved and waiting for the cashier to record the payment.',
                $documentRequest->request_number,
            ));
        }

        $next = match ($documentRequest->status) {
            DocumentRequestStatus::SUBMITTED->value => [
                DocumentRequestStatus::FOR_SIGNATURE->value,
                'for_signature_at',
                'has been approved',
            ],
            // Paid: the cashier has recorded the OR. The registrar must
            // explicitly click "Start Processing" to begin credential work.
            DocumentRequestStatus::AWAITING_OR_VALIDATION->value => [
                DocumentRequestStatus::PROCESSING->value,
                'processing_at',
                'is now being processed',
            ],
            DocumentRequestStatus::PROCESSING->value => [
                DocumentRequestStatus::READY_FOR_PICKUP->value,
                'ready_at',
                'is now ready for pick-up',
            ],
            DocumentRequestStatus::READY_FOR_PICKUP->value => [
                DocumentRequestStatus::COMPLETED->value,
                'completed_at',
                'has been completed',
            ],
            default => null,
        };

        abort_if($next === null, 400, 'This request cannot be advanced.');

        [$status, $timestamp, $message] = $next;

        $attributes = [
            'status' => $status,
        ];

        if ($status === DocumentRequestStatus::READY_FOR_PICKUP->value) {
            if ($blocked = $this->paymentGate($documentRequest, 'released')) {
                return $blocked;
            }

            $data = $request->validate([
                'release_date' => ['nullable', 'date'],
                'release_time' => ['nullable', 'date_format:H:i'],
            ]);

            $attributes[$timestamp] = isset($data['release_date'])
                ? Carbon::parse($data['release_date'].' '.($data['release_time'] ?? '08:00'))
                : now();
        } else {
            $attributes[$timestamp] = now();
        }

        $documentRequest->forceFill($attributes)->save();

        $label = DocumentRequestStatus::from($status)->label();

        AuditLogger::log('request.advanced', 'Advanced request '.$documentRequest->request_number.' to '.$label.'.');

        $documentRequest->user->notify(
            new DocumentRequestStatusNotification($documentRequest, $label, "Your document request {$message}.")
        );

        if ($status === DocumentRequestStatus::READY_FOR_PICKUP->value) {
            SafeMailer::send($documentRequest->user,
                new DocumentRequestReadyForPickup($documentRequest)
            );
        } elseif ($status === DocumentRequestStatus::COMPLETED->value) {
            SafeMailer::send($documentRequest->user,
                new DocumentRequestCompleted($documentRequest)
            );
        } else {
            SafeMailer::send($documentRequest->user,
                new DocumentRequestStatusUpdate($documentRequest, $label, "Your document request {$message}.")
            );
        }

        // Approval is the hand-off: the cashier's queue gains the request
        // and the student learns what payment is now due.
        if ($status === DocumentRequestStatus::FOR_SIGNATURE->value) {
            $this->onApproved($documentRequest);
        }

        return redirect()
            ->back()
            ->with('status', "Request {$label}. The student has been notified by email and in the system.");
    }

    public function updateStatus(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        abort_if(! $documentRequest->isActive(), 404);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                DocumentRequestStatus::PROCESSING->value,
                DocumentRequestStatus::FOR_SIGNATURE->value,
                DocumentRequestStatus::AWAITING_OR_VALIDATION->value,
                DocumentRequestStatus::READY_FOR_PICKUP->value,
                DocumentRequestStatus::COMPLETED->value,
            ])],
            'release_date' => ['nullable', 'date'],
            'release_time' => ['nullable', 'date_format:H:i'],
        ]);

        $target = DocumentRequestStatus::from($data['status']);

        // This endpoint is not linked from the UI, but it must not become a
        // side door around the payment requirement: everything past approval
        // (paid, released, completed) needs the cashier's payment on file.
        // Approving is the one step that is not gated.
        if ($target !== DocumentRequestStatus::FOR_SIGNATURE
            && ($blocked = $this->paymentGate($documentRequest, 'advanced'))) {
            return $blocked;
        }

        // Paid carries no stamp of its own: the payment timestamp is the
        // cashier's write, set when the OR was issued.
        [$timestamp, $message] = match ($target) {
            DocumentRequestStatus::PROCESSING => ['processing_at', 'is now being processed'],
            DocumentRequestStatus::FOR_SIGNATURE => ['for_signature_at', 'has been approved'],
            DocumentRequestStatus::AWAITING_OR_VALIDATION => [null, 'has been paid'],
            DocumentRequestStatus::READY_FOR_PICKUP => ['ready_at', 'is now ready for pick-up'],
            DocumentRequestStatus::COMPLETED => ['completed_at', 'has been completed'],
            default => [null, null],
        };

        abort_if($message === null, 400, 'This status cannot be applied.');

        $attributes = [
            'status' => $target->value,
        ];

        if ($timestamp !== null) {
            $attributes[$timestamp] = $target === DocumentRequestStatus::READY_FOR_PICKUP && isset($data['release_date'])
                ? Carbon::parse($data['release_date'].' '.($data['release_time'] ?? '08:00'))
                : now();
        }

        $documentRequest->forceFill($attributes)->save();

        AuditLogger::log('request.status_updated', 'Set request '.$documentRequest->request_number.' to '.$target->label().'.');

        $documentRequest->user->notify(
            new DocumentRequestStatusNotification($documentRequest, $target->label(), "Your document request {$message}.")
        );

        if ($target === DocumentRequestStatus::READY_FOR_PICKUP) {
            SafeMailer::send($documentRequest->user, new DocumentRequestReadyForPickup($documentRequest));
        } elseif ($target === DocumentRequestStatus::COMPLETED) {
            SafeMailer::send($documentRequest->user, new DocumentRequestCompleted($documentRequest));
        } else {
            SafeMailer::send($documentRequest->user,
                new DocumentRequestStatusUpdate($documentRequest, $target->label(), "Your document request {$message}.")
            );
        }

        // Approval is the hand-off: the cashier's queue gains the request
        // and the student learns what payment is now due.
        if ($target === DocumentRequestStatus::FOR_SIGNATURE) {
            $this->onApproved($documentRequest);
        }

        return redirect()
            ->back()
            ->with('status', "Request {$target->label()}. The student has been notified by email and in the system.");
    }

    /**
     * The registrar's copy of the Order of Payment — same sheet the student
     * prints, so the office can reissue one that was lost.
     */
    public function orderOfPayment(DocumentRequest $documentRequest): View
    {
        abort_if($documentRequest->op_number === null, 404);

        return view('documents.order-of-payment', [
            'request' => $documentRequest->load(['user.studentProfile', 'documents']),
        ]);
    }

    public function cancel(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        abort_if(! $documentRequest->isActive(), 404);

        // A paid request is past rejecting: the cashier has collected the
        // money and issued the Official Receipt, so settling it back is a
        // refund, not a rejection. The page hides the button; this is what
        // still holds if the form is posted by hand.
        if ($documentRequest->isPaid()) {
            return back()->with('error', sprintf(
                'Payment for %s has already been recorded, so the request cannot be rejected. Refund the payment first if it must not go ahead.',
                $documentRequest->request_number,
            ));
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $reason = trim($data['reason'] ?? '') ?: null;

        $documentRequest->forceFill([
            'status' => DocumentRequestStatus::CANCELLED->value,
            'cancelled_at' => now(),
            'rejection_reason' => $reason,
        ])->save();

        AuditLogger::log('request.cancelled', 'Rejected request '.$documentRequest->request_number.($reason ? '. Reason: '.$reason : ''));

        $notificationMessage = $reason
            ? 'Your document request was rejected due to: '.$reason
            : 'Your document request has been rejected by the registrar office.';

        $documentRequest->user->notify(
            new DocumentRequestStatusNotification(
                $documentRequest,
                'Rejected',
                $notificationMessage
            )
        );

        SafeMailer::send($documentRequest->user,
            new DocumentRequestRejected($documentRequest, $reason)
        );

        return redirect()
            ->back()
            ->with('status', 'Request rejected. The student has been notified by email and in the system.');
    }

    /**
     * Removing a request is the registrar's call — a duplicate, a test entry,
     * or one the student already settled elsewhere. The pivot is released
     * first so no link row is left stranded, and the removal is written to
     * the audit trail because it leaves nothing else behind.
     */
    public function destroy(DocumentRequest $documentRequest): RedirectResponse
    {
        AuditLogger::log('request.deleted', 'Registrar deleted request '.$documentRequest->request_number.'.');

        $documentRequest->documents()->detach();
        $documentRequest->delete();

        return redirect()
            ->route('registrar.document-requests.index')
            ->with('status', 'Request deleted.');
    }
}
