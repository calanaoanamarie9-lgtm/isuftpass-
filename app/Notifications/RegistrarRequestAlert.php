<?php

namespace App\Notifications;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * In-system alert for the registrar desk.
 *
 * The student-facing notifications point at student.documents.show, which is
 * behind role:student — a registrar clicking one would land on a 403. So the
 * registrar gets its own class with its own target: the request in their own
 * queue, where the pipeline buttons live.
 */
class RegistrarRequestAlert extends Notification
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
            'url' => route('registrar.document-requests.show', $this->documentRequest),
        ];
    }
}
