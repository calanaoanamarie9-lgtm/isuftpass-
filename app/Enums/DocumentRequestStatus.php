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
            self::AWAITING_OR_VALIDATION => 'Awaiting OR Validation',
            self::PROCESSING => 'Processing',
            self::FOR_SIGNATURE => 'Approved',
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
     * Official Receipt, the student presents that receipt back for
     * validation, and only then does credential processing begin.
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
