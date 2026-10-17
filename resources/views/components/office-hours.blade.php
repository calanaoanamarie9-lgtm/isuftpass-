@props(['office' => null])

{{--
    Editing card for one office's hours and its slot times.

    The whole system reads its slot list from this card: each row is one
    bookable slot, so retyping a pair of times — 8:00 to 9:00 becoming 8:30
    to 9:30 — retimes that slot on every date's list, on the booking form,
    on the calendar, and in the capacity table. A card with no rows of its
    own falls back to the hours above, one slot to the hour.
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

    // The card edits slot times as clock pairs, so the day's labels arrive
    // as {start, end} rows in 24-hour form: the office's own times when it
    // has them, otherwise the hourly run its hours imply.
    $hoursClock = function (string $half): string {
        if (! preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', trim($half), $matches)) {
            return '';
        }

        $hour = ((int) $matches[1] % 12) + (strcasecmp($matches[3], 'PM') === 0 ? 12 : 0);

        return sprintf('%02d:%02d', $hour, (int) $matches[2]);
    };

    $hoursSlotRows = collect($hoursSlots)
        ->map(function (string $label) use ($hoursClock) {
            $halves = explode(' - ', $label);

            return ['start' => $hoursClock($halves[0] ?? ''), 'end' => $hoursClock($halves[1] ?? '')];
        })
        ->filter(fn (array $row) => $row['start'] !== '' && $row['end'] !== '')
        ->values()
        ->all();
@endphp

@if (\Illuminate\Support\Facades\Route::has($hoursRoute))
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6"
         x-data='officeHoursForm("{{ $hoursOpen }}", "{{ $hoursClose }}", {{ $hoursCapacity }}, "{{ route($hoursRoute) }}", @json($hoursSlotRows))'>

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
                           @change="hoursChanged"
                           class="mt-1 block rounded-xl border-gray-200 text-sm font-semibold text-gray-700 focus:border-blue-400 focus:ring-blue-400">
                </label>

                <label class="block">
                    <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Closes</span>
                    <input type="time"
                           step="60"
                           name="close_time"
                           value="{{ $hoursClose }}"
                           x-model="close"
                           @change="hoursChanged"
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

        {{-- The office's slot times: one editable row per bookable slot. --}}
        <div class="mt-5 pt-4 border-t border-gray-100">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-extrabold text-blue-950">Slot Times</p>
                    <p class="mt-0.5 text-[11px] font-semibold text-gray-400">
                        Every bookable slot of the day. Retype a pair of times — 8:00 to 9:00 can become 8:30 to 9:30 — then save.
                    </p>
                </div>

                <div class="flex gap-2">
                    <button type="button"
                            @click="backToHourly"
                            class="h-8 px-3 rounded-xl border border-gray-200 text-[11px] font-extrabold text-gray-500 hover:bg-gray-50 transition">
                        Back to hourly
                    </button>
                    <button type="button"
                            @click="addSlot"
                            class="h-8 px-3 rounded-xl border border-gray-200 text-[11px] font-extrabold text-blue-700 hover:bg-blue-50 transition">
                        + Add Slot
                    </button>
                </div>
            </div>

            <div class="mt-3 grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <template x-for="(row, index) in slotRows" :key="index">
                    <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 h-10">
                        <input type="time" step="60"
                               x-model="row.start"
                               @input="retimed"
                               aria-label="Slot starts"
                               class="border-0 bg-transparent p-0 text-sm font-semibold text-gray-700 focus:ring-0">
                        <span class="text-xs font-bold text-gray-300">&ndash;</span>
                        <input type="time" step="60"
                               x-model="row.end"
                               @input="retimed"
                               aria-label="Slot ends"
                               class="border-0 bg-transparent p-0 text-sm font-semibold text-gray-700 focus:ring-0">
                        <button type="button"
                                @click="removeSlot(index)"
                                class="ml-auto text-lg leading-none text-gray-300 transition hover:text-red-500"
                                aria-label="Remove this slot">&times;</button>
                    </div>
                </template>
            </div>

            <p class="mt-2 text-[11px] font-semibold text-gray-400" x-show="!slotRows.length">
                No rows left — saving rebuilds the day from the hours above.
            </p>
        </div>

        <p class="mt-3 text-right text-[11px] font-semibold text-red-500"
           x-show="error" x-text="error"></p>
    </div>
@endif

<script>
    function officeHoursForm(open, close, capacity, url, rows) {
        const same = (a, b) => JSON.stringify(a) === JSON.stringify(b);

        return {
            open,
            close,
            capacity: capacity || 8,
            url,
            slotRows: (rows || []).map(row => ({ start: row.start, end: row.end })),
            followsHours: true,
            saving: false,
            error: '',

            init() {
                this.syncWithHours();
            },

            /** Whether the rows are still exactly the hours' own hourly run. */
            syncWithHours() {
                this.followsHours = same(this.slotRows, this.hourlyRows(this.open, this.close));
            },

            /** Hours changed: a list nobody retimed keeps following them. */
            hoursChanged() {
                if (this.followsHours) {
                    this.slotRows = this.hourlyRows(this.open, this.close);
                }

                this.syncWithHours();
            },

            retimed() {
                this.syncWithHours();
            },

            backToHourly() {
                this.slotRows = this.hourlyRows(this.open, this.close);
                this.syncWithHours();
            },

            addSlot() {
                const last = this.slotRows[this.slotRows.length - 1];
                const start = last ? last.end : this.open;

                this.slotRows.push({ start, end: this.plusHour(start) });
                this.syncWithHours();
            },

            removeSlot(index) {
                this.slotRows.splice(index, 1);
                this.syncWithHours();
            },

            /** Whole hours from opening to closing, the way the server builds them. */
            hourlyRows(open, close) {
                const start = this.toMinutes(open);
                const end = this.toMinutes(close);
                const built = [];

                if (start < 0 || end - start < 60) {
                    return built;
                }

                for (let at = start; at + 60 <= end; at += 60) {
                    built.push({ start: this.toClock(at), end: this.toClock(at + 60) });
                }

                return built;
            },

            /** H:i on the clock to minutes past midnight, or -1 when unreadable. */
            toMinutes(clock) {
                const match = /^(\d{1,2}):(\d{2})$/.exec(String(clock || ''));

                if (!match) return -1;

                const minutes = Number(match[1]) * 60 + Number(match[2]);

                return minutes > 1439 ? -1 : minutes;
            },

            toClock(minutes) {
                return String(Math.floor(minutes / 60)).padStart(2, '0')
                    + ':' + String(minutes % 60).padStart(2, '0');
            },

            plusHour(clock) {
                const at = this.toMinutes(clock);

                return at < 0 ? clock : this.toClock(Math.min(at + 60, 1440));
            },

            async save() {
                // Commit an hours edit the time input hasn't blurred yet:
                // rows that follow the hours are rebuilt before posting.
                this.hoursChanged();

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
                            slot_definitions: this.slotRows,
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
