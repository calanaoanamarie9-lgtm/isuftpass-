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
        // Same rule as the appointment alert: name the requester and their
        // classification (Student, Alumni, Parent, Guest) so the desk knows
        // whose request it is without opening it first.
        $requester = $this->documentRequest->user;

        return [
            'title' => 'New Document Request',
            'message' => sprintf(
                '%s (%s) has submitted document request %s for %s',
                $requester->name,
                $requester->classificationLabel(),
                $this->documentRequest->request_number,
                $this->documentRequest->documentsSummary(),
            ),
            'url' => route('registrar.document-requests.index'),
        ];
    }
}