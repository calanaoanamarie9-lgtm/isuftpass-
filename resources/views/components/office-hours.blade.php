@props(['office' => null])

{{--
    Editing card for one office's opening hours and slot capacity.

    The whole system builds its slot list from these values:
    - Every hour between opening and closing becomes one bookable time slot.
    - An office can customize its hours and max capacity per slot, allowing them
      to handle as many or as few slots in a day as they can accommodate (not
      restricted to a fixed 8 slots).
--}}
@php
    $hoursOffice = $office ?: (auth()->user()?->officeScope() ?? 'Registrar');
    $hoursRow = \App\Models\Office::query()->where('name', $hoursOffice)->first()
        ?? \App\Models\Office::query()->where('name', 'like', '%' . $hoursOffice . '%')->first();
    $hoursOpen = $hoursRow?->open_time ?: \App\Support\TimeSlots::DEFAULT_OPEN;
    $hoursClose = $hoursRow?->close_time ?: \App\Support\TimeSlots::DEFAULT_CLOSE;
    $hoursCapacity = (int) ($hoursRow?->default_slot_capacity ?: 8);
    $hoursRoute = strtolower($hoursOffice) . '.availability.hours';
    $hoursSlots = \App\Support\TimeSlots::forOffice($hoursOffice);
@endphp

@if (\Illuminate\Support\Facades\Route::has($hoursRoute))
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6"
         x-data="officeHoursForm('{{ $hoursOpen }}', '{{ $hoursClose }}', {{ $hoursCapacity }}, '{{ route($hoursRoute) }}')">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-sm font-extrabold text-blue-950">Office Hours & Daily Slots</p>
                    <span class="px-2 py-0.5 rounded-full bg-blue-50 text-[10px] font-extrabold text-blue-700 uppercase">
                        {{ $hoursOffice }}
                    </span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1 max-w-xl leading-relaxed">
                    Configure how many slots and what times can be accommodated in a day.
                    This is not limited to 8 slots only — the opening/closing hours and the number of students per slot can be modified.
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <label class="block">
                    <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Opens</span>
                    <input type="time"
                           step="60"
                           name="open_time"
                           value="{{ $hoursOpen }}"
                           x-model="open"
                           class="mt-1 block rounded-xl border-gray-200 text-sm font-semibold text-gray-700 focus:border-blue-400 focus:ring-blue-400">
                </label>

                <label class="block">
                    <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Closes</span>
                    <input type="time"
                           step="60"
                           name="close_time"
                           value="{{ $hoursClose }}"
                           x-model="close"
                           class="mt-1 block rounded-xl border-gray-200 text-sm font-semibold text-gray-700 focus:border-blue-400 focus:ring-blue-400">
                </label>

            </div>
        </div>

        {{-- QUICK PRESETS --}}
        <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-semibold text-gray-400 mr-1">Quick Slots:</span>
                <button type="button" @click="setPreset('08:00', '16:00')"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition"
                        :class="open === '08:00' && close === '16:00' ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 border-gray-200'">
                    8 slots (8 AM - 4 PM)
                </button>
                <button type="button" @click="setPreset('08:00', '17:00')"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition"
                        :class="open === '08:00' && close === '17:00' ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 border-gray-200'">
                    9 slots (8 AM - 5 PM)
                </button>
                <button type="button" @click="setPreset('08:00', '18:00')"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition"
                        :class="open === '08:00' && close === '18:00' ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 border-gray-200'">
                    10 slots (8 AM - 6 PM)
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-100 text-blue-700 text-xs font-extrabold"
                      x-text="slotList.length + ' time slots per day'">{{ count($hoursSlots) }} slots per day</span>

                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-extrabold"
                      x-text="(slotList.length * (capacity || 1)) + ' total appointments/day'">{{ count($hoursSlots) * $hoursCapacity }} total appointments/day</span>

                <button type="button"
                        @click="save"
                        :disabled="saving"
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold shadow-sm transition disabled:opacity-60 ml-2">
                    <span x-show="!saving">Save Schedule & Capacity</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>

        <div class="mt-2 flex items-center justify-between">
            <span class="text-[11px] text-gray-400"
                  x-text="firstSlot">From {{ $hoursSlots[0] ?? '—' }} to {{ $hoursSlots[count($hoursSlots) - 1] ?? '—' }}</span>
            <span class="text-[11px] font-semibold text-red-500"
                  x-show="error" x-text="error"></span>
        </div>
    </div>
@endif

<script>
    function officeHoursForm(open, close, capacity, url) {
        return {
            open,
            close,
            capacity: capacity || 8,
            url,
            saving: false,
            error: '',

            setPreset(start, end) {
                this.open = start;
                this.close = end;
            },

            label(minutes) {
                const hour24 = Math.floor(minutes / 60) % 24;
                const suffix = hour24 < 12 ? 'AM' : 'PM';
                const hour12 = (hour24 % 12) || 12;

                return String(hour12).padStart(2, '0') + ':'
                    + String(minutes % 60).padStart(2, '0') + ' '
                    + suffix;
            },

            get slotList() {
                const [openHour, openMinute] = this.open.split(':').map(Number);
                const [closeHour, closeMinute] = this.close.split(':').map(Number);
                const start = openHour * 60 + openMinute;
                const end = closeHour * 60 + closeMinute;
                const slots = [];

                if (isNaN(start) || isNaN(end) || end - start < 60) {
                    return slots;
                }

                for (let from = start; from + 60 <= end; from += 60) {
                    slots.push(this.label(from) + ' - ' + this.label(from + 60));
                }

                return slots;
            },

            get firstSlot() {
                return this.slotList.length
                    ? 'Schedule: ' + this.slotList[0].split(' - ')[0] + ' to ' + this.slotList[this.slotList.length - 1].split(' - ')[1] + ' (' + this.slotList.length + ' hourly slots)'
                    : 'Closing time must be at least an hour after opening time.';
            },

            async save() {
                this.saving = true;
                this.error = '';

                try {
                    const response = await fetch(this.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            open_time: this.open,
                            close_time: this.close,
                            default_slot_capacity: this.capacity,
                        }),
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        this.saving = false;
                        this.error = Object.values(payload.errors || {})[0]
                            || payload.message
                            || 'Could not save the office hours.';

                        return;
                    }

                    window.location.reload();
                } catch (error) {
                    this.saving = false;
                    this.error = 'Could not save the office hours.';
                }
            },
        };
    }
</script>
