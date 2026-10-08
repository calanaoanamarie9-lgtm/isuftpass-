<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\SlotAvailability;
use Carbon\Carbon;

/**
 * Real-time slot availability & capacity checker.
 *
 * Resolution order for a slot's capacity:
 *   1. Date-specific override rule (slot_availabilities.date = given date)
 *   2. Default rule for all dates (slot_availabilities.date IS NULL)
 *   3. Office default_slot_capacity
 *   4. Legacy Appointment::SLOT_LIMITS fallback
 */
class SlotAvailabilityService
{
    /**
     * Resolve the offices row id from the appointment-style office name.
     * Matches exact name first, then any office containing the keyword.
     */
    public function officeIdFor(string $office): ?int
    {
        return Office::query()
            ->where('name', $office)
            ->orWhere('name', 'like', '%' . $office . '%')
            ->value('id');
    }

    /**
     * Most specific rule (date override or default) for an office/date/slot.
     */
    public function ruleFor(string $office, Carbon|string $date, string $timeSlot): ?SlotAvailability
    {
        $officeId = $this->officeIdFor($office);

        if (! $officeId) {
            return null;
        }

        return SlotAvailability::query()
            ->where('office_id', $officeId)
            ->where('time_slot', $timeSlot)
            ->where(fn ($q) => $q->whereNull('date')->orWhereDate('date', $date))
            ->orderByRaw('date IS NULL')
            ->first();
    }

    /**
     * Effective capacity for an office / date / time slot.
     */
    public function limitFor(string $office, Carbon|string $date, string $timeSlot): int
    {
        if ($rule = $this->ruleFor($office, $date, $timeSlot)) {
            return max(0, $rule->max_capacity);
        }

        $officeRow = Office::find($this->officeIdFor($office));

        if ($officeRow && $officeRow->default_slot_capacity > 0) {
            return $officeRow->default_slot_capacity;
        }

        return Appointment::SLOT_LIMITS[$office] ?? 6;
    }

    /**
     * Live count of active bookings occupying a slot.
     */
    public function bookedCount(string $office, Carbon|string $date, string $timeSlot, ?int $ignoreId = null): int
    {
        return Appointment::query()
            ->where('office', $office)
            ->whereDate('date', $date)
            ->where('time_slot', $timeSlot)
            ->whereIn('status', AppointmentStatus::activeValues())
            ->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->count();
    }

    /**
     * Persist the live booked count and effective status onto the rule row.
     */
    public function refreshSlotStatus(string $office, Carbon|string $date, string $timeSlot, ?int $ignoreId = null): void
    {
        $rule = $this->ruleFor($office, $date, $timeSlot);

        if (! $rule) {
            return;
        }

        $booked = $this->bookedCount($office, $date, $timeSlot, $ignoreId);

        $status = $rule->status === SlotAvailability::STATUS_BLOCKED
            ? SlotAvailability::STATUS_BLOCKED
            : ($booked >= $rule->max_capacity
                ? SlotAvailability::STATUS_FULLY_BOOKED
                : SlotAvailability::STATUS_AVAILABLE);

        if ($rule->booked_slots !== $booked || $rule->status !== $status) {
            $rule->forceFill(['booked_slots' => $booked, 'status' => $status])->save();
        }
    }

    /**
     * Core availability check (by offices row id, as requested).
     *
     * @return array{available: bool, remaining: int, limit: int, booked: int, status: string}
     */
    public function checkSlotAvailability(int $officeId, Carbon|string $date, string $timeSlot, ?int $ignoreId = null): array
    {
        $office = Office::find($officeId)?->name ?? 'Office';

        return $this->checkForOffice($office, $date, $timeSlot, $ignoreId);
    }

