<?php

namespace App\Support;

use Illuminate\Mail\Mailable;

class SafeMailer
{
    /**
     * Mail delivery is DISABLED.
     *
     * Render's free tier blocks outbound SMTP (ports 25/465/587) since
     * 2025-09-26, and no HTTPS mail API is configured for this app, so a
     * message could never reach a mailbox anyway. Every call site writes a
     * database notification immediately after this call, so users are still
     * notified in-app through the notification bell — only the copy by email
     * is gone.
     *
     * Returning true keeps every caller on its normal path: the reminder job
     * marks reminders as handled instead of re-processing the same
     * appointments forever.
     *
     * To bring email back: point MAIL_MAILER at a transport reachable from
     * Render (an HTTPS API such as Resend or Postmark — plain SMTP stays
     * blocked on the free tier) and restore the Mail::to() line below.
     *
     * @return bool whether the message was delivered (now always: no send)
     */
    public static function send(mixed $recipient, Mailable $mailable): bool
    {
        return true;
    }
}
