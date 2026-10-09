<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Office;

/**
 * The bookable time slots of an office, derived from the hours it keeps.
 *
 * Slots run hourly from the opening time to the closing time, so how many
 * slots an office offers in a day is whatever its hours add up to: open it
 * later or close it later and the day grows. The hours are edited on the
 * office's own Availability page, and every list in the system — the
 * booking form, the calendar, the availability checkboxes, the capacity
 * table — reads the same answer from here.
 *
 * An office with no row yet falls back to Appointment::TIME_SLOTS, the
 * default eight-to-five day.
 */
class TimeSlots
{
    public const DEFAULT_OPEN = '08:00';
    public const DEFAULT_CLOSE = '17:00';

    /**
     * Hourly slot labels for an office, e.g. '08:00 AM - 09:00 AM'.
     *
     * @return list<string>
     */
    public static function forOffice(?string $office): array
    {
        $hours = self::hoursFor($office);

        return self::between($hours['open'], $hours['close']);
    }

    /**
     * The hours an office keeps, in H:i, falling back to the default day
     * when it has saved none yet.
     *
     * @return array{open: string, close: string}
     */
    public static function hoursFor(?string $office): array
    {
        $row = $office === null ? null : self::officeRow($office);

        return [
            'open' => $row?->open_time ?: self::DEFAULT_OPEN,
            'close' => $row?->close_time ?: self::DEFAULT_CLOSE,
        ];
    }

    /**
     * Hourly labels between two H:i times. A range that cannot hold a whole
     * hour — closing at or before opening, an unreadable time — falls back to
     * the default day instead of handing the office an empty schedule.
     *
     * @return list<string>
     */
    public static function between(string $open, string $close): array
    {
        $start = self::minutes($open);
        $end = self::minutes($close);

        if ($start < 0 || $end < 0 || $end - $start < 60) {
            $start = self::minutes(self::DEFAULT_OPEN);
            $end = self::minutes(self::DEFAULT_CLOSE);
        }

        $slots = [];

        for ($from = $start; $from + 60 <= $end; $from += 60) {
            $slots[] = self::label($from) . ' - ' . self::label($from + 60);
        }

        return $slots ?: Appointment::TIME_SLOTS;
    }

    /**
     * Exact office name first, then a loose match, so an appointment carrying
     * a label like "University Library" still finds the Library row.
     */
    private static function officeRow(string $office): ?Office
    {
        return Office::query()->where('name', $office)->first()
            ?? Office::query()->where('name', 'like', '%' . $office . '%')->first();
    }

    /**
     * H:i in 24-hour form to minutes past midnight, or -1 when unreadable.
     */
    private static function minutes(string $time): int
    {
        if (! preg_match('/^(\d{1,2}):(\d{2})$/', trim($time), $matches)) {
            return -1;
        }

        $hour = (int) $matches[1];
        $minute = (int) $matches[2];

        if ($hour > 23 || $minute > 59) {
            return -1;
        }

        return $hour * 60 + $minute;
    }

    /**
     * Minutes past midnight to the label appointments store: '08:00 AM'.
     */
    private static function label(int $minutes): string
    {
        $minutes = (($minutes % 1440) + 1440) % 1440;
        $hour24 = intdiv($minutes, 60);
        $hour12 = $hour24 % 12 ?: 12;

        return sprintf('%02d:%02d %s', $hour12, $minutes % 60, $hour24 < 12 ? 'AM' : 'PM');
    }
}
