<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The email the registration popup promises: an office / staff applicant
 * is told at signup that an email arrives once an administrator approves
 * the account, and this is that email. Sent best-effort through
 * SafeMailer from the admin's approve action.
 */
class OfficeAccountApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $applicant) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Office Account Approved - ISUFSTPASS',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.office-account-approved',
        );
    }
}
