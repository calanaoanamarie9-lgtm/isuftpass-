<?php

namespace App\Http\Controllers\Registrar;

use App\Enums\AppointmentStatus;
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
            'office' => ['nullable', 'in:'.implode(',', \App\Enums\Office::toSelectKeys())],
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
            'month' => $month,
            'schedule' => $this->buildSchedule($month),
            'rosterByDate' => $this->rosterByDate(),
        ]);
    }

    /**
     * The day's appointments in booking order: date => list of rows.
     *
     * The list numbers these instead of the office's hourly slots, so the
     * registrar reads the queue itself rather than a tally of it. Whoever
     * booked first sits at the top and takes one of the day's first slots;
     * anyone arriving past that still books — booking is never turned away —
     * but lands below the day's fill instead of in front of it. First come,
     * first served.
     *
     * Every row carries the appointment's state — approved by this office,
     * still waiting on it, or cancelled — because the registrar is reading
     * who is coming, not merely how many. Cancelled bookings keep their
     * place on the list so nothing quietly disappears, but they take no
     * number: a booking nobody will occupy does not count towards the day.
     *
     * @return array<string, list<array{name: string, status: string, statusLabel: string, cancelled: bool, number: int|null}>>
     */
    private function rosterByDate(): array
    {
        return Appointment::query()
            ->with('user:id,name')
            ->where('office', $this->officeKey())
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
            'slots.*' => ['string', 'in:'.implode(',', $slots)],
            'max_capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $openSlots = match ($data['type']) {
            'open' => $slots,
            'closed' => [],
            default => array_values(array_intersect($slots, $data['slots'] ?? [])),
        };

        $capacity = ! empty($data['max_capacity']) ? (int) $data['max_capacity'] : null;

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
            'message' => $office->name.' availability has been updated.',
            'month' => $savedMonth,
            'schedule' => $this->buildSchedule($savedMonth),
        ]);
    }

    /**
     * Persist the office's opening and closing time, default capacity, and
     * the slot times it keeps.
     *
     * Two ways to say when the office serves: post the hours alone and the
     * day rebuilds itself one slot to the hour from them, or post
     * slot_definitions — the office's own {start, end} rows, as the
     * Availability page's card does — and those times become the slot list
     * every date, booking form, and validation rule reads: 8:00 - 9:00
     * retyped as 8:30 - 9:30 applies everywhere at once. Days already set
     * are moved onto the new list so their open and closed choices stay
     * set; appointments keep the times they were booked at.
     */
    public function saveHours(Request $request): JsonResponse
    {
        $data = $request->validate([
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'after:open_time'],
            'default_slot_capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'slot_definitions' => ['nullable', 'array', 'max:120', $this->slotRowsRule()],
        ]);

        $office = Office::where('name', $this->officeKey())->firstOrFail();

        $previous = TimeSlots::forOffice($office->name);

        $office->open_time = $data['open_time'];
        $office->close_time = $data['close_time'];
        $office->default_slot_capacity = $data['default_slot_capacity'] ?? $office->default_slot_capacity;
        // Rows sent win; none sent (or none left) means back to the hours.
        $office->slot_definitions = $this->normalizeSlotRows($data['slot_definitions'] ?? null);
        $office->save();

        $slots = TimeSlots::forOffice($office->name);

        $this->rekeyDayRules($office, $previous, $slots);

        AuditLogger::log(
            'office.hours',
            'Set office hours to '.$data['open_time'].' - '.$data['close_time']
                .(isset($data['default_slot_capacity']) ? ' (capacity: '.$data['default_slot_capacity'].')' : '')
                .($office->slot_definitions ? ' Slot times: '.count($slots).' a day.' : '')
                .' for '.$office->name.'.'
        );

        return response()->json([
            'message' => $office->name.' is now open '.$data['open_time'].' - '.$data['close_time'].' ('.count($slots).' slots/day, '.($office->default_slot_capacity).' capacity/slot).',
            'count' => count($slots),
            'slots' => $slots,
            'default_slot_capacity' => $office->default_slot_capacity,
        ]);
    }

    /**
     * The slot rows read as a whole list: a clock time at each end, an end
     * after its own start, and no row twice. The pair-wise checks only make
     * sense with a row in hand, so they are made once here — a duplicated
     * pair would name one slot twice, which no day can hold.
     */
    private function slotRowsRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $seen = [];

            foreach ((array) $value as $index => $row) {
                $number = $index + 1;
                $start = is_array($row) ? ($row['start'] ?? null) : null;
                $end = is_array($row) ? ($row['end'] ?? null) : null;

                if (! is_string($start) || ! is_string($end)
                    || ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $start)
                    || ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $end)) {
                    $fail('Slot '.$number.' needs a start and an end time on the clock, like 08:30.');

                    continue;
                }

                // Zero-padded H:i compares as cleanly as any string: the
                // later clock time is the later one.
                if ($end <= $start) {
                    $fail('Slot '.$number.' must end after it starts.');

                    continue;
                }

                if (isset($seen[$start.$end])) {
                    $fail('Slot '.$number.' repeats an earlier slot.');

                    continue;
                }

                $seen[$start.$end] = true;
            }
        };
    }

    /**
     * The submitted rows as they are stored, or null when none were sent
     * (or none are left) — such an office has its day read hour by hour
     * from its hours instead.
     *
     * @return array<int, array{start: string, end: string}>|null
     */
    private function normalizeSlotRows(mixed $definitions): ?array
    {
        $rows = collect(is_array($definitions) ? $definitions : [])
            ->filter(fn ($row) => is_array($row) && isset($row['start'], $row['end']))
            ->map(fn ($row) => ['start' => (string) $row['start'], 'end' => (string) $row['end']])
            ->values()
            ->all();

        return $rows === [] ? null : $rows;
    }

    /**
     * Move every day already set onto a changed slot list.
     *
     * A day set slot by slot carries one row per label of the list as it
     * was. Retiming a slot swaps its label, so without this the day's
     * choice for that hour would fall out of the list — a closed day would
     * quietly reopen, an open day would read as partly open. Two passes put
     * it right. A vanished label walks onto whatever now sits at its old
     * position, which is the everyday case of one row's time being edited.
     * A label with no row yet — a slot the office added — takes the day's
     * standing: open only on a day with nothing blocked, blocked whenever
     * the day blocks anything, so a new time can never silently open a day
     * the office had closed. Default (all-dates) rules follow the first
     * pass only: a new slot there simply falls back to the office's own
     * default capacity, while a default rule is a limit, not a day's state.
     */
    private function rekeyDayRules(Office $office, array $previous, array $slots): void
    {
        if ($previous === $slots) {
            return;
        }

        $days = SlotAvailability::query()
            ->where('office_id', $office->id)
            ->get()
            ->groupBy(fn (SlotAvailability $rule) => $rule->date ?: '__default__');

        foreach ($days as $key => $rules) {
            $held = $rules->pluck('time_slot')->all();

            foreach ($previous as $index => $gone) {
                if (in_array($gone, $slots, true)) {
                    continue;
                }

                $target = $slots[$index] ?? null;

                // A row already sits where this one would land — leave it
                // be rather than collide on the day's unique rows.
                if ($target === null || in_array($target, $held, true)) {
                    continue;
                }

                SlotAvailability::query()
                    ->where('office_id', $office->id)
                    ->where('time_slot', $gone)
                    ->when($key === '__default__',
                        fn ($query) => $query->whereNull('date'),
                        fn ($query) => $query->whereDate('date', $key))
                    ->update(['time_slot' => $target]);

                $held[] = $target;
            }

            if ($key === '__default__') {
                continue;
            }

            $blocked = $rules->contains(
                fn (SlotAvailability $rule) => $rule->status === SlotAvailability::STATUS_BLOCKED
            );
            $capacity = $rules->first()?->max_capacity ?: $office->default_slot_capacity;

            foreach ($slots as $slot) {
                if (in_array($slot, $held, true)) {
                    continue;
                }

                SlotAvailability::updateOrCreate(
                    [
                        'office_id' => $office->id,
                        'date' => $key,
                        'time_slot' => $slot,
                    ],
                    [
                        'max_capacity' => $capacity,
                        'status' => $blocked
                            ? SlotAvailability::STATUS_BLOCKED
                            : SlotAvailability::STATUS_AVAILABLE,
                    ]
                );
            }
        }
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
