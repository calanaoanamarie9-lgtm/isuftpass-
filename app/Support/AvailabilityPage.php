<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\SlotAvailability;
use Carbon\Carbon;

/**
 * The data behind the Availability page, for whichever office is asking.
 *
 * The registrar and every office workspace answer the same question - which
 * days are open, what was set on each of them, and who is coming - so the
 * answers are built once here, for an office named, rather than twice: once
 * for the registrar and once for each of the eight workspaces.
 */
class AvailabilityPage
{
    /**
     * Everything the page draws.
     *
     * @return array<string, mixed>
     */
    public static function for(string $office, ?string $month = null): array
    {
        $month = self::resolveMonth($month);

        return [
            'office' => Office::where('name', $office)->first(),
            'timeSlots' => TimeSlots::forOffice($office),
            'month' => $month,
            'schedule' => self::schedule($office, $month),
            'rosterByDate' => self::roster($office),
        ];
    }

    /**
     * The dates in a month that carry an override, each with what was set.
     *
     * One entry per date - only the badge naming how that day was set. The
     * clock times behind it are deliberately left out: the roster numbers a
     * day's bookings first come, first served, so a cell has no hour of its
     * own to print. The month is bounded rather than running from today
     * onwards, so a date earlier in the month still reads truthfully when
     * the grid is turned back to it.
     *
     * @return array<int, array{id: string, date: string, type: string, typeLabel: string}>
     */
    public static function schedule(string $office, string $month): array
    {
        $row = Office::where('name', $office)->first();
        $slots = TimeSlots::forOffice($office);
        $total = count($slots);

        // The '!' keeps the month pinned to its first day, so a month name
        // typed on the 31st cannot overflow into the next one.
        $monthStart = Carbon::createFromFormat('!Y-m', $month);
        $monthEnd = $monthStart->copy()->endOfMonth();

        $groups = SlotAvailability::query()
            ->when($row, fn ($query) => $query->where('office_id', $row->id))
            ->whereNotNull('date')
            ->whereDate('date', '>=', $monthStart->toDateString())
            ->whereDate('date', '<=', $monthEnd->toDateString())
            ->orderBy('date')
            ->get()
            ->groupBy(fn (SlotAvailability $rule) => Carbon::parse($rule->date)->toDateString());

        return $groups->map(function ($group, string $date) use ($slots, $total) {
            $available = $group->filter(
                fn (SlotAvailability $rule) => $rule->status === SlotAvailability::STATUS_AVAILABLE
                    && in_array($rule->time_slot, $slots, true)
            );

            $type = $available->isEmpty()
                ? 'closed'
                : ($available->count() === $total ? 'open' : 'slots');

            return [
                'id' => $date,
                'date' => Carbon::parse($date)->format('F j, Y'),
                'type' => $type,
                // Days set hour by hour can no longer be set that way, so
                // one that still carries such data reads as partly open
                // instead of by the hours it used to name.
                'typeLabel' => [
                    'open' => 'Open Entire Day',
                    'closed' => 'Closed',
                    'slots' => 'Partly Open',
                ][$type],
            ];
        })->values()->all();
    }

    /**
     * The day's appointments in booking order: date => list of rows.
     *
     * The list numbers these instead of the office's hourly slots, so the
     * office reads the queue itself rather than a tally of it. Whoever
     * booked first sits at the top and takes one of the day's first slots;
     * anyone arriving past that still books - booking is never turned away -
     * but lands below the day's fill instead of in front of it. First come,
     * first served.
     *
     * Every row carries the appointment's state - approved by this office,
     * still waiting on it, or cancelled - because the office is reading who
     * is coming, not merely how many. Cancelled bookings keep their place on
     * the list so nothing quietly disappears, but they take no number: a
     * booking nobody will occupy does not count towards the day.
     *
     * @return array<string, list<array{name: string, status: string, statusLabel: string, cancelled: bool, number: int|null}>>
     */
    public static function roster(string $office): array
    {
        return Appointment::query()
            ->with('user:id,name')
            ->where('office', $office)
            ->firstComeFirstServed()
            ->get()
            ->groupBy(fn (Appointment $appointment) => $appointment->date->toDateString())
            ->map(function ($appointments) {
                $rows = [];
                $number = 0;

                foreach ($appointments as $appointment) {
                    $cancelled = $appointment->status === AppointmentStatus::CANCELLED->value;

                    $rows[] = [
                        'name' => $appointment->user?->name ?? '—',
                        'status' => $appointment->status,
                        // The button this office presses reads "Approve", so
                        // the list names the result the same way.
                        'statusLabel' => $appointment->status === AppointmentStatus::CONFIRMED->value
                            ? 'Approved'
                            : (AppointmentStatus::tryFrom($appointment->status)?->label()
                                ?? ucfirst($appointment->status)),
                        // When the office told this student to arrive, once
                        // it has answered their request.
                        'time' => $appointment->confirmed_time,
                        'cancelled' => $cancelled,
                        'number' => $cancelled ? null : ++$number,
                    ];
                }

                return $rows;
            })
            ->all();
    }

    /**
     * Derive the effective open/closed config for a date from slot checks.
     */
    public static function configForDate(string $office, string $date): array
    {
        $service = app(SlotAvailabilityService::class);
        $slots = TimeSlots::forOffice($office);

        $openSlots = collect($slots)
            ->filter(fn (string $slot) => $service->checkForOffice($office, $date, $slot)['status'] !== SlotAvailability::STATUS_BLOCKED)
            ->values();

        $row = Office::where('name', $office)->first();
        $dateRule = $row ? SlotAvailability::query()
            ->where('office_id', $row->id)
            ->whereDate('date', $date)
            ->whereNotNull('max_capacity')
            ->first() : null;

        return [
            'type' => $openSlots->isEmpty()
                ? 'closed'
                : ($openSlots->count() === count($slots) ? 'open' : 'slots'),
            'slots' => $openSlots->all(),
            'max_capacity' => $dateRule?->max_capacity ?? $row?->default_slot_capacity ?? 8,
        ];
    }

    /**
     * The month a page opens on: the one asked for, or this one.
     */
    public static function resolveMonth(?string $month): string
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            try {
                // '!' clears the day, so '2026-02' asked for on the 31st
                // cannot overflow into March and hand back the wrong month.
                return Carbon::createFromFormat('!Y-m', $month)->format('Y-m');
            } catch (\Throwable) {
                // fall through to current month
            }
        }

        return now()->format('Y-m');
    }
}
