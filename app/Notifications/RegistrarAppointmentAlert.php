<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * In-system alert for the registrar desk.
 *
 * Students book into any office, but the registrar owns the appointment
 * pipeline — confirming, rescheduling, cancelling — so every new booking
 * lands here. The link opens the appointment on their own page, where the
 * action buttons live.
 */
class RegistrarAppointmentAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public string $title,
        public string $message,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message . " ({$this->appointment->reference_code})",
            'url' => route('registrar.appointments.show', $this->appointment),
        ];
    }
}
