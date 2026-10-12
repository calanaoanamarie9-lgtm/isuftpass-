<x-app-layout>

    <style>
        /*
         * The camera feed fills the viewfinder whatever the video's
         * intrinsic size, and the library's own chrome stays out of it.
         */
        #gate-reader {
            position: absolute;
            inset: 0;
            background: #020617;
        }

        #gate-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            position: absolute;
            inset: 0;
        }

        #gate-reader__dashboard {
            display: none !important;
        }

        /* The sweep line that runs across the frame while scanning. */
        @keyframes gate-sweep {
            0%   { transform: translateY(0);    opacity: 0; }
            12%  { opacity: 1; }
            88%  { opacity: 1; }
            100% { transform: translateY(1000%); opacity: 0; }
        }
    </style>

    <div class="py-8" x-data="gateScanner()" x-on:beforeunload.window="stopCamera()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =============================================
                HEADER — post identity + live clock
            ============================================== --}}
            <div class="mb-7 flex flex-col sm:flex-row sm:items-end
                        justify-between gap-5">

                <div>
                    <span class="inline-flex items-center gap-2 rounded-full
                                 bg-slate-900 border border-slate-700 px-3.5 py-1.5
                                 text-xs font-bold uppercase tracking-widest
                                 text-amber-300">

                        <span class="w-2 h-2 rounded-full"
                              :class="cameraOn
                                  ? 'bg-emerald-400 animate-pulse'
                                  : 'bg-slate-500'"></span>

                        Campus Security · {{ $gateName }}
                    </span>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold
                               text-gray-900">
                        Gate Scanner &amp; Check-in
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Scan a Visitor Gate Pass, a Digital Claim Pass, an
                        appointment pass or a Digital ID to verify it and log
                        the entry or exit.
                    </p>
                </div>


                {{-- Live clock — the desk always knows the time of a scan --}}
                <div class="shrink-0 rounded-2xl bg-white border border-gray-100
                            shadow-sm px-5 py-4 text-right min-w-[10.5rem]">

                    <p class="text-2xl font-extrabold text-gray-900
                              tabular-nums leading-none"
                       x-text="clock">—</p>

                    <p class="text-xs text-gray-500 mt-1.5" x-text="today">Today</p>

                </div>

            </div>


            {{-- =============================================
                STATS
            ============================================== --}}
            <div class="mb-6 grid grid-cols-2 sm:grid-cols-3 gap-4">

                <div class="rounded-2xl bg-white border border-gray-100
                            shadow-sm px-5 py-4">
                    <p class="text-3xl font-extrabold text-emerald-600
                              leading-none tabular-nums"
                       x-text="stats.inside">{{ $insideCount }}</p>
                    <p class="text-xs text-gray-500 mt-1.5 font-semibold
                              uppercase tracking-wide">
                        Inside now
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-gray-100
                            shadow-sm px-5 py-4">
                    <p class="text-3xl font-extrabold text-slate-900
                              leading-none tabular-nums"
                       x-text="stats.today">{{ $todayCount }}</p>
                    <p class="text-xs text-gray-500 mt-1.5 font-semibold
                              uppercase tracking-wide">
                        Gate sessions today
                    </p>
                </div>

                <div class="col-span-2 sm:col-span-1 rounded-2xl bg-slate-900
                            shadow-sm px-5 py-4 text-white">
                    <p class="text-sm font-bold leading-none">{{ $gateName }}</p>
                    <p class="text-xs text-white/60 mt-1.5">
                        Active post · campus security desk
                    </p>
                </div>

            </div>


            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

                {{-- =============================================
                    SCANNER
                ============================================== --}}
                <section class="lg:col-span-3 bg-white rounded-2xl
                                border border-gray-100 shadow-sm overflow-hidden">

                    {{-- Card header --}}
                    <div class="px-6 py-5 border-b border-gray-100
                                flex flex-wrap items-center justify-between
                                gap-3">

                        <div>
                            <h2 class="font-bold text-gray-900">Scan pass QR</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Hold the pass inside the frame, or use the USB scanner below.
                            </p>
                        </div>


                        <div class="flex items-center gap-2">

                            {{-- Audible feedback so the operator need not look --}}
                            <button type="button"
                                    @click="soundOn = !soundOn"
                                    :aria-pressed="soundOn"
                                    :title="soundOn ? 'Sound feedback on' : 'Sound feedback off'"
                                    class="shrink-0 rounded-xl px-3 py-2 text-xs
                                           font-bold transition
                                           focus:outline-none focus:ring-2
                                           focus:ring-blue-500"
                                    :class="soundOn
                                        ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100'
                                        : 'bg-gray-50 text-gray-500 ring-1 ring-gray-200 hover:bg-gray-100'">
                                <span x-text="soundOn ? '🔊 Sound' : '🔇 Muted'">Sound</span>
                            </button>


                            <button type="button"
                                    @click="cameraOn ? stopCamera() : startCamera()"
                                    class="shrink-0 rounded-xl px-4 py-2 text-xs
                                           font-bold transition
                                           focus:outline-none focus:ring-2
                                           focus:ring-blue-500"
                                    :class="cameraOn
                                        ? 'bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100'
                                        : 'bg-blue-700 text-white hover:bg-blue-800'">
                                <span x-text="cameraOn ? 'Stop camera' : 'Start camera'"></span>
                            </button>

                        </div>

                    </div>


                    <div class="p-6 space-y-5">

                        {{-- VIEWFINDER — always visible; the camera fills it --}}
                        <div class="relative aspect-[4/3] overflow-hidden
                                    rounded-2xl bg-slate-950
                                    ring-1 transition-colors duration-300"
                             :class="result
                                 ? (result.valid
                                     ? 'ring-4 ring-emerald-400/70'
                                     : 'ring-4 ring-red-400/70')
                                 : 'ring-white/10'">

                            <div id="gate-reader" class="hidden"></div>


                            {{-- Corner brackets --}}
                            <div class="pointer-events-none absolute inset-0 z-10">

                                <span class="absolute left-4 top-4 h-9 w-9
                                             border-l-[3px] border-t-[3px]
                                             rounded-tl-lg transition-colors"
                                      :class="cameraOn
                                          ? 'border-emerald-400'
                                          : 'border-white/25'"></span>

                                <span class="absolute right-4 top-4 h-9 w-9
                                             border-r-[3px] border-t-[3px]
                                             rounded-tr-lg transition-colors"
                                      :class="cameraOn
                                          ? 'border-emerald-400'
                                          : 'border-white/25'"></span>

                                <span class="absolute left-4 bottom-4 h-9 w-9
                                             border-l-[3px] border-b-[3px]
                                             rounded-bl-lg transition-colors"
                                      :class="cameraOn
                                          ? 'border-emerald-400'
                                          : 'border-white/25'"></span>

                                <span class="absolute right-4 bottom-4 h-9 w-9
                                             border-r-[3px] border-b-[3px]
                                             rounded-br-lg transition-colors"
                                      :class="cameraOn
                                          ? 'border-emerald-400'
                                          : 'border-white/25'"></span>

                                {{-- Sweep line while the camera is live --}}
                                <div x-show="cameraOn"
                                     x-transition.opacity
                                     class="absolute left-[18%] right-[18%]
                                            top-[12%] h-[3px] rounded-full
                                            bg-emerald-400/90
                                            shadow-[0_0_14px_3px_rgba(52,211,153,0.55)]"
                                     style="animation: gate-sweep 2.6s ease-in-out infinite;">
                                </div>

                            </div>


                            {{-- Camera-off placeholder --}}
                            <div x-show="!cameraOn"
                                 x-transition.opacity
                                 class="absolute inset-0 z-20 flex flex-col
                                        items-center justify-center gap-4
                                        text-center px-6">

                                <div class="w-16 h-16 rounded-2xl bg-white/10
                                            border border-white/15
                                            flex items-center justify-center">

                                    <svg class="w-8 h-8 text-white/70"
                                         fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M3 9a2 2 0 012-2h.93a2 2 0 001.66-.9l.82-1.2A2 2 0 0110.07 4h3.86a2 2 0 011.66.9l.82 1.2a2 2 0 001.66.9H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z
                                                 M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>

                                </div>


                                <div>
                                    <p class="text-sm font-bold text-white">
                                        Camera is off
                                    </p>

                                    <p class="text-xs text-white/55 mt-1
                                              max-w-xs">
                                        Start the camera to scan passes
                                        hands-free — or use the USB scanner below.
                                    </p>
                                </div>


                                <button type="button"
                                        @click="startCamera()"
                                        class="rounded-xl bg-white px-5 py-2.5
                                               text-sm font-bold text-slate-900
                                               hover:bg-slate-100 transition
                                               shadow-md">
                                    Start camera
                                </button>

                            </div>

                        </div>


                        {{-- Status line --}}
                        <div class="flex flex-wrap items-center justify-between
                                    gap-3">

                            <span class="inline-flex items-center gap-2
                                         rounded-full px-3 py-1.5 text-xs
                                         font-bold ring-1"
                                  :class="cameraOn
                                      ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                      : 'bg-gray-50 text-gray-500 ring-gray-200'">

                                <span class="w-1.5 h-1.5 rounded-full"
                                      :class="cameraOn
                                          ? 'bg-emerald-500 animate-pulse'
                                          : 'bg-gray-400'"></span>

                                <span x-text="cameraOn
                                    ? 'Scanning — hold the pass steady'
                                    : 'Camera off — use the USB scanner below'">
                                    Camera off — use the USB scanner below
                                </span>
                            </span>

                            <span class="text-xs text-gray-400">
                                Visitor · Claim · Appointment · Digital ID
                            </span>

                        </div>


                        {{-- MANUAL ENTRY (USB scanners, pasted links) --}}
                        <div class="rounded-2xl border border-gray-100
                                    bg-slate-50 p-4">

                            <label for="gate-token"
                                   class="block text-xs font-bold text-gray-500
                                          uppercase tracking-wide mb-2">
                                USB scanner or pasted link
                            </label>

                            <div class="flex flex-col sm:flex-row gap-3">

                                <input type="text"
                                       id="gate-token"
                                       x-ref="token"
                                       x-model="token"
                                       @keyup.enter="submit()"
                                       placeholder="Scan or paste the QR link / token"
                                       autocomplete="off"
                                       class="flex-1 rounded-xl border
                                              border-gray-300 bg-white px-4
                                              py-2.5 text-sm font-mono
                                              shadow-sm focus:border-blue-500
                                              focus:ring-blue-500">

                                <button type="button"
                                        @click="submit()"
                                        :disabled="!token || loading"
                                        class="rounded-xl bg-blue-700 px-6
                                               py-2.5 text-sm font-bold
                                               text-white hover:bg-blue-800
                                               transition disabled:opacity-50
                                               disabled:cursor-not-allowed
                                               shrink-0">
                                    <span x-text="loading ? 'Verifying…' : 'Verify pass'">Verify pass</span>
                                </button>

                            </div>

                        </div>


                        {{-- RESULT — the scanned pass, and nothing else --}}
                        <div x-show="result"
                             x-transition
                             class="rounded-2xl border p-5"
                             :class="panelClass">

                            <div class="flex items-start gap-3">

                                <span class="shrink-0 w-9 h-9 rounded-full
                                             bg-white/80 flex items-center
                                             justify-center text-base
                                             font-black leading-none"
                                      x-text="!result ? '' : (result.valid ? '✓' : '✕')"></span>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold"
                                       x-text="result?.message"></p>

                                    <p x-show="result?.outcome"
                                       class="text-xs font-semibold opacity-80 mt-1"
                                       x-text="result?.outcome
                                           ? (result.outcome.direction === 'out'
                                               ? 'Time in ' + (result.outcome.timeIn || '—') + ' · Time out ' + result.outcome.timeOut
                                               : 'Time in ' + result.outcome.timeIn + ' · still inside')
                                           : ''"></p>
                                </div>

                                <span x-show="result?.pass?.statusLabel"
                                      x-text="result?.pass?.statusLabel"
                                      class="shrink-0 rounded-full bg-white/70
                                             px-2.5 py-1 text-[10px] font-bold
                                             uppercase tracking-wide
                                             ring-1 ring-current">
                                </span>

                            </div>


                            <template x-if="result?.pass">
                                <div class="mt-4 flex items-start gap-4">

                                    <div class="shrink-0 w-16 h-16 rounded-2xl
                                                overflow-hidden bg-white/80
                                                flex items-center justify-center">
                                        <template x-if="result.pass.photoUrl">
                                            <img :src="result.pass.photoUrl"
                                                 alt="Profile Picture"
                                                 class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!result.pass.photoUrl">
                                            <span class="text-2xl font-black"
                                                  x-text="result.pass.initial"></span>
                                        </template>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-lg font-extrabold leading-tight"
                                           x-text="result.pass.name"></p>

                                        <p class="text-xs font-semibold opacity-70 mt-0.5"
                                           x-text="result.pass.label + ' · ' + result.pass.reference"></p>
                                    </div>

                                </div>
                            </template>


                            <dl x-show="result?.pass"
                               class="mt-4 grid grid-cols-1 sm:grid-cols-2
                                      gap-x-6 gap-y-2 text-xs">

                                <div class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">ID / Reference</dt>
                                    <dd class="font-bold text-right"
                                        x-text="result?.pass?.personId"></dd>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">Destination</dt>
                                    <dd class="font-bold text-right"
                                        x-text="result?.pass?.destination"></dd>
                                </div>

                                <div class="flex justify-between gap-3 sm:col-span-2
                                            border-t border-black/10 pt-2">
                                    <dt class="font-semibold opacity-70">Purpose</dt>
                                    <dd class="font-bold text-right"
                                        x-text="result?.pass?.purpose"></dd>
                                </div>

                            </dl>

                        </div>

                    </div>

                </section>


                {{-- =============================================
                    DAILY GATE ACCESS LOGS
                ============================================== --}}
                <section class="lg:col-span-2 bg-white rounded-2xl
                                border border-gray-100 shadow-sm overflow-hidden">

                    <div class="px-6 py-5 border-b border-gray-100">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="font-bold text-gray-900">Daily Gate Access Logs</h2>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Today's scans at {{ $gateName }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-50
                                         px-3 py-1 text-xs font-bold
                                         text-emerald-700 ring-1 ring-emerald-200"
                                  x-text="filteredLogs.length + (filteredLogs.length === 1 ? ' entry' : ' entries')">
                                {{ count($logs) }} {{ Str::plural('entry', count($logs)) }}
                            </span>
                        </div>

                        {{-- Manual search by name or reference code --}}
                        <form method="GET"
                              action="{{ route('guard.dashboard') }}"
                              class="mt-4">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3
                                             flex items-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                    </svg>
                                </span>

                                <input type="search"
                                       name="q"
                                       x-model="search"
                                       value="{{ $search }}"
                                       placeholder="Search by name or reference code"
                                       autocomplete="off"
                                       class="w-full rounded-xl border border-gray-300
                                              bg-gray-50 pl-9 pr-4 py-2.5 text-sm
                                              focus:bg-white focus:border-blue-500
                                              focus:ring-blue-500">
                            </div>
                        </form>
                    </div>


                    <div class="divide-y divide-gray-50 max-h-[34rem] overflow-y-auto">

                        <template x-for="log in filteredLogs" :key="log.id">
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate"
                                           x-text="log.person || '—'"></p>
                                        <p class="text-xs text-gray-500 truncate"
                                           x-text="(log.typeLabel || 'Gate Pass') + ' · ' + (log.reference || '—')"></p>
                                    </div>

                                    <span class="shrink-0 rounded-full px-2.5 py-1
                                                 text-[10px] font-bold uppercase
                                                 tracking-wide ring-1"
                                          :class="log.inside
                                              ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                              : 'bg-slate-100 text-slate-600 ring-slate-200'"
                                          x-text="log.inside ? 'Inside' : 'Exited'">
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1
                                            text-xs text-gray-500">
                                    <span>In: <span class="font-bold text-gray-700"
                                                 x-text="log.timeIn || '—'"></span></span>
                                    <span>Out: <span class="font-bold text-gray-700"
                                                  x-text="log.timeOut || '—'"></span></span>
                                    <span class="truncate" x-show="log.destination"
                                          x-text="'→ ' + log.destination"></span>
                                </div>
                            </div>
                        </template>


                        {{-- Empty state --}}
                        <div x-show="!filteredLogs.length" class="px-6 py-12 text-center">
                            <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100
                                        flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-slate-400" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M9 17v-6h13M9 17H4a1 1 0 01-1-1V5a1 1 0 011-1h16a1 1 0 011 1v6M9 17l3 3 7-7"/>
                                </svg>
                            </div>

                            <p class="text-sm font-semibold text-gray-600"
                               x-text="search ? 'No matching scans today' : 'No gate scans yet today'"></p>

                            <p class="text-xs text-gray-400 mt-1">
                                Every verified pass appears here as an entry
                                and an exit.
                            </p>
                        </div>

                    </div>

                </section>

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        function gateScanner() {
            return {
                token: '',
                loading: false,
                result: null,
                cameraOn: false,
                scanner: null,
                lastCode: '',
                lastReadAt: 0,
                soundOn: true,
                clock: '',
                today: '',
                audioCtx: null,
                search: @js($search),
                logs: @js($logs),
                stats: @js(['inside' => $insideCount, 'today' => $todayCount]),

                /**
                 * The desk clock keeps itself current — every time on screen
                 * is read against it. It is pinned to campus time rather than
                 * the browser's, so it always agrees with the log below it
                 * (which is written in UTC and shown in campus time).
                 */
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

                    this.$nextTick(() => this.$refs.token?.focus());
                },

                /**
                 * Live filter over the day's log — name or reference code,
                 * case-insensitive, so the guard can type either.
                 */
                get filteredLogs() {
                    const needle = (this.search || '').trim().toLowerCase();

                    if (!needle) return this.logs;

                    return this.logs.filter((log) =>
                        (log.person || '').toLowerCase().includes(needle)
                        || (log.reference || '').toLowerCase().includes(needle)
                    );
                },

                get panelClass() {
                    if (!this.result) return '';

                    return this.result.valid
                        ? 'bg-emerald-50 border-emerald-100 text-emerald-800'
                        : 'bg-red-50 border-red-100 text-red-800';
                },

                /**
                 * A rising pair for a valid pass, a low buzz for a rejection —
                 * the operator can keep their eyes on the queue and still
                 * hear the outcome.
                 */
                beep(kind) {
                    if (!this.soundOn) return;

                    try {
                        const Ctx = window.AudioContext || window.webkitAudioContext;

                        if (!Ctx) return;

                        if (!this.audioCtx) this.audioCtx = new Ctx();
                        const ctx = this.audioCtx;

                        if (ctx.state === 'suspended') ctx.resume();

                        const notes = kind === 'ok'
                            ? [[880, 0, 0.09], [1318.5, 0.1, 0.13]]
                            : [[246.94, 0, 0.18]];

                        notes.forEach(([freq, delay, duration]) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();

                            osc.type = kind === 'bad' ? 'square' : 'sine';
                            osc.frequency.value = freq;

                            const at = ctx.currentTime + delay;

                            gain.gain.setValueAtTime(0.0001, at);
                            gain.gain.exponentialRampToValueAtTime(0.22, at + 0.012);
                            gain.gain.exponentialRampToValueAtTime(0.0001, at + duration);

                            osc.connect(gain);
                            gain.connect(ctx.destination);

                            osc.start(at);
                            osc.stop(at + duration + 0.03);
                        });
                    } catch (error) {}
                },

                /**
                 * One POST answers the desk's questions: whose pass is this,
                 * is it valid, and what did this scan just record?
                 */
                async submit() {
                    const value = this.token.trim();

                    if (!value || this.loading) return;

                    this.loading = true;
                    this.result = null;

                    try {
                        const response = await fetch(@js(route('guard.scan')), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ token: value }),
                        });

                        this.result = await response.json();

                        if (Array.isArray(this.result.logs)) {
                            this.logs = this.result.logs;
                        }

                        if (this.result.stats) {
                            this.stats = this.result.stats;
                        }

                        this.beep(this.result.valid ? 'ok' : 'bad');

                        // A cleared field and a focused input are what a USB
                        // scanner and the next person in line both expect.
                        if (this.result.valid) {
                            this.token = '';
                        }

                        this.$refs.token?.focus();
                    } catch (error) {
                        this.result = {
                            valid: false,
                            pass: null,
                            message: 'Could not reach the server. Check the connection and try again.',
                        };

                        this.beep('bad');
                    } finally {
                        this.loading = false;
                    }
                },

                async startCamera() {
                    if (this.cameraOn) return;

                    if (typeof Html5Qrcode === 'undefined') {
                        this.result = {
                            valid: false,
                            pass: null,
                            message: 'The camera scanner could not load. Check the connection, or use the USB scanner.',
                        };
                        this.beep('bad');
                        return;
                    }

                    const box = document.getElementById('gate-reader');
                    box.classList.remove('hidden');

                    this.scanner = new Html5Qrcode('gate-reader');

                    try {
                        await this.scanner.start(
                            { facingMode: 'environment' },
                            { fps: 10, qrbox: { width: 230, height: 230 } },
                            (decoded) => this.read(decoded),
                            () => {}
                        );

                        this.cameraOn = true;
                    } catch (error) {
                        box.classList.add('hidden');
                        this.scanner = null;
                        this.result = {
                            valid: false,
                            pass: null,
                            message: 'Camera unavailable: ' + error + '. Use the USB scanner instead.',
                        };
                        this.beep('bad');
                    }
                },

                async stopCamera() {
                    this.cameraOn = false;

                    if (!this.scanner) return;

                    try {
                        await this.scanner.stop();
                        this.scanner.clear();
                    } catch (error) {}

                    this.scanner = null;

                    const box = document.getElementById('gate-reader');
                    if (box) box.classList.add('hidden');
                },

                /**
                 * A pass held in front of the lens decodes many times a second,
                 * so the same code inside three seconds is the same pass
                 * rather than a second scan.
                 */
                async read(code) {
                    const now = Date.now();

                    if (this.loading) return;
                    if (code === this.lastCode && now - this.lastReadAt < 3000) return;

                    this.lastCode = code;
                    this.lastReadAt = now;
                    this.token = code;

                    await this.submit();
                },

                destroy() {
                    this.stopCamera();
                },
            };
        }
    </script>

</x-app-layout>
