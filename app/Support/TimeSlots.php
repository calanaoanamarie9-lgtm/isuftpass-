<?php

namespace App\Support;

use App\Models\Office;

/**
 * The hours an office keeps.
 *
 * An office's opening and closing time still bounds what it may promise: the
 * approve popover only offers times inside the office's own day, and an
 * approval cannot name an hour the office is shut. Offices with no row yet
 * fall back to the default eight-to-five day.
 *
 * (This class once also generated the hourly *slots* an office booked by.
 * Slots are gone — a booking names a day, and the office answers with the
 * window it wants the student in — so only the hours remain.)
 */
class TimeSlots
{
    public const DEFAULT_OPEN = '08:00';

    public const DEFAULT_CLOSE = '17:00';

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
     * Exact office name first, then a loose match, so an appointment carrying
     * a label like "University Library" still finds the Library row.
     */
    private static function officeRow(string $office): ?Office
    {
        return Office::query()->where('name', $office)->first()
            ?? Office::query()->where('name', 'like', '%'.$office.'%')->first();
    }
}
