<?php

namespace App\Support;

use App\Models\DocumentRequest;
use App\Models\User;
use App\Notifications\RegistrarRequestAlert;
use Illuminate\Support\Facades\Notification;

/**
 * Broadcast a document-request alert to the registrar desk.
 */
class RegistrarNotifier
{
    /**
     * Send one alert to every registrar account.
     *
     * The desk is staffed by whoever happens to be logged in, so an alert
     * aimed at a single account would sit unread while another registrar
     * waits on it. Every approved, active registrar gets the row — and only
     * registrars: students receive their own notification from the caller,
     * and nothing else in the office is part of this pipeline.
     */
    public static function alert(DocumentRequest $documentRequest, string $title, string $message): void
    {
        $recipients = User::query()
            ->where('role', User::ROLE_REGISTRAR)
            ->where('is_active', true)
            ->where('approval_status', User::APPROVAL_APPROVED)
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new RegistrarRequestAlert($documentRequest, $title, $message),
        );
    }
}
