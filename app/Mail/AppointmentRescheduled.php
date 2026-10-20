<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Support\QrUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentRescheduled extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The link the student's QR pass carries, so the email can take them
     * straight to the code that is now valid for the new schedule.
     */
    public string $qrUrl;

    public function __construct(public Appointment $appointment, public ?string $oldSchedule = null)
    {

        $this->oldSchedule ??= $this->appointment->original_date
            ? $this->appointment->original_date->format('F j, Y').' · '.($this->appointment->original_time_slot ?: $this->appointment->time_slot)
            : 'previous schedule';

        $this->qrUrl = QrUrl::to('/verify/appointment/'.$this->appointment->qr_token);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Rescheduled - ISUFSTPASS',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.appointment-rescheduled',
        );
    }
}
