<x-app-layout>

    <div class="py-6 sm:py-8"
         x-data="gateLogbook({
             logs: @js($logs),
             search: @js($search),
             stats: @js([
                 'inside' => $insideCount,
                 'today' => $todayCount,
                 'expected' => $expectedCount,
             ]),
         })">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =============================================
                HEADER
            ============================================== --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-5">

                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#12347d]
                                 px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-widest
                                 text-blue-100 ring-1 ring-white/10">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Live audit trail · {{ $gateName }}
                    </span>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold text-gray-900">
                        Daily Logbook
                    </h1>

                    <p class="text-sm text-gray-500 mt-1" x-text="todayLabel">
                        Every verified entry and exit recorded today.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">

                    <button type="button"
                            @click="refresh()"
                            class="inline-flex items-center gap-2 rounded-xl bg-white border border-gray-200
                                   px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm
                                   hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" :class="loading ? 'animate-spin text-blue-600' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8 8 0 104.582 9M20 20v-5h-.581"/>
                        </svg>
                        Refresh
                    </button>

                    <a href="{{ route('guard.scanner') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5
                              text-xs font-bold text-white shadow-sm hover:bg-blue-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 7V5a1 1 0 011-1h2M20 7V5a1 1 0 00-1-1h-2M4 17v2a1 1 0 001 1h2M20 17v2a1 1 0 01-1 1h-2M8 12h8"/>
                        </svg>
                        Scan pass
                    </a>
                </div>
            </div>


            {{-- =============================================
                SUMMARY
            ============================================== --}}
            <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-5 py-4">
                    <p class="text-3xl font-extrabold text-slate-900 leading-none tabular-nums"
                       x-text="logs.length">{{ count($logs) }}</p>
                    <p class="text-[11px] text-gray-500 mt-2 font-bold uppercase tracking-wide">
                        Entries Today
                    </p>
                </div>
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-5 py-4">
                    <p class="text-3xl font-extrabold text-emerald-600 leading-none tabular-nums"
                       x-text="stats.inside">{{ $insideCount }}</p>
                    <p class="text-[11px] text-gray-500 mt-2 font-bold uppercase tracking-wide">
                        Still Inside
                    </p>
                </div>
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-5 py-4">
                    <p class="text-3xl font-extrabold text-blue-700 leading-none tabular-nums"
                       x-text="stats.expected">{{ $expectedCount }}</p>
                    <p class="text-[11px] text-gray-500 mt-2 font-bold uppercase tracking-wide">
                        Expected Visitors
                    </p>
                </div>
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-5 py-4">
                    <p class="text-sm font-bold text-gray-900 leading-none">{{ $gateName }}</p>
                    <p class="text-[11px] text-gray-500 mt-2">Last sync</p>
                    <p class="text-[11px] font-bold text-blue-700 tabular-nums" x-text="lastSync || '—'">—</p>
                </div>
            </div>


            {{-- =============================================
                LOGBOOK TABLE
            ============================================== --}}
            <section class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                <div class="px-5 sm:px-6 py-4 border-b border-gray-100
                            flex flex-col sm:flex-row sm:items-center justify-between gap-3">

                    <div>
                        <h2 class="font-bold text-gray-900">Verified gate sessions</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Checked-in and checked-out visitors for today, newest first.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50
                                     px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider
                                     text-emerald-700 ring-1 ring-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live
                        </span>

                        <span class="text-[11px] text-gray-400">Auto-refreshes every 15s</span>
                    </div>
                </div>

                <div class="px-5 sm:px-6 py-4 border-b border-gray-100">
                    <form method="GET" action="{{ route('guard.logbook') }}" class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                            </svg>
                        </span>

                        <input type="search"
                               name="q"
                               x-model="search"
                               value="{{ $search }}"
                               placeholder="Search by visitor name or reference code"
                               autocomplete="off"
                               class="w-full rounded-xl border border-gray-300 bg-gray-50 pl-9 pr-4 py-2.5
                                      text-sm focus:bg-white focus:border-blue-500 focus:ring-blue-500">
                    </form>
                </div>


                {{-- Desktop table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                <th scope="col" class="px-5 py-3">Visitor</th>
                                <th scope="col" class="px-5 py-3">Pass / Reference</th>
                                <th scope="col" class="px-5 py-3">Destination</th>
                                <th scope="col" class="px-5 py-3">Time in</th>
                                <th scope="col" class="px-5 py-3">Time out</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3 text-right">Logged by</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            <template x-for="log in filtered" :key="log.id">
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3.5">
                                        <p class="text-sm font-semibold text-gray-900" x-text="log.person || '—'"></p>
                                        <p class="text-[11px] text-gray-500" x-text="log.purpose || '—'"></p>
                                    </td>

                                    <td class="px-5 py-3.5">
                                        <p class="text-xs font-bold text-gray-700" x-text="log.typeLabel"></p>
                                        <p class="text-[11px] font-mono text-gray-500" x-text="log.reference || '—'"></p>
                                    </td>

                                    <td class="px-5 py-3.5">
                                        <span class="text-xs font-semibold text-gray-700" x-text="log.destination || '—'"></span>
                                    </td>

                                    <td class="px-5 py-3.5">
                                        <span class="text-xs font-bold text-gray-900 tabular-nums" x-text="log.timeIn || '—'"></span>
                                    </td>

                                    <td class="px-5 py-3.5">
                                        <span class="text-xs font-bold text-gray-900 tabular-nums" x-text="log.timeOut || '—'"></span>
                                    </td>

                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1
                                                     text-[10px] font-bold uppercase tracking-wide ring-1"
                                              :class="log.inside
                                                  ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                  : 'bg-slate-100 text-slate-600 ring-slate-200'">
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                  :class="log.inside ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                            <span x-text="log.inside ? 'Inside' : 'Checked out'"></span>
                                        </span>
                                    </td>

                                    <td class="px-5 py-3.5 text-right">
                                        <span class="text-[11px] text-gray-500" x-text="log.guard || '—'"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>


                {{-- Mobile cards --}}
                <div class="md:hidden divide-y divide-gray-100">
                    <template x-for="log in filtered" :key="'m-' + log.id">
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate"
                                       x-text="log.person || '—'"></p>
                                    <p class="text-[11px] text-gray-500 truncate"
                                       x-text="(log.typeLabel || 'Gate Pass') + ' · ' + (log.reference || '—')"></p>
                                </div>

                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold
                                             uppercase tracking-wide ring-1"
                                      :class="log.inside
                                          ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                          : 'bg-slate-100 text-slate-600 ring-slate-200'"
                                      x-text="log.inside ? 'Inside' : 'Checked out'">
                                </span>
                            </div>

                            <div class="mt-2 grid grid-cols-2 gap-2 text-[11px] text-gray-500">
                                <span>In: <span class="font-bold text-gray-700" x-text="log.timeIn || '—'"></span></span>
                                <span>Out: <span class="font-bold text-gray-700" x-text="log.timeOut || '—'"></span></span>
                                <span class="col-span-2 truncate" x-text="'→ ' + (log.destination || '—')"></span>
                            </div>
                        </div>
                    </template>
                </div>


                {{-- Empty state --}}
                <div x-show="!filtered.length" class="px-6 py-14 text-center">
                    <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 flex items-center
                                justify-center mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M9 17v-6h13M9 17H4a1 1 0 01-1-1V5a1 1 0 011-1h16a1 1 0 011 1v6M9 17l3 3 7-7"/>
                        </svg>
                    </div>

                    <p class="text-sm font-semibold text-gray-600"
                       x-text="search ? 'No matching scans today' : 'No gate scans yet today'">
                        No gate scans yet today
                    </p>

                    <p class="text-xs text-gray-400 mt-1">
                        Every verified pass appears here as an entry and an exit.
                    </p>
                </div>

            </section>

        </div>
    </div>

    <script>
        function gateLogbook(initial) {
            return {
                logs: initial.logs || [],
                search: initial.search || '',
                stats: initial.stats || { inside: 0, today: 0, expected: 0 },
                lastSync: '',
                loading: false,
                todayLabel: '',

                init() {
                    const campus = 'Asia/Manila';
                    this.todayLabel = new Date().toLocaleDateString('en-PH', {
                        timeZone: campus,
                        weekday: 'long',
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric',
                    }) + ' · every verified entry and exit recorded at the gate.';

                    this.refresh();
                    setInterval(() => this.refresh(), 15000);
                },

                /**
                 * Instant filter on the rows already loaded. The server search
                 * (`?q=`) still exists so a filtered logbook can be linked and
                 * printed; this only saves a round trip while typing.
                 */
                get filtered() {
                    const needle = (this.search || '').trim().toLowerCase();

                    if (!needle) return this.logs;

                    return this.logs.filter((log) =>
                        (log.person || '').toLowerCase().includes(needle)
                        || (log.reference || '').toLowerCase().includes(needle)
                    );
                },

                async refresh() {
                    if (this.loading) return;

                    this.loading = true;

                    try {
                        const url = new URL(@js(route('guard.logbook.data')), window.location.origin);

                        // The refresh follows the search that produced the page.
                        if ((this.search || '').trim() !== '') {
                            url.searchParams.set('q', this.search.trim());
                        }

                        const response = await fetch(url.toString(), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) return;

                        const data = await response.json();

                        if (Array.isArray(data.logs)) this.logs = data.logs;
                        if (data.stats) this.stats = data.stats;
                        if (data.serverTime) this.lastSync = data.serverTime;
                    } catch (error) {
                        // A failed poll leaves the table as it was; the next
                        // tick tries again.
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>

</x-app-layout>
