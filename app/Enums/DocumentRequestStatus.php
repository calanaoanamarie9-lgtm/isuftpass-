<?php

namespace App\Enums;

enum DocumentRequestStatus: string
{
    case SUBMITTED = 'submitted';
    case PROCESSING = 'processing';
    case FOR_SIGNATURE = 'for_signature';
    case READY_FOR_PICKUP = 'ready_for_pickup';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Label used across the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Submitted',
            self::PROCESSING => 'Paid',
            self::FOR_SIGNATURE => 'Approved',
            self::READY_FOR_PICKUP => 'Ready for Pick-up',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Ordered tracking pipeline steps (excluding terminal states).
     *
     * The registrar approves first; only then can the cashier record the
     * payment, and the document cannot be released until that payment is in.
     */
    public static function pipeline(): array
    {
        return [
            self::SUBMITTED,
            self::FOR_SIGNATURE,
            self::PROCESSING,
            self::READY_FOR_PICKUP,
            self::COMPLETED,
        ];
    }
}