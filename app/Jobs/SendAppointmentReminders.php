<?php

namespace App\Jobs;

use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use App\Support\SafeMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAppointmentReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $appointments = Appointment::with('user')
            ->where('date', $tomorrow)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($appointments as $appointment) {
            // Only mark as reminded once delivery succeeded, so a transient
            // SMTP failure is retried on the next scheduled run instead of
            // silently dropping every remaining reminder in this batch.
            if (SafeMailer::send($appointment->user->email, new AppointmentReminder($appointment))) {
                $appointment->update(['reminder_sent_at' => now()]);
            }
        }
    }
}
