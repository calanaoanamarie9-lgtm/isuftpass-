<x-app-layout>
    @php
        $wsPrefix = $workspacePrefix ?? strtolower($office);
    @endphp
    {{--
        Availability for whichever office or department is signed in.

        One component owns both cards, so the schedule on the right is the same
        state the form on the left writes to — previously the schedule panel was
        outside x-data and sat on "Loading schedule..." forever, while save()
        posted to a GET-only route and came back 405. Both endpoints now come
        from this workspace's own route group.
    --}}
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">{{ $office }} Availability</h1>
                <p class="text-sm text-gray-500 mt-1">Manage the dates and time slots students can book with {{ $office }}.</p>
            </div>

            <x-office-hours :office="$office" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data="availabilityManager()">

                {{-- =================================================
                    SET AVAILABILITY
                ================================================== --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="font-bold text-gray-900 mb-4">Set Availability</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-1.5">Date</label>
                            <input type="date" x-model="date" :min="today" @change="onDateChange()"
                                   class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-1.5">Status</label>
                            <div class="flex flex-wrap gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" x-model="type" value="open" class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm font-semibold">Open Entire Day</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" x-model="type" value="closed" class="text-red-600 focus:ring-red-500">
                                    <span class="text-sm font-semibold">Closed</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" x-model="type" value="slots" class="text-yellow-600 focus:ring-yellow-500">
                                    <span class="text-sm font-semibold">Specific Slots</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="type === 'slots'" x-transition class="space-y-3">
                            <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-blue-950">Open first:</span>
                                    <div class="flex items-center gap-1.5">
                                        <input type="number" min="0" :max="allSlots.length" x-model.number="slotCount" @input="applySlotCount()"
                                               class="w-16 h-8 text-center text-xs font-extrabold rounded-lg border-gray-300 bg-white focus:ring-blue-500">
                                        <span class="text-xs font-bold text-gray-500">/ <span x-text="allSlots.length"></span> slots</span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-1.5 mt-2.5 pt-2 border-t border-blue-100/60">
                                    <span class="text-[10px] uppercase font-bold text-gray-400 mr-1">Quick:</span>
                                    <button type="button" @click="setQuickSlotCount(4)" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-white border border-gray-200 hover:bg-gray-50 text-gray-700">4 slots</button>
                                    <button type="button" @click="setQuickSlotCount(6)" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-white border border-gray-200 hover:bg-gray-50 text-gray-700">6 slots</button>
                                    <button type="button" @click="setQuickSlotCount(8)" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-white border border-gray-200 hover:bg-gray-50 text-gray-700">8 slots</button>
                                    <button type="button" @click="setQuickSlotCount(allSlots.length)" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200">All (<span x-text="allSlots.length"></span> slots)</button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-1.5">Choose Specific Slots</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ($timeSlots as $slot)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="{{ $slot }}" x-model="slots" @change="onSlotsChange()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-xs font-semibold text-gray-700">{{ $slot }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-1.5">Capacity per Slot (Optional)</label>
                            <input type="number" min="1" max="999" x-model.number="capacity" placeholder="Default capacity per slot"
                                   class="w-full sm:w-44 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row pt-2">
                            <button @click="save()" :disabled="saving || !date"
                                    class="flex-1 px-4 py-2.5 bg-blue-700 text-white text-sm font-bold rounded-xl hover:bg-blue-800 transition disabled:opacity-50">
                                <span x-text="saving ? 'Saving...' : (editing ? 'Update Availability' : 'Save Availability')"></span>
                            </button>

                            <button x-show="editing" @click="clear()" type="button"
                                    class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-200 transition">
                                Clear
                            </button>
                        </div>

                        <p x-show="message" x-transition
                           class="text-xs font-semibold text-green-700 bg-green-50 rounded-lg px-3 py-2"
                           x-text="message"></p>

                        <p x-show="error" x-transition
                           class="text-xs font-semibold text-red-700 bg-red-50 rounded-lg px-3 py-2"
                           x-text="error"></p>
                    </div>
                </div>

                {{-- =================================================
                    SCHEDULE
                ================================================== --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 self-start w-full">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900">Schedule</h2>
                        <span class="text-xs text-gray-400" x-text="loading ? 'Loading...' : (schedule.length + ' configured')"></span>
                    </div>

                    <p class="text-sm text-gray-500 mb-4">
                        Upcoming dates configured for {{ $office }}. Select one to edit it.
                    </p>

                    <div class="space-y-3">
                        {{-- Skeleton while the first fetch is in flight --}}
                        <template x-if="loading">
                            <div class="space-y-3" aria-hidden="true">
                                @foreach ([1, 2, 3] as $i)
                                    <div class="h-14 rounded-xl bg-gray-50 animate-pulse"></div>
                                @endforeach
                            </div>
                        </template>

                        {{-- Empty state --}}
                        <template x-if="!loading && schedule.length === 0">
                            <div class="px-4 py-8 text-center">
                                <p class="text-sm text-gray-400">No dates configured yet.</p>
                                <p class="text-xs text-gray-400 mt-1">Pick a date on the left and save it to open or block that day.</p>
                            </div>
                        </template>

                        {{-- Configured dates --}}
                        <template x-if="!loading && schedule.length > 0">
                            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                                <template x-for="entry in schedule" :key="entry.id">
                                    <button type="button" @click="edit(entry)"
                                            class="w-full text-left px-4 py-3 rounded-xl border transition"
                                            :class="date === entry.id ? 'border-blue-500 bg-blue-50/60' : 'border-gray-200 hover:bg-gray-50'">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-sm font-bold text-gray-900" x-text="entry.date"></span>
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"
                                                  :class="entry.type === 'closed' ? 'bg-red-50 text-red-700'
                                                       : (entry.type === 'open' ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700')"
                                                  x-text="entry.typeLabel"></span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1" x-text="entry.status"></p>
                                        <p class="text-[11px] text-gray-400 mt-1"
                                           x-show="entry.type === 'slots' && entry.slots.length"
                                           x-text="entry.slots.map(s => s.start).join(', ')"></p>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    function availabilityManager() {
        return {
            date: '',
            type: 'open',
            slots: @json($timeSlots),
            allSlots: @json($timeSlots),
            slotCount: {{ count($timeSlots) }},
            capacity: null,
            saving: false,
            loading: true,
            editing: false,
            message: '',
            error: '',
            schedule: [],
            today: new Date().toISOString().split('T')[0],

            init() {
                this.loadSchedule();
            },

            async loadSchedule() {
                this.loading = true;
                try {
                    const res = await fetch('{{ route($wsPrefix . ".availability.schedule") }}', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    this.schedule = data.schedule || [];
                } catch (e) {
                    this.schedule = [];
                } finally {
                    this.loading = false;
                }
            },

            applySlotCount() {
                const max = this.allSlots.length;
                let n = parseInt(this.slotCount);
                if (isNaN(n) || n < 0) n = 0;
                if (n > max) { n = max; this.slotCount = max; }
                this.slots = this.allSlots.slice(0, n);
            },

            setQuickSlotCount(n) {
                this.slotCount = n;
                this.applySlotCount();
            },

            onSlotsChange() {
                this.slotCount = this.slots.length;
            },

            async onDateChange() {
                if (!this.date) return;
                try {
                    const url = '{{ route($wsPrefix . ".availability.settings", ["date" => "DATE_PARAM"]) }}'.replace('DATE_PARAM', this.date);
                    const res = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    this.type = data.type || 'open';
                    this.slots = data.slots && data.slots.length ? data.slots : (this.type === 'open' ? [...this.allSlots] : []);
                    this.slotCount = this.type === 'open' ? this.allSlots.length : this.slots.length;
                    this.capacity = data.max_capacity || null;
                } catch (e) {}
            },

            /** Prefill the form from a saved date so it can be revised. */
            async edit(entry) {
                this.date = entry.id;
                this.type = entry.type;
                this.slots = entry.slots ? entry.slots.map(s => s.start) : [...this.allSlots];
                this.slotCount = this.type === 'open' ? this.allSlots.length : this.slots.length;
                this.editing = true;
                this.message = '';
                this.error = '';
                await this.onDateChange();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            clear() {
                this.date = '';
                this.type = 'open';
                this.slots = [...this.allSlots];
                this.slotCount = this.allSlots.length;
                this.capacity = null;
                this.editing = false;
                this.message = '';
                this.error = '';
            },

            async save() {
                if (!this.date) {
                    this.error = 'Please select a date.';
                    return;
                }

                this.saving = true;
                this.message = '';
                this.error = '';

                try {
                    const res = await fetch('{{ route($wsPrefix . ".availability.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            date: this.date,
                            type: this.type,
                            slots: this.slots,
                            max_capacity: this.capacity,
                        }),
                    });

                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        this.error = data.errors
                            ? Object.values(data.errors)[0][0]
                            : (data.message || 'Could not save availability.');
                        return;
                    }

                    this.message = data.message || 'Availability saved.';
                    this.schedule = data.schedule || this.schedule;
                    this.editing = false;
                } catch (e) {
                    this.error = 'Network error. Please try again.';
                } finally {
                    this.saving = false;
                }
            }
        };
    }
    </script>
</x-app-layout>
