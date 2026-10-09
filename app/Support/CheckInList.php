<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;

/**
 * Today's arrivals at one desk, newest first.
 *
 * The scanner page shows this on first paint and the check-in endpoint hands
 * it back after every scan, so both callers must read the same rows: the list
 * is what proves to the desk that a scan actually recorded something.
 */
class CheckInList
{
    public static function today(string $office): array
    {
        return Appointment::query()
            ->with('user')
            ->where('office', $office)
            ->whereNotNull('checked_in_at')
            ->whereDate('checked_in_at', now()->toDateString())
            ->orderByDesc('checked_in_at')
            ->limit(50)
            ->get()
            ->map(fn (Appointment $appointment) => [
                'student' => $appointment->user->name,
                'time' => $appointment->checked_in_at->format('g:i A'),
                'slot' => $appointment->timeToCome(),
                'status' => $appointment->statusLabel(),
            ])
            ->all();
    }
}
