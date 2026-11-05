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

        // Name the requester as the office staff will meet them: their own
        // name plus their classification (Student, Alumni, Parent, Guest),
        // never the generic "A student" — the desk needs to know whose
        // booking it is before the student walks in.
        $requester = $this->appointment->user;

        return [
            'title' => 'New Appointment Request',
            'message' => sprintf(
                '%s (%s) has requested an appointment with the %s office on %s',
                $requester->name,
                $requester->classificationLabel(),
                $this->appointment->office,
                $this->appointment->date->format('F j, Y'),
            ),
            'url' => $url,
        ];
    }
}
