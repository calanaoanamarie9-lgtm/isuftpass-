<?php

namespace App\Notifications;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** 
 * In-system alert sent to the registrar office when a student submits a document request. 
 * The student also receives DocumentRequestReceivedNotification, but this one lands 
 * on the registrar's notifications page where they can manage the request pipeline. 
 */
class DocumentRequestOfficeNotification extends Notification
{
    use Queueable;

    public function __construct(public DocumentRequest $documentRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Document Request',
            'message' => 'A student has submitted document request ' . $this->documentRequest->request_number . ' for ' . $this->documentRequest->documentsSummary(),
            'url' => route('registrar.document-requests.index'),
        ];
    }
}