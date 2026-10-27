<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

/** 
 * In-system alert sent to the office / department and registrar when a student books an appointment.
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
        $url = route('registrar.appointments.index');

        if ($notifiable instanceof User) {
            $prefix = strtolower($notifiable->officeScope());
            if (Route::has($prefix . '.appointments.index')) {
                $url = route($prefix . '.appointments.index');
            } elseif ($notifiable->role === User::ROLE_OFFICE && Route::has('workspace.appointments.index')) {
                $url = route('workspace.appointments.index');
            }
        }

        return [
            'title' => 'New Appointment Request',
            'message' => 'A student has requested an appointment with the ' . $this->appointment->office . ' office on ' . $this->appointment->date->format('F j, Y'),
            'url' => $url,
        ];
    }
}
