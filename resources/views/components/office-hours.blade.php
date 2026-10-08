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
                    <p class="text-sm font-extrabold text-blue-950">Office Hours</p>
                    <span class="px-2 py-0.5 rounded-full bg-blue-50 text-[10px] font-extrabold text-blue-700 uppercase">
                        {{ $hoursOffice }}
                    </span>
                </div>
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

                <button type="button"
                        @click="save"
                        :disabled="saving"
                        class="h-9 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold shadow-sm transition disabled:opacity-60">
                    <span x-show="!saving">Save Office Hours</span>
                    <span x-show="saving">Saving...</span>
                </button>

            </div>
        </div>

        <p class="mt-3 text-right text-[11px] font-semibold text-red-500"
           x-show="error" x-text="error"></p>
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