    /**
     * Core availability check (by appointment-style office name).
     */
    public function checkForOffice(string $office, Carbon|string $date, string $timeSlot, ?int $ignoreId = null): array
    {
        $rule = $this->ruleFor($office, $date, $timeSlot);
        $limit = $this->limitFor($office, $date, $timeSlot);
        $booked = $this->bookedCount($office, $date, $timeSlot, $ignoreId);

        if ($rule) {
            $this->refreshSlotStatus($office, $date, $timeSlot, $ignoreId);
            $rule->refresh();
        }

        $status = $rule && $rule->status === SlotAvailability::STATUS_BLOCKED
            ? SlotAvailability::STATUS_BLOCKED
            : ($booked >= $limit
                ? SlotAvailability::STATUS_FULLY_BOOKED
                : SlotAvailability::STATUS_AVAILABLE);

        return [
            'available' => $status === SlotAvailability::STATUS_AVAILABLE && $booked < $limit,
            'remaining' => $status === SlotAvailability::STATUS_BLOCKED
                ? 0
                : max(0, $limit - $booked),
            'limit' => $limit,
            'booked' => $booked,
            'status' => $status,
        ];
    }

    /**
     * Full day availability view across all standard time slots.
     */
    public function availableSlots(string $office, Carbon|string $date, ?int $ignoreId = null): array
    {
        return collect(TimeSlots::forOffice($office))->map(function (string $slot) use ($office, $date, $ignoreId) {
            $check = $this->checkForOffice($office, $date, $slot, $ignoreId);

            return [
                'time' => $slot,
                'remaining' => $check['remaining'],
                'limit' => $check['limit'],
                'booked' => $check['booked'],
                'status' => $check['status'],
                'is_open' => $check['available'],
            ];
        })->all();
    }

    /**
     * Ensure a booking is allowed, throwing an HTTP 422 if not.
     */
    public function assertBookable(string $office, Carbon|string $date, string $timeSlot, ?int $ignoreId = null): void
    {
        $result = $this->checkForOffice($office, $date, $timeSlot, $ignoreId);

        if (! $result['available']) {
            abort(422, 'The selected time slot is unavailable. Choose another schedule.');
        }
    }

    /**
     * Per-day availability overview for a whole month, used to color
     * calendar cells (green = at least one open slot, red = fully booked/blocked).
     *
     * @return array<string, array{past: bool, available: bool, open: int, status: string}>
     */
    public function monthOverview(string $office, string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $today = Carbon::today();

        $officeRow = Office::find($this->officeIdFor($office));
        $defaultLimit = $officeRow && $officeRow->default_slot_capacity > 0
            ? (int) $officeRow->default_slot_capacity
            : (Appointment::SLOT_LIMITS[$office] ?? 6);

        $rules = $officeRow
            ? SlotAvailability::query()
                ->where('office_id', $officeRow->id)
                ->get()
                ->groupBy(fn (SlotAvailability $rule) => $rule->date ?: '__default__')
            : collect();

        $booked = Appointment::query()
            ->where('office', $office)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->whereIn('status', AppointmentStatus::activeValues())
            ->get(['date', 'time_slot'])
            ->countBy(fn (Appointment $booking) => $booking->date->format('Y-m-d') . '|' . $booking->time_slot);

        $overview = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $dateStr = $day->toDateString();

            if ($day->lt($today)) {
                $overview[$dateStr] = ['past' => true, 'available' => false, 'open' => 0, 'status' => 'past'];
                continue;
            }

            $open = 0;
            $blocked = false;

            foreach (TimeSlots::forOffice($office) as $slot) {
                $rule = ($rules->get($dateStr) ?? collect())->firstWhere('time_slot', $slot)
                    ?? ($rules->get('__default__') ?? collect())->firstWhere('time_slot', $slot);

                if ($rule && $rule->status === SlotAvailability::STATUS_BLOCKED) {
                    $blocked = true;
                    continue;
                }

                $limit = $rule ? max(0, (int) $rule->max_capacity) : $defaultLimit;
                $bookedCount = $booked[$dateStr . '|' . $slot] ?? 0;

                if ($bookedCount < $limit) {
                    $open++;
                }
            }

            $overview[$dateStr] = [
                'past' => false,
                'available' => $open > 0,
                'open' => $open,
                'status' => $blocked && $open === 0 ? 'blocked' : ($open === 0 ? 'full' : 'open'),
            ];
        }

        return $overview;
    }
}