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
     * SMTP outage (bad credentials, provider down, timeout) must never turn a
     * successful submission into a 500. Failures are reported to the log
     * instead of bubbling up to the user.
     *
     * @return bool true when the message was handed to the transport
     */
    public static function send(mixed $recipient, Mailable $mailable): bool
    {
        try {
            Mail::to($recipient)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
