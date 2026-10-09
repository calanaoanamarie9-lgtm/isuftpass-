<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentApprovedNotification extends Notification
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
        return [
            'title' => 'Appointment Approved',
            'message' => 'Your ' . $this->appointment->office . ' appointment on ' . $this->appointment->date->format('F j, Y') . ' is approved. Come at ' . $this->appointment->timeToCome() . '.',
            'url' => route('student.appointments.index'),
        ];
    }
}
