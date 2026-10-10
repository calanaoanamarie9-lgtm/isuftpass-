<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * The in-app half of the approval: the portal bell entry that accompanies
 * the OfficeAccountApproved email, for the applicant who is already
 * roaming the site. The registration popup promises an email; the admin's
 * approve action sends both.
 */
class OfficeAccountApprovedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Office Account Approved',
            'message' => 'Your '.($notifiable->office ?: 'office').' office / staff account has been approved. You may now sign in to ISUFSTPASS.',
            'url' => route('login'),
        ];
    }
}
