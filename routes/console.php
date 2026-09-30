<?php

use App\Jobs\SendAppointmentReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// App timezone is UTC (config/app.php), and Manila is UTC+8, so 00:00 UTC
// == 08:00 AM Manila — the time appointment reminders should go out.
Schedule::daily()->at('00:00')->job(new SendAppointmentReminders);
