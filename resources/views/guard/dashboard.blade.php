<x-app-layout>

    <div class="py-6 sm:py-8"
         x-data="gateOverview({
             inside: {{ $insideCount }},
             today: {{ $todayCount }},
             expected: {{ $expectedCount }},
             logs: @js($logs),
         })"
         x-on:gate:scanned.window="absorb($event.detail)">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =============================================
                HEADER — post identity + live clock
            ============================================== --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-5">

                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#12347d]
                                 px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-widest
                                 text-blue-100 ring-1 ring-white/10">

                        <span class="w-2 h-2 rounded-full"
                              :class="cameraOnline ? 'bg-emerald-400 animate-pulse' : 'bg-blue-300/60'"></span>

                        Security / Gate Station · {{ $gateName }}
                    </span>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold text-gray-900">
                        Guard Dashboard
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Today's gate traffic at {{ $gateName }}. Scan a pass to verify
                        it and record the entry or exit.
                    </p>
                </div>

                <div class="flex items-stretch gap-3">

                    {{-- Live clock — the desk always knows the time of a scan --}}
                    <div class="shrink-0 rounded-2xl bg-white border border-gray-100
                                shadow-sm px-5 py-4 text-right min-w-[10.5rem]">
                        <p class="text-2xl font-extrabold text-gray-900 tabular-nums leading-none"
                           x-text="clock">—</p>
                        <p class="text-xs text-gray-500 mt-1.5" x-text="today">Today</p>
                    </div>

                    <a href="{{ route('guard.scanner') }}"
                       class="shrink-0 inline-flex flex-col justify-center gap-1 rounded-2xl
                              bg-[#12347d] px-5 py-4 text-white shadow-sm
                              hover:bg-[#0f2b68] transition">
                        <span class="inline-flex items-center gap-2 text-sm font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 7V5a1 1 0 011-1h2M20 7V5a1 1 0 00-1-1h-2M4 17v2a1 1 0 001 1h2M20 17v2a1 1 0 01-1 1h-2M8 12h8"/>
                            </svg>
                            Scan pass
                        </span>
                        <span class="text-[11px] text-blue-200">Open the QR scanner</span>
                    </a>

                </div>
            </div>


            {{-- =============================================
                STATS
            ============================================== --}}
            <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-5 py-4">
                    <p class="text-3xl font-extrabold text-blue-700 leading-none tabular-nums"
                       x-text="stats.expected">{{ $expectedCount }}</p>
                    <p class="text-[11px] text-gray-500 mt-2 font-bold uppercase tracking-wide">
                        Total Expected Visitors
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-5 py-4">
                    <p class="text-3xl font-extrabold text-slate-900 leading-none tabular-nums"
                       x-text="stats.today">{{ $todayCount }}</p>
                    <p class="text-[11px] text-gray-500 mt-2 font-bold uppercase tracking-wide">
                        Checked-In Today
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-5 py-4">
                    <p class="text-3xl font-extrabold text-emerald-600 leading-none tabular-nums"
                       x-text="stats.inside">{{ $insideCount }}</p>
                    <p class="text-[11px] text-gray-500 mt-2 font-bold uppercase tracking-wide">
                        Inside Campus Now
                    </p>
                </div>

                <div class="rounded-2xl bg-[#12347d] shadow-sm px-5 py-4 text-white">
                    <p class="text-sm font-bold leading-none">{{ $gateName }}</p>
                    <p class="text-[11px] text-blue-200 mt-2">
                        Active post · {{ auth()->user()->name }}
                    </p>
                    <p class="mt-2 inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        On duty
                    </p>
                </div>

            </div>


            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

                {{-- =============================================
                    CAMERA / SCANNER PREVIEW
                ============================================== --}}
                <section class="lg:col-span-3 bg-white rounded-2xl border border-gray-100
                                shadow-sm overflow-hidden">

                    <div class="px-5 sm:px-6 py-4 border-b border-gray-100
                                flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-gray-900">Scanner preview</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                The gate camera, live. Scanning happens on the Scan Pass page.
                            </p>
                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1
                                     text-[10px] font-bold uppercase tracking-wider ring-1"
                              :class="cameraOnline
                                  ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                  : 'bg-gray-50 text-gray-500 ring-gray-200'">
                            <span class="w-1.5 h-1.5 rounded-full"
                                  :class="cameraOnline ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                            <span x-text="cameraOnline ? 'Camera live' : 'Camera off'">Camera off</span>
                        </span>
                    </div>

                    <div class="p-4 sm:p-5">
                        <x-gate-scanner compact :gate-name="$gateName" />
                    </div>
                </section>


                {{-- =============================================
                    TODAY AT A GLANCE — latest entries
                ============================================== --}}
                <section class="lg:col-span-2 bg-white rounded-2xl border border-gray-100
                                shadow-sm overflow-hidden">

                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-gray-900">Latest gate activity</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Most recent arrivals and exits</p>
                        </div>

                        <a href="{{ route('guard.logbook') }}"
                           class="text-[11px] font-bold text-blue-700 hover:text-blue-900">
                            Full logbook →
                        </a>
                    </div>

                    <div class="divide-y divide-gray-50 max-h-[27rem] overflow-y-auto">

                        <template x-for="log in recent" :key="log.id">
                            <div class="px-5 py-3.5">
                                <div class="flex items-center justify-between gap-3">
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
                                          x-text="log.inside ? 'Inside' : 'Exited'">
                                    </span>
                                </div>

                                <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1
                                            text-[11px] text-gray-500">
                                    <span>In: <span class="font-bold text-gray-700" x-text="log.timeIn || '—'"></span></span>
                                    <span>Out: <span class="font-bold text-gray-700" x-text="log.timeOut || '—'"></span></span>
                                </div>
                            </div>
                        </template>

                        <div x-show="!recent.length" class="px-5 py-12 text-center">
                            <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 flex items-center
                                        justify-center mb-3">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M9 17v-6h13M9 17H4a1 1 0 01-1-1V5a1 1 0 011-1h16a1 1 0 011 1v6M9 17l3 3 7-7"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-600">No gate scans yet today</p>
                            <p class="text-xs text-gray-400 mt-1">
                                Verified passes appear here as they are scanned.
                            </p>
                        </div>

                    </div>

                </section>

            </div>


            {{-- Quick destinations --}}
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">

                <a href="{{ route('guard.scanner') }}"
                   class="group rounded-2xl bg-white border border-gray-100 shadow-sm px-5 py-4
                          hover:border-blue-200 hover:shadow transition">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-blue-700">Scan Pass / QR Verification</p>
                    <p class="text-xs text-gray-500 mt-1">Camera or USB scan of a visitor, claim, appointment or ID pass.</p>
                </a>

                <a href="{{ route('guard.logbook') }}"
                   class="group rounded-2xl bg-white border border-gray-100 shadow-sm px-5 py-4
                          hover:border-blue-200 hover:shadow transition">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-blue-700">Daily Logbook</p>
                    <p class="text-xs text-gray-500 mt-1">Today's complete audit trail of verified entries and exits.</p>
                </a>

            </div>

        </div>
    </div>

    <script>
        function gateOverview(initial) {
            return {
                stats: {
                    inside: initial.inside,
                    today: initial.today,
                    expected: initial.expected,
                },
                logs: initial.logs || [],
                clock: '',
                today: '',
                cameraOnline: false,
                timer: null,

                init() {
                    const campus = 'Asia/Manila';

                    const tick = () => {
                        const now = new Date();

                        this.clock = now.toLocaleTimeString('en-PH', {
                            timeZone: campus,
                            hour: 'numeric',
                            minute: '2-digit',
                            second: '2-digit',
                        });

                        this.today = now.toLocaleDateString('en-PH', {
                            timeZone: campus,
                            weekday: 'short',
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric',
                        });
                    };

                    tick();
                    setInterval(tick, 1000);

                    // Mirror the nested scanner's camera state into the badge.
                    const sync = () => {
                        const badge = document.querySelector('.gate-reader video');

                        this.cameraOnline = Boolean(badge);
                    };

                    setInterval(sync, 1000);

                    // Keep the numbers and the activity feed honest even when
                    // the scan happened on the desk's other page.
                    this.refresh();
                    setInterval(() => this.refresh(), 15000);
                },

                get recent() {
                    return this.logs.slice(0, 6);
                },

                absorb(detail) {
                    if (!detail) return;

                    if (detail.stats) this.stats = detail.stats;
                    if (Array.isArray(detail.logs)) this.logs = detail.logs;
                },

                async refresh() {
                    try {
                        const response = await fetch(@js(route('guard.logbook.data')), {
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
                    } catch (error) {
                        // Offline is not an error the desk needs to see; the
                        // next poll simply tries again.
                    }
                },
            };
        }
    </script>

</x-app-layout>
