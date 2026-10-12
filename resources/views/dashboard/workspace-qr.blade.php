<x-app-layout>

    @php
        /**
         * This page is written once but served from nine route names
         * (cici.qr.index, osas.qr.index, …). Its endpoint is declared beside
         * each scanner route in $workspaceRoutes, so swapping the suffix
         * resolves the check-in URL for whichever desk is looking at it.
         *
         * This view owns the <x-app-layout> tag — the nine office wrappers
         * around it are plain @includes, so the page renders one sidebar,
         * not two.
         */
        $checkInUrl = route(preg_replace('/\.qr\.index$/', '.qr.check-in', (string) request()->route()?->getName()));
    @endphp

    <style>
        /*
         * The camera feed fills the viewfinder frame whatever the video's
         * intrinsic size, and the library's own dashboard chrome stays out
         * of it — the frame below is the only chrome this desk needs.
         */
        #qr-reader {
            position: absolute;
            inset: 0;
            background: #020617;
        }

        #qr-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            position: absolute;
            inset: 0;
        }

        #qr-reader__dashboard {
            display: none !important;
        }

        /* The sweep line that runs across the frame while scanning. */
        @keyframes qr-sweep {
            0%   { transform: translateY(0);    opacity: 0; }
            12%  { opacity: 1; }
            88%  { opacity: 1; }
            100% { transform: translateY(1000%); opacity: 0; }
        }
    </style>

    <div class="py-10" x-data="qrCheckIn()" x-on:beforeunload.window="stopCamera()">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =============================================
                HEADER — desk identity + live clock
            ============================================== --}}
            <div class="mb-8 flex flex-col sm:flex-row sm:items-end
                        justify-between gap-5">

                <div>
                    <span class="inline-flex items-center gap-2 rounded-full
                                 bg-blue-50 border border-blue-100 px-3.5 py-1.5
                                 text-xs font-bold uppercase tracking-wide
                                 text-blue-700">

                        <span class="w-2 h-2 rounded-full"
                              :class="cameraOn
                                  ? 'bg-emerald-500 animate-pulse'
                                  : 'bg-blue-400'"></span>

                        {{ $office }} Desk
                    </span>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold
                               text-gray-900">
                        QR Scanner &amp; Check-in
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Scan an appointment pass or the student's digital ID
                        to record their arrival at {{ $office }}.
                    </p>
                </div>


                {{-- Live clock — the desk always knows the time of an arrival --}}
                <div class="shrink-0 rounded-2xl bg-white border border-gray-100
                            shadow-sm px-5 py-4 text-right min-w-[10.5rem]">

                    <p class="text-2xl font-extrabold text-gray-900
                              tabular-nums leading-none"
                       x-text="clock">—</p>

                    <p class="text-xs text-gray-500 mt-1.5" x-text="today">Today</p>

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
                            <h2 class="font-bold text-gray-900">Scan QR Code</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Hold the pass inside the frame, or type the token below.
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
                                     ? (result.checkedIn
                                         ? 'ring-4 ring-emerald-400/70'
                                         : 'ring-4 ring-amber-400/70')
                                     : 'ring-4 ring-red-400/70')
                                 : 'ring-white/10'">

                            <div id="qr-reader" class="hidden"></div>


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
                                     style="animation: qr-sweep 2.6s ease-in-out infinite;">
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
                                        hands-free — or type the token below.
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
                                    : 'Camera off — enter the token below'">
                                    Camera off — enter the token below
                                </span>
                            </span>

                            <span class="text-xs text-gray-400">
                                Accepts appointment passes &amp; digital IDs
                            </span>

                        </div>


                        {{-- MANUAL ENTRY (USB scanners, pasted links) --}}
                        <div class="rounded-2xl border border-gray-100
                                    bg-slate-50 p-4">

                            <label for="qr-token"
                                   class="block text-xs font-bold text-gray-500
                                          uppercase tracking-wide mb-2">
                                USB scanner or pasted link
                            </label>

                            <div class="flex flex-col sm:flex-row gap-3">

                                <input type="text"
                                       id="qr-token"
                                       x-ref="token"
                                       x-model="token"
                                       @keyup.enter="submit()"
                                       placeholder="Paste the QR link or type the token"
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
                                    <span x-text="loading ? 'Checking…' : 'Check in'">Check in</span>
                                </button>

                            </div>

                        </div>


                        {{-- RESULT --}}
                        <div x-show="result"
                             x-transition
                             class="rounded-2xl border p-5"
                             :class="panelClass">

                            <div class="flex items-start gap-3">

                                <span class="shrink-0 w-9 h-9 rounded-full
                                             bg-white/80 flex items-center
                                             justify-center text-base
                                             font-black leading-none"
                                      x-text="!result
                                          ? ''
                                          : (result.valid
                                              ? (result.checkedIn ? '✓' : '!')
                                              : '✕')"></span>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold"
                                       x-text="result?.message"></p>
                                </div>

                                <span x-show="result?.status"
                                      x-text="result?.status"
                                      class="shrink-0 rounded-full bg-white/70
                                             px-2.5 py-1 text-[10px] font-bold
                                             uppercase tracking-wide
                                             ring-1 ring-current">
                                </span>

                            </div>

                            <dl x-show="result?.student"
                               class="mt-4 grid grid-cols-1 sm:grid-cols-2
                                      gap-x-6 gap-y-2 text-xs">

                                <div class="flex justify-between gap-3
                                            sm:col-span-2 border-b
                                            border-black/10 pb-2">
                                    <dt class="font-semibold opacity-70">Student</dt>
                                    <dd class="font-bold text-right"
                                        x-text="result?.student"></dd>
                                </div>

                                <div x-show="result?.studentId"
                                     class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">Student ID</dt>
                                    <dd class="font-bold text-right"
                                        x-text="result?.studentId"></dd>
                                </div>

                                <div x-show="result?.purpose"
                                     class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">Purpose</dt>
                                    <dd class="font-bold text-right"
                                        x-text="result?.purpose"></dd>
                                </div>

                                <div x-show="result?.schedule"
                                     class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">Schedule</dt>
                                    <dd class="font-bold text-right"
                                        x-text="result?.schedule"></dd>
                                </div>

                                <div x-show="result?.office"
                                     class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">Booked with</dt>
                                    <dd class="font-bold text-right"
                                        x-text="result?.office"></dd>
                                </div>

                            </dl>

                        </div>

                    </div>

                </section>


                {{-- =============================================
                    TODAY — count + arrivals
                ============================================== --}}
                <section class="lg:col-span-2 space-y-6">

                    {{-- Headline count --}}
                    <div class="bg-white rounded-2xl border border-gray-100
                                shadow-sm p-6">

                        <div class="flex items-center gap-4">

                            <div class="w-14 h-14 rounded-2xl bg-emerald-100
                                        text-emerald-700 flex items-center
                                        justify-center shrink-0">

                                <svg class="w-7 h-7" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0
                                             11-18 0 9 9 0 0118 0z"/>
                                </svg>

                            </div>


                            <div class="min-w-0">
                                <p class="text-3xl font-extrabold text-gray-900
                                          leading-none tabular-nums"
                                   x-text="checkIns.length">{{ count($checkIns) }}</p>

                                <p class="text-xs text-gray-500 mt-1.5">
                                    students checked in at {{ $office }} today
                                </p>
                            </div>

                        </div>

                    </div>


                    {{-- Arrivals list --}}
                    <div class="bg-white rounded-2xl border border-gray-100
                                shadow-sm overflow-hidden">

                        <div class="px-6 py-5 border-b border-gray-100
                                    flex items-center justify-between gap-3">

                            <div>
                                <h2 class="font-bold text-gray-900">Checked in today</h2>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Arrivals recorded at {{ $office }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-50
                                         px-3 py-1 text-xs font-bold
                                         text-emerald-700 ring-1
                                         ring-emerald-200"
                                  x-text="checkIns.length + (checkIns.length === 1 ? ' student' : ' students')">
                                {{ count($checkIns) }} {{ Str::plural('student', count($checkIns)) }}
                            </span>

                        </div>


                        <div class="divide-y divide-gray-50 max-h-[28rem]
                                    overflow-y-auto">

                            <template x-for="(visit, index) in checkIns"
                                      :key="index">

                                <div class="px-6 py-4 flex items-center
                                            justify-between gap-3">

                                    <div class="flex items-center gap-3 min-w-0">

                                        <span class="shrink-0 w-9 h-9
                                                     rounded-full bg-blue-50
                                                     text-blue-700 flex
                                                     items-center justify-center
                                                     text-sm font-extrabold
                                                     uppercase"
                                              x-text="(visit.student || '?').charAt(0)"></span>

                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold
                                                      text-gray-900 truncate"
                                               x-text="visit.student"></p>
                                            <p class="text-xs text-gray-500
                                                      truncate"
                                               x-text="visit.slot"></p>
                                        </div>

                                    </div>


                                    <span class="shrink-0 rounded-full
                                                 bg-emerald-50 px-2.5 py-1
                                                 text-xs font-bold
                                                 text-emerald-700 ring-1
                                                 ring-emerald-200"
                                          x-text="visit.time">
                                    </span>

                                </div>

                            </template>


                            <div x-show="!checkIns.length"
                                 class="px-6 py-10 text-center">

                                <div class="mx-auto w-12 h-12 rounded-2xl
                                            bg-slate-100 flex items-center
                                            justify-center mb-3">

                                    <svg class="w-6 h-6 text-slate-400"
                                         fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M17 20h5v-2a3 3 0
                                                 00-5.356-1.857
                                                 M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                                                 M7 20H2v-2a3 3 0
                                                 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                                                 m0 0a5.002 5.002 0
                                                 019.288 0M15 7a3 3 0
                                                 11-6 0 3 3 0 016 0z"/>
                                    </svg>

                                </div>

                                <p class="text-sm font-semibold text-gray-600">
                                    No check-ins yet today
                                </p>

                                <p class="text-xs text-gray-400 mt-1">
                                    Every arrival scanned here is recorded
                                    in this list.
                                </p>

                            </div>

                        </div>

                    </div>

                </section>

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        function qrCheckIn() {
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
                checkIns: @js($checkIns),

                /**
                 * The desk clock starts with the page and keeps itself
                 * current — every arrival time on screen is read against it.
                 */
                init() {
                    const tick = () => {
                        const now = new Date();

                        this.clock = now.toLocaleTimeString([], {
                            hour: 'numeric',
                            minute: '2-digit',
                            second: '2-digit',
                        });

                        this.today = now.toLocaleDateString([], {
                            weekday: 'short',
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric',
                        });
                    };

                    tick();
                    setInterval(tick, 1000);
                },

                get panelClass() {
                    if (!this.result) return '';

                    if (!this.result.valid) {
                        return 'bg-red-50 border-red-100 text-red-800';
                    }

                    return this.result.checkedIn
                        ? 'bg-emerald-50 border-emerald-100 text-emerald-800'
                        : 'bg-amber-50 border-amber-100 text-amber-800';
                },

                /**
                 * A rising pair for an arrival, a single tone for a warning,
                 * a low buzz for a rejection — the operator can keep their
                 * eyes on the queue and still hear the outcome.
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
                            : kind === 'warn'
                                ? [[659.25, 0, 0.16]]
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
                 * One POST answers all three desk questions: whose visit, is it
                 * ours, may it be marked as arrived.
                 */
                async submit() {
                    const value = this.token.trim();

                    if (!value || this.loading) return;

                    this.loading = true;
                    this.result = null;

                    try {
                        const response = await fetch(@js($checkInUrl), {
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

                        if (Array.isArray(this.result.checkIns)) {
                            this.checkIns = this.result.checkIns;
                        }

                        this.beep(!this.result.valid
                            ? 'bad'
                            : (this.result.checkedIn ? 'ok' : 'warn'));

                        // A cleared field and a focused input are what a USB
                        // scanner and the next student in line both expect.
                        if (this.result.valid && this.result.checkedIn) {
                            this.token = '';
                        }

                        this.$refs.token?.focus();
                    } catch (error) {
                        this.result = {
                            valid: false,
                            checkedIn: false,
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
                            checkedIn: false,
                            message: 'The camera scanner could not load. Check the connection, or type the token instead.',
                        };
                        this.beep('bad');
                        return;
                    }

                    const box = document.getElementById('qr-reader');
                    box.classList.remove('hidden');

                    this.scanner = new Html5Qrcode('qr-reader');

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
                            checkedIn: false,
                            message: 'Camera unavailable: ' + error + '. Type the token instead.',
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

                    const box = document.getElementById('qr-reader');
                    if (box) box.classList.add('hidden');
                },

                /**
                 * A pass held in front of the lens decodes many times a second,
                 * so the same code inside three seconds is the same student
                 * rather than a new arrival.
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
