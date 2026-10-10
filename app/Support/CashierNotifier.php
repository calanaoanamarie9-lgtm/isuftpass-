<?php

namespace App\Support;

use App\Models\DocumentRequest;
use App\Models\User;
use App\Notifications\CashierPaymentDueAlert;
use Illuminate\Support\Facades\Notification;

/**
 * Broadcast a payment-pending alert to the cashier desk.
 */
class CashierNotifier
{
    /**
     * Send one alert to every cashier account.
     *
     * Same rule as RegistrarNotifier: the desk is staffed by whoever
     * happens to be logged in, so an alert aimed at a single account would
     * sit unread while another cashier waits on it. Every active, approved
     * cashier gets the row - and only cashiers: collecting the payment is
     * their step alone.
     */
    public static function alert(DocumentRequest $documentRequest, string $title, string $message): void
    {
        $recipients = User::query()
            ->where('role', User::ROLE_CASHIER)
            ->where('is_active', true)
            ->where('approval_status', User::APPROVAL_APPROVED)
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new CashierPaymentDueAlert($documentRequest, $title, $message),
        );
    }
}
