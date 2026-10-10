<?php

namespace App\Notifications;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * In-system alert for the student: the registrar approved their request
 * and a payment now stands between them and pick-up.
 *
 * Approval and payment are two statuses apart in the pipeline, so the
 * due-payment step gets its own notification — paired with the "Payment
 * recorded" status the cashier sends later when it is settled.
 */
class PaymentPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public DocumentRequest $documentRequest,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment pending',
            'message' => sprintf(
                '%s has been approved. A payment of ₱%s is due at the Cashier Office before the document can be released.',
                $this->documentRequest->request_number,
                number_format($this->documentRequest->totalFee(), 2),
            ),
            'url' => route('student.documents.show', $this->documentRequest),
        ];
    }
}
