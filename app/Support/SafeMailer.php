<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class SafeMailer
{
    /**
     * Best-effort mail delivery.
     *
     * Every call site persists its record *before* sending the email, so an
     * outage at the mail provider (missing API key, rejected sender, provider
     * down, timeout) must never turn a successful submission into a 500.
     * Failures are reported to the log instead of bubbling up to the user,
     * and the caller falls back to the database notification it writes next.
     *
     * @return bool true when the message was handed to the transport
     */
    public static function send(mixed $recipient, Mailable $mailable): bool
    {
        try {
            Mail::to($recipient)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            report(self::describe($mailable, $recipient, $e));

            return false;
        }
    }

    /**
     * A bare transport exception does not say *which* notification was
     * dropped or who it was for, which is exactly what you need when
     * reading a log a week later. Wrap it so the message names both.
     */
    private static function describe(Mailable $mailable, mixed $recipient, \Throwable $e): \Throwable
    {
        return new \RuntimeException(
            sprintf(
                'Email not delivered: %s -> %s (%s)',
                $mailable::class,
                self::recipientLabel($recipient),
                $e->getMessage(),
            ),
            0,
            $e,
        );
    }

    /**
     * Call sites pass either a User model or a raw address string.
     */
    private static function recipientLabel(mixed $recipient): string
    {
        if (is_string($recipient)) {
            return $recipient;
        }

        if (is_object($recipient) && isset($recipient->email)) {
            return (string) $recipient->email;
        }

        return get_debug_type($recipient);
    }
}
