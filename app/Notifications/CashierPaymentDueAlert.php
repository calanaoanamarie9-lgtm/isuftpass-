<?php

namespace App\Notifications;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * In-system alert for the cashier desk.
 *
 * The registrar's approval is the hand-off point: the request now waits in
 * the pending-payments queue and the cashier has to know it arrived without
 * watching the registrar's screen. The link opens their own queue filtered
 * to this one request.
 */
class CashierPaymentDueAlert extends Notification
{
    use Queueable;

    public function __construct(
        public DocumentRequest $documentRequest,
        public string $title,
        public string $message,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message . " ({$this->documentRequest->request_number})",
            'url' => route('cashier.payments.pending', ['q' => $this->documentRequest->request_number]),
        ];
    }
}
