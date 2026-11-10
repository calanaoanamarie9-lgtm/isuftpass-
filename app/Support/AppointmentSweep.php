<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentCancelled;
use App\Models\Appointment;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Bookings whose student never arrived in time.
 *
 * An appointment is a promise to be somewhere inside a named window - the
 * office allocates 8:00 AM - 9:00 AM and the student is expected at the gate
 * while it runs. The window shuts with no grace: a scan at 9:01 is already
 * late, so the booking is cancelled the way a registrar would cancel it - the
 * same status, the same cancelled_at, the same in-app notice. Left alone it
 * would sit on every list as "Confirmed" for ever, and its pass would keep
 * reading as good at the gate long after the visit was over.
 *
 * The scheduled endpoint runs it across the day; both QR desks also run it
 * over the single booking under the scanner, so a pass is never read as
 * valid a moment after it stopped being one. Document requests never reach
 * this class - they carry no window, and the late-arrival policy applies to
 * online appointments only.
 */
class AppointmentSweep
{
    /**
     * The statuses that mean "this student is still expected" - exactly the
     * two a desk may check a student in for. Checked in, completed, already
     * cancelled, rescheduled and for-reschedule each say something else has
     * happened, and none of them is a student who failed to turn up.
     *
     * Public because the guard's overview counts today's expected visitors
     * from this same list: a metric and a scan verdict that disagreed about
     * who was still coming would be worse than no metric at all.
     */
    public const EXPECTED = [
        AppointmentStatus::PENDING->value,
        AppointmentStatus::CONFIRMED->value,
    ];

    /**
     * True when the appointment's promised window has run out with no
     * arrival - late counts as missed from the first second past the slot.
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

        return $deadline !== null && now()->greaterThanOrEqualTo($deadline);
    }

    /**
     * Cancel the booking if, and only if, its time has run out. Returns
     * whether this call is the one that cancelled it, so a caller can report
     * an outcome and a second scan a second later stays silent.
     *
     * A desk that just scanned a pass passes its own reason (a scan is an
     * arrival, so the late wording is the true one); the scheduled sweep
     * scans nothing and keeps the default no-show wording.
     */
    public static function cancelIfMissed(Appointment $appointment, ?string $reason = null): bool
    {
        if (! self::hasMissed($appointment)) {
            return false;
        }

        $reason ??= 'Scheduled time passed without check-in.';

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED->value,
            'cancelled_at' => now(),
            'notes' => $appointment->notes ? $appointment->notes."\nCancellation Reason: ".$reason : 'Cancellation Reason: '.$reason,
        ]);

        $appointment->user?->notify(new AppointmentCancelledNotification($appointment, $reason));
        if ($appointment->user) {
            SafeMailer::send($appointment->user, new AppointmentCancelled($appointment, $reason));
        }

        AuditLogger::log(
            'appointment.auto-cancelled',
            'Auto-cancelled appointment '.$appointment->reference_code.' - '.$reason
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
     *
     * The window itself is the deadline: once its closing minute is over the
     * booking is late, with no grace tacked on.
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

        return Carbon::parse($date.' '.$closing, CampusTime::TIMEZONE)->addMinute();
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
