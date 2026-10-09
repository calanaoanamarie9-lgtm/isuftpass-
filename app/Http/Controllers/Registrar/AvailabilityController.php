<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\SlotAvailability;
use App\Support\AuditLogger;
use App\Support\SlotAvailabilityService;
use App\Support\TimeSlots;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    /**
     * The office managed by the logged-in staff account.
     */
    private function officeKey(): string
    {
        return auth()->user()?->officeScope() ?? 'Registrar';
    }

    /**
     * JSON endpoint: per-day availability map for a calendar.
     * Defaults to the Registrar office; reschedule pages may pass another office.
     */
    public function month(Request $request, SlotAvailabilityService $service)
    {
        $data = $request->validate([
            'office' => ['nullable', 'in:' . implode(',', \App\Enums\Office::toSelectKeys())],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        return response()->json($service->monthOverview($data['office'] ?? $this->officeKey(), $data['month']));
    }

    /**
     * Registrar Availability page — configure open days/slots and review the schedule.
     */
    public function index(Request $request): View
    {
        // The page is a calendar, so it opens on a month: that month's set
        // dates come with it, and ?month= lets a page be pointed at another.
        $month = $this->resolveMonth($request->input('month'));

        return view('registrar.availability', [
            'office' => Office::where('name', $this->officeKey())->first(),
            'timeSlots' => TimeSlots::forOffice($this->officeKey()),
            'slotsPerDay' => Appointment::SLOTS_PER_DAY,
            'month' => $month,
            'schedule' => $this->buildSchedule($month),
            'rosterByDate' => $this->rosterByDate(),
        ]);
    }

    /**
     * The day's appointments in booking order: date => list of names.
     *
     * The list numbers these instead of the office's hourly slots, so the
     * registrar reads the queue itself rather than a tally of it. Whoever
     * booked first sits at the top and takes one of the day's first slots;
     * anyone arriving past that still books — booking is never turned away —
     * but lands below the day's fill instead of in front of it. First come,
     * first served.
     *
     * Cancelled bookings are dropped: a slot that nobody will occupy should
     * not count towards the day.
     *
     * @return array<string, list<string>>
     */
    private function rosterByDate(): array
    {
        return Appointment::query()
            ->with('user:id,name')
            ->where('office', $this->officeKey())
            ->whereNotIn('status', ['cancelled'])
            ->firstComeFirstServed()
            ->get()
            ->groupBy(fn (Appointment $appointment) => $appointment->date->toDateString())
            ->map(fn ($appointments) => $appointments
                ->map(fn (Appointment $appointment) => $appointment->user?->name ?? '—')
                ->all()
            )
            ->all();
    }

    /**
     * JSON: upcoming configured dates for the schedule panel.
     */
    /**
     * A month's set dates, for paging the calendar without a full reload.
     */
    public function schedule(Request $request): JsonResponse
    {
        return response()->json([
            'schedule' => $this->buildSchedule($this->resolveMonth($request->input('month'))),
        ]);
    }

    /**
     * JSON: saved availability configuration for one date (form prefill).
     */
    public function settings(string $date): JsonResponse
    {
        $validated = request()->merge(['date' => $date])->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        return response()->json($this->configForDate($validated['date']));
    }

    /**
     * Persist an entire-day or per-slot availability configuration.
     */
    public function save(Request $request): JsonResponse
    {
        $office = Office::where('name', $this->officeKey())->firstOrFail();
        $slots = TimeSlots::forOffice($office->name);

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'type' => ['required', 'in:open,closed,slots'],
            'slots' => ['nullable', 'array'],
            'slots.*' => ['string', 'in:' . implode(',', $slots)],
            'max_capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $openSlots = match ($data['type']) {
            'open' => $slots,
            'closed' => [],
            default => array_values(array_intersect($slots, $data['slots'] ?? [])),
        };

        $capacity = !empty($data['max_capacity']) ? (int) $data['max_capacity'] : null;

        foreach ($slots as $slot) {
            $existing = SlotAvailability::query()
                ->where('office_id', $office->id)
                ->whereDate('date', $data['date'])
                ->where('time_slot', $slot)
                ->first();

            SlotAvailability::updateOrCreate(
                [
                    'office_id' => $office->id,
                    'date' => $data['date'],
                    'time_slot' => $slot,
                ],
                [
                    'max_capacity' => $capacity ?: ($existing?->max_capacity ?: $office->default_slot_capacity),
                    'status' => in_array($slot, $openSlots, true)
                        ? SlotAvailability::STATUS_AVAILABLE
                        : SlotAvailability::STATUS_BLOCKED,
                ]
            );
        }

        // Rows left over from an earlier, longer set of office hours would
        // otherwise count towards this date's totals, so drop them.
        SlotAvailability::query()
            ->where('office_id', $office->id)
            ->whereDate('date', $data['date'])
            ->whereNotIn('time_slot', $slots)
            ->delete();

        AuditLogger::log(
            'availability.updated',
            match ($data['type']) {
                'open' => 'Opened the entire day for appointments on '.$data['date'].' ('.$office->name.').',
                'closed' => 'Closed the entire day for appointments on '.$data['date'].' ('.$office->name.').',
                default => 'Set '.count($openSlots).' specific slot(s) open on '.$data['date'].' ('.$office->name.').',
            }
        );

        // The calendar reads one month at a time, so send back the month
        // that was written — it is the one the grid should be showing.
        $savedMonth = Carbon::parse($data['date'])->format('Y-m');

        return response()->json([
            'message' => $office->name . ' availability has been updated.',
            'month' => $savedMonth,
            'schedule' => $this->buildSchedule($savedMonth),
        ]);
    }

    /**
     * Persist the office's opening and closing time and default capacity.
     *
     * The slot list of the whole system is derived from these two times, so
     * saving them here is how an office changes how many slots it handles in
     * a day — every Availability page owns this form for its own office.
     */
    public function saveHours(Request $request): JsonResponse
    {
        $data = $request->validate([
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'after:open_time'],
            'default_slot_capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $office = Office::where('name', $this->officeKey())->firstOrFail();

        $office->update(array_filter($data, fn ($val) => $val !== null));

        AuditLogger::log(
            'office.hours',
            'Set office hours to '.$data['open_time'].' - '.$data['close_time']
                . (isset($data['default_slot_capacity']) ? ' (capacity: '.$data['default_slot_capacity'].')' : '')
                . ' for '.$office->name.'.'
        );

        $slots = TimeSlots::forOffice($office->name);

        return response()->json([
            'message' => $office->name.' is now open '.$data['open_time'].' - '.$data['close_time'].' ('.count($slots).' slots/day, '.($office->default_slot_capacity).' capacity/slot).',
            'count' => count($slots),
            'slots' => $slots,
            'default_slot_capacity' => $office->default_slot_capacity,
        ]);
    }

    /**
     * Derive the effective open/closed config for a date from slot checks.
     */
    private function configForDate(string $date): array
    {
        $service = app(SlotAvailabilityService::class);
        $slots = TimeSlots::forOffice($this->officeKey());

        $openSlots = collect($slots)
            ->filter(fn (string $slot) => $service->checkForOffice($this->officeKey(), $date, $slot)['status'] !== SlotAvailability::STATUS_BLOCKED)
            ->values();

        $office = Office::where('name', $this->officeKey())->first();
        $dateRule = $office ? SlotAvailability::query()
            ->where('office_id', $office->id)
            ->whereDate('date', $date)
            ->whereNotNull('max_capacity')
            ->first() : null;

        return [
            'type' => $openSlots->isEmpty()
                ? 'closed'
                : ($openSlots->count() === count($slots) ? 'open' : 'slots'),
            'slots' => $openSlots->all(),
            'max_capacity' => $dateRule?->max_capacity ?? $office?->default_slot_capacity ?? 8,
        ];
    }

    /**
     * The dates in a month that carry an override, each with what was set.
     *
     * One entry per date — only the badge naming how that day was set.
     * The clock times behind it are deliberately left out: the roster
     * numbers a day's bookings first come, first served, so a cell has no
     * hour of its own to print. The month is bounded rather than running
     * from today onwards, so a date earlier in the month still reads
     * truthfully when the grid is turned back to it.
     *
     * @return array<int, array{id: string, date: string, type: string, typeLabel: string}>
     */
    private function buildSchedule(string $month): array
    {
        $office = Office::where('name', $this->officeKey())->first();
        $slots = TimeSlots::forOffice($this->officeKey());
        $total = count($slots);

        // The '!' keeps the month pinned to its first day, so a month name
        // typed on the 31st cannot overflow into the next one.
        $monthStart = Carbon::createFromFormat('!Y-m', $month);
        $monthEnd = $monthStart->copy()->endOfMonth();

        $groups = SlotAvailability::query()
            ->when($office, fn ($query) => $query->where('office_id', $office->id))
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

    private function resolveMonth(?string $month): string
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
