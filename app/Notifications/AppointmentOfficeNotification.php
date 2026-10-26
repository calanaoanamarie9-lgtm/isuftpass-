<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** 
 * In-system alert sent to the registrar office when a student books an appointment. 
 * The student also receives AppointmentBookedNotification, but this one lands 
 * on the registrar's notifications page where they can manage the approval queue. 
 */
class AppointmentOfficeNotification extends Notification
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
            'title' => 'New Appointment Request',
            'message' => 'A student has requested an appointment with the ' . $this->appointment->office . ' office on ' . $this->appointment->date->format('F j, Y'),
            'url' => route('registrar.appointments.index'),
        ];
    }
}