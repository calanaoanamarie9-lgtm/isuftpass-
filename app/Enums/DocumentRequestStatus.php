<?php

namespace App\Enums;

enum DocumentRequestStatus: string
{
    case SUBMITTED = 'submitted';
    case AWAITING_OR_VALIDATION = 'awaiting_or_validation';
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
            self::FOR_SIGNATURE => 'Approved',
            // "Paid": the cashier has recorded the payment and issued the OR.
            // The registrar must explicitly start processing from here.
            self::AWAITING_OR_VALIDATION => 'Paid',
            self::PROCESSING => 'Processing',
            self::READY_FOR_PICKUP => 'Ready for Pick-up',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Ordered tracking pipeline steps (excluding terminal states).
     *
     * Follows the Functional Flow Chart in the Delivery of Service —
     * Release of Credentials: the registrar verifies the request and
     * issues an Order of Payment, the cashier collects and issues an
     * Official Receipt (marking it Paid), and only then does the registrar
     * explicitly start credential processing.
     *
     * The AWAITING_OR_VALIDATION case keeps its legacy value (it is stored
     * in the database) but now represents the "Paid" stage — payment is in,
     * waiting on the registrar to begin processing.
     */
    public static function pipeline(): array
    {
        return [
            self::SUBMITTED,
            self::FOR_SIGNATURE,
            self::AWAITING_OR_VALIDATION,
            self::PROCESSING,
            self::READY_FOR_PICKUP,
            self::COMPLETED,
        ];
    }
}
