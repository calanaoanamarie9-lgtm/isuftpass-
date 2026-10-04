<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\SlotAvailability;
use App\Support\AuditLogger;
use App\Support\SlotAvailabilityService;
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
    public function index(): View
    {
        return view('registrar.availability', [
            'office' => Office::where('name', $this->officeKey())->first(),
            'timeSlots' => Appointment::TIME_SLOTS,
            'schedule' => $this->buildSchedule(),
        ]);
    }

    /**
     * JSON: upcoming configured dates for the schedule panel.
     */
    public function schedule(): JsonResponse
    {
        return response()->json(['schedule' => $this->buildSchedule()]);
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
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'type' => ['required', 'in:open,closed,slots'],
            'slots' => ['nullable', 'array'],
            'slots.*' => ['string', 'in:' . implode(',', Appointment::TIME_SLOTS)],
        ]);

        $office = Office::where('name', $this->officeKey())->firstOrFail();

        $openSlots = match ($data['type']) {
            'open' => Appointment::TIME_SLOTS,
            'closed' => [],
            default => array_values(array_intersect(Appointment::TIME_SLOTS, $data['slots'] ?? [])),
        };

        foreach (Appointment::TIME_SLOTS as $slot) {
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
                    'max_capacity' => $existing?->max_capacity ?: $office->default_slot_capacity,
                    'status' => in_array($slot, $openSlots, true)
                        ? SlotAvailability::STATUS_AVAILABLE
                        : SlotAvailability::STATUS_BLOCKED,
                ]
            );
        }

        AuditLogger::log(
            'availability.updated',
            match ($data['type']) {
                'open' => 'Opened the entire day for appointments on '.$data['date'].' ('.$office->name.').',
                'closed' => 'Closed the entire day for appointments on '.$data['date'].' ('.$office->name.').',
                default => 'Set '.count($openSlots).' specific slot(s) open on '.$data['date'].' ('.$office->name.').',
            }
        );

        return response()->json([
            'message' => $office->name . ' availability has been updated.',
            'schedule' => $this->buildSchedule(),
        ]);
    }

    /**
     * Derive the effective open/closed config for a date from slot checks.
     */
    private function configForDate(string $date): array
    {
        $service = app(SlotAvailabilityService::class);

        $openSlots = collect(Appointment::TIME_SLOTS)
            ->filter(fn (string $slot) => $service->checkForOffice($this->officeKey(), $date, $slot)['status'] !== SlotAvailability::STATUS_BLOCKED)
            ->values();

        return [
            'type' => $openSlots->isEmpty()
                ? 'closed'
                : ($openSlots->count() === count(Appointment::TIME_SLOTS) ? 'open' : 'slots'),
            'slots' => $openSlots->all(),
        ];
    }

    /**
     * Upcoming date-specific overrides grouped for the schedule panel.
     */
    private function buildSchedule(): array
    {
        $total = count(Appointment::TIME_SLOTS);

        $groups = SlotAvailability::query()
            ->whereNotNull('date')
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->get()
            ->groupBy(fn (SlotAvailability $rule) => Carbon::parse($rule->date)->toDateString());

        return $groups->map(function ($group, string $date) use ($total) {
            $available = $group->filter(
                fn (SlotAvailability $rule) => $rule->status === SlotAvailability::STATUS_AVAILABLE
            );

            $type = $available->isEmpty()
                ? 'closed'
                : ($available->count() === $total ? 'open' : 'slots');

            return [
                'id' => $date,
                'date' => Carbon::parse($date)->format('F j, Y'),
                'status' => $type === 'closed'
                    ? 'All time slots are blocked.'
                    : $available->count().' of '.$total.' time slots open.',
                'type' => $type,
                'typeLabel' => [
                    'open' => 'Open Entire Day',
                    'closed' => 'Closed',
                    'slots' => 'Specific Time Slots',
                ][$type],
                'slots' => $available
                    ->sortBy('time_slot')
                    ->map(fn (SlotAvailability $rule) => ['start' => $rule->time_slot])
                    ->values()
                    ->all(),
            ];
        })->values()->all();
    }

    private function resolveMonth(?string $month): string
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            try {
                return Carbon::createFromFormat('Y-m', $month)->format('Y-m');
            } catch (\Throwable) {
                // fall through to current month
            }
        }

        return now()->format('Y-m');
    }
}
