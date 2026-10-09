<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $when = $this->appointment->time_slot
            ? ' at ' . $this->appointment->time_slot
            : '';

        return [
            'title' => 'Appointment Cancelled',
            'message' => 'Your ' . $this->appointment->office . ' appointment scheduled on ' . $this->appointment->date->format('F j, Y') . $when . ' has been cancelled.',
            'url' => route('student.appointments.index', ['tab' => 'cancelled']),
        ];
    }
}