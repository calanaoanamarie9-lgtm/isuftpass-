@props(['office' => null])

{{--
    Editing card for one office's hours.

    An office's opening and closing time still bounds what it may promise:
    the approve popover only offers times inside the office's own day, and an
    approval cannot name an hour the office is shut. This card edits those
    two bounds. (It once also edited the office's bookable *slots*; slots are
    gone — a booking names a day and the office answers with the window it
    wants the student in — so only the hours remain.)
--}}
@php
    $hoursOffice = $office ?: (auth()->user()?->officeScope() ?? 'Registrar');
    $hoursRow = \App\Models\Office::query()->where('name', $hoursOffice)->first()
        ?? \App\Models\Office::query()->where('name', 'like', '%' . $hoursOffice . '%')->first();
    $hoursOpen = $hoursRow?->open_time ?: \App\Support\TimeSlots::DEFAULT_OPEN;
    $hoursClose = $hoursRow?->close_time ?: \App\Support\TimeSlots::DEFAULT_CLOSE;
    $hoursRoute = strtolower($hoursOffice) . '.availability.hours';
@endphp

@if (\Illuminate\Support\Facades\Route::has($hoursRoute))
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6"
         x-data='officeHoursForm("{{ $hoursOpen }}", "{{ $hoursClose }}", "{{ route($hoursRoute) }}")'>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-sm font-extrabold text-blue-950">Office Hours</p>
                    <span class="px-2 py-0.5 rounded-full bg-blue-50 text-[10px] font-extrabold text-blue-700 uppercase">
                        {{ $hoursOffice }}
                    </span>
                </div>
                <p class="mt-1 text-[11px] font-semibold text-gray-400">
                    The window an approval may name — a time outside these hours cannot be offered.
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

                <button type="button"
                        @click="save"
                        :disabled="saving || invalid"
                        class="h-9 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold shadow-sm transition disabled:opacity-60">
                    <span x-show="!saving">Save Office Hours</span>
                    <span x-show="saving">Saving...</span>
                </button>

            </div>
        </div>

        <p class="mt-3 text-[11px] font-bold text-red-500" x-show="invalid">
            Closing time must be after opening time.
        </p>

        <p class="mt-3 text-right text-[11px] font-semibold text-red-500"
           x-show="error" x-text="error"></p>
    </div>
@endif

<script>
    function officeHoursForm(open, close, url) {
        return {
            open,
            close,
            url,
            saving: false,
            error: '',

            /** Closing must read after opening, or the day is not a day. */
            get invalid() {
                const start = this.toMinutes(this.open);
                const end = this.toMinutes(this.close);

                return start >= 0 && end > start;
            },

            /** H:i on the clock to minutes past midnight, or -1 when unreadable. */
            toMinutes(clock) {
                const match = /^(\d{1,2}):(\d{2})$/.exec(String(clock || ''));

                if (!match) return -1;

                const minutes = Number(match[1]) * 60 + Number(match[2]);

                return minutes > 1439 ? -1 : minutes;
            },

            async save() {
                if (this.invalid) return;

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
