<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\SlotAvailability;
use App\Support\AuditLogger;
use App\Support\SlotAvailabilityService;
use App\Support\TimeSlots;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SlotCapacityController extends Controller
{
    public function index(Request $request, SlotAvailabilityService $service): View
    {
        $offices = Office::query()->orderBy('name')->get();
        $office = Office::find($request->integer('office_id')) ?? $offices->first();
        $date = $request->query('date') ?: now()->toDateString();
        $date = \Illuminate\Support\Carbon::parse($date)->toDateString();

        $rows = [];
        $withOffice = false;

        if ($office) {
            $withOffice = true;

            foreach (TimeSlots::forOffice($office->name) as $slot) {
                $check = $service->checkForOffice($office->name, $date, $slot);

                $rows[] = [
                    'time_slot' => $slot,
                    'check' => $check,
                    'rule' => $service->ruleFor($office->name, $date, $slot),
                    'default_rule' => SlotAvailability::query()
                        ->where('office_id', $office->id)
                        ->whereNull('date')
                        ->where('time_slot', $slot)
                        ->first(),
                ];
            }
        }

        return view('admin.slots.index', [
            'offices' => $offices,
            'office' => $office,
            'date' => $date,
            'rows' => $rows,
            'withOffice' => $withOffice,
            'timeSlots' => TimeSlots::forOffice($office?->name),
        ]);
    }

    /**
     * Upsert a per-date (or all-dates default) capacity rule for one slot.
     */
    public function store(Request $request, SlotAvailabilityService $service): RedirectResponse
    {
        // The office decides which slots exist, so it has to be resolved
        // before a time slot can be checked against anything.
        $office = Office::findOrFail($request->validate([
            'office_id' => ['required', 'exists:offices,id'],
        ])['office_id']);

        $data = $request->validate([
            'date' => ['nullable', 'date'],
            'time_slot' => ['required', Rule::in(TimeSlots::forOffice($office->name))],
            'max_capacity' => ['required', 'integer', 'min:0', 'max:999'],
            'status' => ['required', Rule::in([
                SlotAvailability::STATUS_AVAILABLE,
                SlotAvailability::STATUS_BLOCKED,
            ])],
        ]);

        $date = $request->boolean('apply_to_all_dates') ? null : ($data['date'] ?: null);

        $rule = SlotAvailability::updateOrCreate(
            ['office_id' => $office->id, 'date' => $date, 'time_slot' => $data['time_slot']],
            [
                'max_capacity' => $data['max_capacity'],
                'status' => $data['status'],
            ]
        );

        $service->refreshSlotStatus($office->name, $date ?? now()->toDateString(), $data['time_slot']);

        AuditLogger::log(
            'slot_capacity.saved',
            'Set ' . $data['time_slot'] . ' capacity to ' . $data['max_capacity']
                . ' (' . $data['status'] . ') for ' . $office->name
                . ($date ? ' on ' . $date : ' (all dates)') . '.'
        );

        return back()->with('status', 'Slot rule saved for ' . $office->name . '.');
    }

    /**
     * Remove a date-specific override so the default rule applies again.
     */
    public function destroy(Request $request, SlotAvailability $slotAvailability): RedirectResponse
    {
        $office = $slotAvailability->office;
        $slot = $slotAvailability->time_slot;

        $slotAvailability->delete();

        AuditLogger::log('slot_capacity.deleted', 'Removed slot override for ' . $slot . ' (' . $office->name . ').');

        return back()->with('status', 'Slot override removed — using the default rule again.');
    }

    /**
     * Set the office-wide default capacity for all slots.
     */
    public function updateCapacity(Request $request, Office $office): RedirectResponse
    {
        $data = $request->validate([
            'default_slot_capacity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $office->update($data);

        AuditLogger::log('slot_capacity.default', 'Set default slot capacity to ' . $data['default_slot_capacity'] . ' for ' . $office->name . '.');

        return back()->with('status', 'Default capacity updated for ' . $office->name . '.');
    }
}