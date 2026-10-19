<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Bookings whose student never arrived.
 *
 * An appointment is a promise to be somewhere at a named hour. Once that hour
 * - plus a grace for the student running a little late - has passed with
 * nobody checked in, nobody is coming and no one is going to remember to
 * close it out, so it is cancelled the way a registrar would cancel it: the
 * same status, the same cancelled_at, the same in-app notice. Left alone it
 * would sit on every list as "Confirmed" for ever, and its pass would keep
 * reading as good at the gate long after the visit was over.
 *
 * The scheduled endpoint runs it across the day; both QR desks also run it
 * over the single booking under the scanner, so a pass is never read as
 * valid a moment after it stopped being one.
 */
class AppointmentSweep
{
    /**
     * Minutes past the end of the office's window in which a student may
     * still arrive and be checked in before the booking counts as missed.
     */
    public const GRACE_MINUTES = 30;

    /**
     * The statuses that mean "this student is still expected" - exactly the
     * two a desk may check a student in for. Checked in, completed, already
     * cancelled, rescheduled and for-reschedule each say something else has
     * happened, and none of them is a student who failed to turn up.
     */
    private const EXPECTED = [
        AppointmentStatus::PENDING->value,
        AppointmentStatus::CONFIRMED->value,
    ];

    /**
     * True when the appointment's promised time has run out with no arrival.
     */
    public static function hasMissed(Appointment $appointment): bool
    {
        if (! in_array((string) $appointment->status, self::EXPECTED, true)) {
            return false;
        }

        // They did turn up - whether or not anyone ever closed the visit out.
        if ($appointment->checked_in_at !== null) {
            return false;
        }

        $deadline = self::deadline($appointment);

        return $deadline !== null && now()->greaterThan($deadline);
    }

    /**
     * Cancel the booking if, and only if, its time has run out. Returns
     * whether this call is the one that cancelled it, so a caller can report
     * an outcome and a second scan a second later stays silent.
     */
    public static function cancelIfMissed(Appointment $appointment): bool
    {
        if (! self::hasMissed($appointment)) {
            return false;
        }

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ]);

        $appointment->user?->notify(new AppointmentCancelledNotification($appointment));

        AuditLogger::log(
            'appointment.auto-cancelled',
            'Auto-cancelled appointment '.$appointment->reference_code
            .' - the scheduled time passed with no check-in.'
        );

        return true;
    }

    /**
     * Every booking still waiting on a student whose time has run out, as of
     * now. Returns how many were cancelled.
     *
     * The date is compared through whereDate() like every other date filter
     * in the app, and status is indexed, so the scan stays cheap even on a
     * database full of history.
     */
    public static function run(): int
    {
        $candidates = Appointment::query()
            ->with('user')
            ->whereIn('status', self::EXPECTED)
            ->whereNull('checked_in_at')
            ->whereDate('date', '<=', now()->toDateString())
            ->get();

        $cancelled = 0;

        foreach ($candidates as $appointment) {
            if (self::cancelIfMissed($appointment)) {
                $cancelled++;
            }
        }

        return $cancelled;
    }

    /**
     * The instant this booking stops being honourable.
     *
     * A date column carries no time zone, so a booking that named nothing
     * but a day runs out at the end of that day the way the application
     * reads dates - the same rule every "is today" check here uses. An hour
     * the office chose is a wall clock on campus, so it is read on the
     * appointment's own date in Manila and only then compared with now.
     */
    private static function deadline(Appointment $appointment): ?Carbon
    {
        $date = $appointment->date?->toDateString();

        if ($date === null) {
            return null;
        }

        $closing = $appointment->status === AppointmentStatus::CONFIRMED->value
            ? self::windowCloses($appointment)
            : null;

        // No hour was ever promised: a request still waiting on the office
        // survives the whole day the office could still act on it.
        if ($closing === null) {
            return Carbon::parse($date, 'UTC')->endOfDay();
        }

        return Carbon::parse($date.' '.$closing, CampusTime::TIMEZONE)
            ->addMinutes(self::GRACE_MINUTES);
    }

    /**
     * When the office's window shuts, as a clock time on the day itself:
     * the end it stored, else a start - or the lone "come at" time the older
     * approvals answered with - which is worth one slot, an hour, to arrive.
     */
    private static function windowCloses(Appointment $appointment): ?string
    {
        if ($appointment->end_time !== null) {
            return self::clock($appointment->end_time)?->format('H:i:s');
        }

        $opening = $appointment->start_time ?? $appointment->confirmed_time;

        return $opening === null
            ? null
            : self::clock($opening)?->addHour()->format('H:i:s');
    }

    /**
     * A stored clock time, or null when it is not one. A booking carrying
     * something unreadable runs out at the end of its day rather than
     * throwing mid-scan and refusing the gate a pass over it.
     */
    private static function clock(string $value): ?Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
