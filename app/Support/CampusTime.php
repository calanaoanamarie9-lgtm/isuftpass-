<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * The campus runs on Philippine time; the database runs on UTC.
 *
 * Every timestamp the application writes is UTC (config/app.php says so on
 * purpose, and the reminder schedule depends on it), so this class does not
 * change how anything is stored. It exists for the one desk that is read in
 * real time beside a clock on the wall: the gate. A guard looking at a scan
 * logged "10:28 PM" while the wall says 6:28 AM is looking at a bug, so the
 * guard desk converts to campus time for both the times it shows and the day
 * of scans it covers, while the rows underneath stay UTC like everything else.
 */
class CampusTime
{
    public const TIMEZONE = 'Asia/Manila';

    public static function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    /**
     * The campus date, as a "Y-m-d" string, for comparing against the date
     * columns (valid_until and friends) that are stored without a time.
     */
    public static function today(): string
    {
        return self::now()->toDateString();
    }

    /**
     * A UTC datetime localised for display, or null when there is none.
     */
    public static function display(?object $moment, string $format = 'g:i A'): ?string
    {
        if ($moment === null) {
            return null;
        }

        return Carbon::instance($moment)->timezone(self::TIMEZONE)->format($format);
    }

    /**
     * The UTC instants a campus day begins and ends at.
     *
     * The gate log is filtered on the UTC timestamps it stores, so the day
     * boundaries have to be converted rather than compared to a bare date:
     * 00:00 in Manila is 16:00 UTC the day before.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function todayBoundsUtc(): array
    {
        $start = self::now()->startOfDay();

        return [$start->copy()->utc(), $start->copy()->endOfDay()->utc()];
    }
}
