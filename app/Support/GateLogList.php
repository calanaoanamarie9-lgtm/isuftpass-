<?php

namespace App\Support;

use App\Models\GateLog;

/**
 * The guard desk's view of gate_logs.
 *
 * Only rows written by the gate desk carry a pass_type; the older rows are
 * phone scans of a QR that never reached a gate, so the daily table and
 * its counters read guard rows alone.
 */
class GateLogList
{
    /** What each pass type is called on the desk's table. */
    public const TYPES = [
        'visitor' => 'Visitor Gate Pass',
        'document' => 'Digital Claim Pass',
        'appointment' => 'Appointment Visit Pass',
        'student' => 'Digital ID',
    ];

    public static function label(?string $type): string
    {
        return self::TYPES[$type] ?? 'Gate Pass';
    }

    /**
     * Today's gate sessions, newest first, optionally narrowed by the name
     * or reference code typed into the desk's search box.
     */
    public static function today(?string $search = null): array
    {
        $search = trim((string) $search);

        return GateLog::query()
            ->with('guardUser')
            ->whereNotNull('pass_type')
            ->whereBetween('scanned_at', CampusTime::todayBoundsUtc())
            ->when($search !== '', function ($query) use ($search) {
                // lower() instead of ILIKE so the same query runs on the
                // production PostgreSQL and the SQLite the tests use.
                $needle = '%'.strtolower($search).'%';

                $query->where(function ($inner) use ($needle) {
                    $inner->whereRaw("lower(coalesce(person_name, '')) like ?", [$needle])
                        ->orWhereRaw("lower(coalesce(reference_code, '')) like ?", [$needle]);
                });
            })
            ->orderByDesc('scanned_at')
            ->limit(100)
            ->get()
            ->map(fn (GateLog $log) => [
                'id' => $log->id,
                'person' => $log->person_name,
                'reference' => $log->reference_code,
                'type' => $log->pass_type,
                'typeLabel' => self::label($log->pass_type),
                'destination' => $log->destination,
                'purpose' => $log->purpose,
                'inside' => $log->isOpen(),
                'timeIn' => CampusTime::display($log->time_in),
                'timeOut' => CampusTime::display($log->time_out),
                'time' => CampusTime::display($log->scanned_at),
                'guard' => $log->guardUser?->name,
            ])
            ->all();
    }

    /**
     * How many people are inside right now, per today's open sessions.
     */
    public static function insideCount(): int
    {
        return GateLog::query()
            ->whereNotNull('pass_type')
            ->whereNull('time_out')
            ->whereBetween('scanned_at', CampusTime::todayBoundsUtc())
            ->count();
    }

    /**
     * Every gate session recorded today.
     */
    public static function todayCount(): int
    {
        return GateLog::query()
            ->whereNotNull('pass_type')
            ->whereBetween('scanned_at', CampusTime::todayBoundsUtc())
            ->count();
    }
}
