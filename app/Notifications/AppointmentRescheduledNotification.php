<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentRescheduledNotification extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Appointment Rescheduled',
            'message' => 'Your '.$this->appointment->office.' appointment has been moved to '.$this->appointment->date->format('F j, Y').'. The time will be set once it is re-approved. Reason: '.$this->appointment->reschedule_reason.'.',
            'url' => route('student.appointments.index'),
        ];
    }
}
