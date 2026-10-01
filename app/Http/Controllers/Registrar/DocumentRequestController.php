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
use App\Support\AuditLogger;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentRequestController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'archived' ? 'archived' : 'active';

        $query = DocumentRequest::with(['user', 'documents'])->latest();

        if ($tab === 'archived') {
            $query->whereIn('status', [
                DocumentRequestStatus::COMPLETED->value,
                DocumentRequestStatus::CANCELLED->value,
            ]);
        } elseif ($status = $request->query('status')) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', [
                DocumentRequestStatus::SUBMITTED->value,
                DocumentRequestStatus::PROCESSING->value,
                DocumentRequestStatus::FOR_SIGNATURE->value,
                DocumentRequestStatus::READY_FOR_PICKUP->value,
            ]);
        }

        return view('registrar.document-requests.index', [
            'requests' => $query->paginate(10)->withQueryString(),
            'statuses' => collect(DocumentRequestStatus::cases())
                ->mapWithKeys(fn (DocumentRequestStatus $s) => [$s->value => $s->label()])
                ->all(),
            'filters' => array_filter([
                'status' => $request->query('status'),
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

    public function next(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        abort_if(! $documentRequest->isActive(), 404);

        $next = match ($documentRequest->status) {
            DocumentRequestStatus::SUBMITTED->value => [
                DocumentRequestStatus::PROCESSING->value,
                'processing_at',
                'is now being processed',
            ],
            DocumentRequestStatus::PROCESSING->value => [
                DocumentRequestStatus::FOR_SIGNATURE->value,
                'for_signature_at',
                'is now for signature',
            ],
            DocumentRequestStatus::FOR_SIGNATURE->value => [
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
            $data = $request->validate([
                'release_date' => ['nullable', 'date'],
                'release_time' => ['nullable', 'date_format:H:i'],
            ]);

            $attributes[$timestamp] = isset($data['release_date'])
                ? \Illuminate\Support\Carbon::parse($data['release_date'] . ' ' . ($data['release_time'] ?? '08:00'))
                : now();
        } else {
            $attributes[$timestamp] = now();
        }

        $documentRequest->forceFill($attributes)->save();

        $label = DocumentRequestStatus::from($status)->label();

        AuditLogger::log('request.advanced', 'Advanced request ' . $documentRequest->request_number . ' to ' . $label . '.');

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
                DocumentRequestStatus::READY_FOR_PICKUP->value,
                DocumentRequestStatus::COMPLETED->value,
            ])],
            'release_date' => ['nullable', 'date'],
            'release_time' => ['nullable', 'date_format:H:i'],
        ]);

        $target = DocumentRequestStatus::from($data['status']);

        [$timestamp, $message] = match ($target) {
            DocumentRequestStatus::PROCESSING => ['processing_at', 'is now being processed'],
            DocumentRequestStatus::FOR_SIGNATURE => ['for_signature_at', 'is now for signature'],
            DocumentRequestStatus::READY_FOR_PICKUP => ['ready_at', 'is now ready for pick-up'],
            DocumentRequestStatus::COMPLETED => ['completed_at', 'has been completed'],
            default => [null, null],
        };

        abort_if($timestamp === null, 400, 'This status cannot be applied.');

        $attributes = [
            'status' => $target->value,
            $timestamp => $target === DocumentRequestStatus::READY_FOR_PICKUP && isset($data['release_date'])
                ? \Illuminate\Support\Carbon::parse($data['release_date'] . ' ' . ($data['release_time'] ?? '08:00'))
                : now(),
        ];

        $documentRequest->forceFill($attributes)->save();

        AuditLogger::log('request.status_updated', 'Set request ' . $documentRequest->request_number . ' to ' . $target->label() . '.');

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

        return redirect()
            ->back()
            ->with('status', "Request {$target->label()}. The student has been notified by email and in the system.");
    }

    public function cancel(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        abort_if(! $documentRequest->isActive(), 404);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $reason = trim($data['reason'] ?? '') ?: null;

        $documentRequest->forceFill([
            'status' => DocumentRequestStatus::CANCELLED->value,
            'cancelled_at' => now(),
            'rejection_reason' => $reason,
        ])->save();

        AuditLogger::log('request.cancelled', 'Rejected request ' . $documentRequest->request_number . ($reason ? '. Reason: ' . $reason : ''));

        $notificationMessage = $reason
            ? 'Your document request was rejected due to: ' . $reason
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
}