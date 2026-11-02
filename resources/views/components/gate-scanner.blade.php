@props([
    'compact' => false,
    'gateName' => 'Main Gate',
])

<style>
    /*
     * The container is thrown away and rebuilt on every camera start
     * (html5-qrcode never restarts cleanly on a container it has already
     * used - see startCamera()), so the viewfinder styling is keyed to a
     * class instead of the #gate-reader id, and position carries !important
     * because the library forces an inline "position: relative" on whatever
     * element it binds to - without it the container collapses to the height
     * of its hidden canvas and the camera feed renders at zero height.
     */
    .gate-reader {
        position: absolute !important;
        inset: 0 !important;
        background: #020617;
        z-index: 10;
    }

    .gate-reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        position: absolute;
        inset: 0;
    }

    /* html5-qrcode names its chrome "<container id>__dashboard", and the id
       changes with every container, so hide it by suffix. */
    .gate-reader [id$="__dashboard"] {
        display: none !important;
    }

    /* The library's own shaded scan box is replaced by this component's
       corner brackets; leaving it on draws a second, misaligned frame. */
    .gate-reader #qr-shaded-region {
        display: none !important;
    }

    @keyframes gate-sweep {
        0%   { transform: translateY(0);    opacity: 0; }
        12%  { opacity: 1; }
        88%  { opacity: 1; }
        100% { transform: translateY(1000%); opacity: 0; }
    }
</style>

<div x-data="gateScanner()" x-on:beforeunload.window="stopCamera()">

    <div class="{{ $compact
        ? ''
        : 'bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden' }}">

        {{-- =========================================================
            CARD HEADER — only the full desk carries the controls
        ========================================================== --}}
        @unless ($compact)
            <div class="px-5 sm:px-6 py-5 border-b border-gray-100
                        flex flex-wrap items-center justify-between gap-3">

                <div>
                    <h2 class="font-bold text-gray-900">Scan pass QR</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Hold the pass inside the frame, or use the USB scanner below.
                    </p>
                </div>

                <div class="flex items-center gap-2">

                    <button type="button"
                            @click="soundOn = !soundOn"
                            :aria-pressed="soundOn"
                            :title="soundOn ? 'Sound feedback on' : 'Sound feedback off'"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition
                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="soundOn
                                ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100'
                                : 'bg-gray-50 text-gray-500 ring-1 ring-gray-200 hover:bg-gray-100'">
                        <span class="inline-flex items-center gap-1.5">
                            <svg x-show="soundOn" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z"/></svg>
                            <svg x-show="!soundOn" style="display: none" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z"/></svg>
                            <span x-text="soundOn ? 'Sound' : 'Muted'">Sound</span>
                        </span>
                    </button>

                    <button type="button"
                            @click="cameraOn ? stopCamera() : startCamera()"
                            class="rounded-xl px-4 py-2 text-xs font-bold transition
                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="cameraOn
                                ? 'bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100'
                                : 'bg-blue-700 text-white hover:bg-blue-800'">
                        <span x-text="cameraOn ? 'Stop camera' : 'Start camera'"></span>
                    </button>

                </div>
            </div>
        @endunless


        <div class="{{ $compact ? 'p-3' : 'p-5 sm:p-6 space-y-5' }}">

            {{-- =====================================================
                VIEWFINDER — always visible; the camera fills it
            ====================================================== --}}
            {{-- Inline aspect-ratio rather than a Tailwind arbitrary class: the
                 viewfinder must keep its height even when Tailwind's scanner
                 has not emitted the utility, or the camera fills nothing. --}}
            <div class="relative overflow-hidden rounded-2xl bg-slate-950
                        ring-1 transition-colors duration-300"
                 style="aspect-ratio: {{ $compact ? '16 / 10' : '4 / 3' }}; min-height: {{ $compact ? '12rem' : '18rem' }};"
                 :class="result
                     ? (result.valid
                         ? 'ring-4 ring-emerald-400/70'
                         : 'ring-4 ring-red-400/70')
                     : 'ring-white/10'">

                <div id="gate-reader" class="hidden gate-reader"></div>

                {{-- Corner brackets --}}
                <div class="pointer-events-none absolute inset-0 z-10">
                    <span class="absolute left-3 top-3 h-8 w-8 border-l-[3px] border-t-[3px] rounded-tl-lg transition-colors"
                          :class="cameraOn ? 'border-emerald-400' : 'border-white/25'"></span>
                    <span class="absolute right-3 top-3 h-8 w-8 border-r-[3px] border-t-[3px] rounded-tr-lg transition-colors"
                          :class="cameraOn ? 'border-emerald-400' : 'border-white/25'"></span>
                    <span class="absolute left-3 bottom-3 h-8 w-8 border-l-[3px] border-b-[3px] rounded-bl-lg transition-colors"
                          :class="cameraOn ? 'border-emerald-400' : 'border-white/25'"></span>
                    <span class="absolute right-3 bottom-3 h-8 w-8 border-r-[3px] border-b-[3px] rounded-br-lg transition-colors"
                          :class="cameraOn ? 'border-emerald-400' : 'border-white/25'"></span>

                    <div x-show="cameraOn"
                         x-transition.opacity
                         class="absolute left-[18%] right-[18%] top-[12%] h-[3px] rounded-full
                                bg-emerald-400/90 shadow-[0_0_14px_3px_rgba(52,211,153,0.55)]"
                         style="animation: gate-sweep 2.6s ease-in-out infinite;"></div>
                </div>

                {{-- Camera-off placeholder --}}
                <div x-show="!cameraOn"
                     x-transition.opacity
                     class="absolute inset-0 z-0 flex flex-col items-center justify-center gap-3
                            text-center px-6">

                    <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/15
                                flex items-center justify-center">
                        <svg class="w-7 h-7 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.66-.9l.82-1.2A2 2 0 0110.07 4h3.86a2 2 0 011.66.9l.82 1.2a2 2 0 001.66.9H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-white">Camera is off</p>
                        <p class="text-xs text-white/55 mt-1 max-w-xs">
                            Start the camera to scan passes hands-free
                            @unless ($compact)
                                or use the USB scanner below
                            @endunless
                            .
                        </p>
                    </div>

                    <button type="button"
                            @click="startCamera()"
                            class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-900
                                   hover:bg-slate-100 transition shadow-md">
                        Start camera
                    </button>
                </div>

                {{-- Live camera badge --}}
                <span class="absolute left-1/2 -translate-x-1/2 top-3 z-20 inline-flex items-center gap-1.5
                             rounded-full bg-black/55 backdrop-blur px-2.5 py-1 text-[10px] font-bold
                             uppercase tracking-wider text-white/90"
                      x-show="cameraOn" x-cloak>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live
                </span>

                @if ($compact)
                    {{-- Compact preview keeps the controls at hand --}}
                    <div class="absolute inset-x-0 bottom-0 z-20 flex items-center justify-between gap-2
                                bg-gradient-to-t from-black/75 to-transparent px-3 pb-3 pt-8">
                        <button type="button"
                                @click="cameraOn ? stopCamera() : startCamera()"
                                class="rounded-lg px-3 py-1.5 text-[11px] font-bold transition"
                                :class="cameraOn
                                    ? 'bg-white/15 text-white hover:bg-white/25'
                                    : 'bg-white text-slate-900 hover:bg-slate-100'">
                            <span x-text="cameraOn ? 'Stop' : 'Start'"></span>
                        </button>

                        <a href="{{ route('guard.scanner') }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5
                                  text-[11px] font-bold text-white hover:bg-blue-500 transition">
                            Open scanner
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12l-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>


            @unless ($compact)
                {{-- Status line --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold ring-1"
                          :class="cameraOn
                              ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                              : 'bg-gray-50 text-gray-500 ring-gray-200'">
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="cameraOn ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                        <span x-text="cameraOn
                            ? 'Scanning — hold the pass steady'
                            : 'Camera off — use the USB scanner below'">Camera off</span>
                    </span>

                    <span class="text-xs text-gray-400">
                        Visitor · Claim · Appointment · Digital ID
                    </span>
                </div>
            @endunless


            {{-- =====================================================
                USB SCANNER / PASTED LINK
            ====================================================== --}}
            <div class="{{ $compact ? 'mt-3 rounded-xl border border-gray-200 bg-slate-50 p-3' : 'rounded-2xl border border-gray-100 bg-slate-50 p-4' }}">

                <label for="gate-token"
                       class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">
                    USB scanner or pasted link
                </label>

                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text"
                           id="gate-token"
                           x-ref="token"
                           x-model="token"
                           @keyup.enter="submit()"
                           placeholder="Scan or paste the QR link / token"
                           autocomplete="off"
                           class="flex-1 rounded-xl border border-gray-300 bg-white px-3
                                  {{ $compact ? 'py-2 text-xs' : 'py-2.5 text-sm' }}
                                  font-mono shadow-sm focus:border-blue-500 focus:ring-blue-500">

                    <button type="button"
                            @click="submit()"
                            :disabled="!token || loading"
                            class="rounded-xl bg-blue-700 px-5 py-2 text-sm font-bold text-white
                                   hover:bg-blue-800 transition disabled:opacity-50
                                   disabled:cursor-not-allowed shrink-0">
                        <span x-text="loading ? 'Verifying…' : 'Verify pass'">Verify pass</span>
                    </button>
                </div>
            </div>


            {{-- =====================================================
                RESULT — the scanned pass and its verdict
            ====================================================== --}}
            <template x-if="result">
                <div class="{{ $compact ? 'mt-3' : 'mt-5' }} rounded-2xl border p-4 sm:p-5"
                     :class="panelClass">

                    <div class="flex items-start gap-3">
                        <span class="shrink-0 w-8 h-8 rounded-full bg-white/80 flex items-center
                                     justify-center text-base font-black leading-none"
                              x-text="result && result.valid ? '✓' : '✕'"></span>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold" x-text="result?.message"></p>

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
                              class="shrink-0 rounded-full bg-white/70 px-2.5 py-1 text-[10px]
                                     font-bold uppercase tracking-wide ring-1 ring-current">
                        </span>
                    </div>

                    <template x-if="result?.pass">
                        <div class="mt-4 flex items-start gap-3">
                            <div class="shrink-0 w-14 h-14 rounded-2xl overflow-hidden bg-white/80
                                        flex items-center justify-center">
                                <template x-if="result.pass.photoUrl">
                                    <img :src="result.pass.photoUrl" alt="Profile Picture"
                                         class="w-full h-full object-cover">
                                </template>
                                <template x-if="!result.pass.photoUrl">
                                    <span class="text-2xl font-black" x-text="result.pass.initial"></span>
                                </template>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-lg font-extrabold leading-tight" x-text="result.pass.name"></p>
                                <p class="text-xs font-semibold opacity-70 mt-0.5"
                                   x-text="result.pass.label + ' · ' + result.pass.reference"></p>
                            </div>
                        </div>
                    </template>

                    <dl x-show="result?.pass"
                        class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-xs">
                        <div class="flex justify-between gap-3">
                            <dt class="font-semibold opacity-70">ID / Reference</dt>
                            <dd class="font-bold text-right" x-text="result?.pass?.personId"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="font-semibold opacity-70">User Type</dt>
                            <dd class="font-bold text-right" x-text="result?.pass?.userType"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="font-semibold opacity-70">Target Department / Office</dt>
                            <dd class="font-bold text-right" x-text="result?.pass?.destination"></dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="font-semibold opacity-70">Purpose</dt>
                            <dd class="font-bold text-right" x-text="result?.pass?.purpose"></dd>
                        </div>

                        <template x-if="result?.pass?.date">
                            <div class="flex justify-between gap-3">
                                <dt class="font-semibold opacity-70">Appointment Date</dt>
                                <dd class="font-bold text-right" x-text="result.pass.date"></dd>
                            </div>
                        </template>

                        <template x-if="result?.pass?.scheduledTime">
                            <div class="flex justify-between gap-3">
                                <dt class="font-semibold opacity-70">Scheduled Time</dt>
                                <dd class="font-bold text-right" x-text="result.pass.scheduledTime"></dd>
                            </div>
                        </template>
                    </dl>
                </div>
            </template>

        </div>
    </div>


    {{-- The office's own Appointment Verification Pass, rendered
         server-side from the one shared card. Only the full desk
         shows it: the overview links here for the detail. --}}
    @unless ($compact)
        <template x-if="result?.passHtml">
            <section id="gate-pass" class="mt-6">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full"
                              :class="result?.valid ? 'bg-emerald-500' : 'bg-red-500'"></span>
                        <h2 class="text-sm font-extrabold uppercase tracking-widest text-gray-500">
                            Appointment Verification Pass
                        </h2>
                    </div>
                    <span class="text-xs font-semibold text-gray-400">
                        Scanned at {{ $gateName }}
                    </span>
                </div>

                <div x-html="result?.passHtml"></div>
            </section>
        </template>
    @endunless

</div>

@once
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
                audioCtx: null,
                // Id of the container the live scanner is reading into. Every
                // start() mints a new one - see startCamera().
                readerId: 'gate-reader',

                init() {
                    // Auto-start the camera so the desk is ready on arrival.
                    // The small delay lets the CDN library load and the DOM settle.
                    setTimeout(() => this.startCamera(), 500);
                },

                get panelClass() {
                    if (!this.result) return '';

                    const tone = this.result.pass?.statusTone;

                    if (tone === 'warning') {
                        return 'bg-amber-50 border-amber-200 text-amber-900';
                    }

                    return this.result.valid
                        ? 'bg-emerald-50 border-emerald-200 text-emerald-900'
                        : 'bg-red-50 border-red-200 text-red-900';
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

                        this.beep(this.result.valid ? 'ok' : 'bad');

                        // The overview and the logbook listen for this so the
                        // numbers and the audit trail move with the scan.
                        window.dispatchEvent(new CustomEvent('gate:scanned', {
                            detail: {
                                stats: this.result.stats,
                                logs: this.result.logs,
                            },
                        }));

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

                    // A camera failure is a scanner fault, never a scan
                    // result: it must not overwrite the pass already on
                    // screen, so it only speaks when nothing was scanned yet.
                    if (typeof Html5Qrcode === 'undefined') {
                        if (!this.result) {
                            this.result = {
                                valid: false,
                                pass: null,
                                passHtml: null,
                                message: 'The camera scanner could not load. Check the connection, or use the USB scanner.',
                            };
                            this.beep('bad');
                        }
                        return;
                    }

                    // html5-qrcode binds its internals to the id it is given,
                    // and a second start() on an id it has already stopped never
                    // settles. Each attempt therefore gets its own container.
                    const previous = document.getElementById(this.readerId);
                    const box = document.createElement('div');

                    this.readerId = 'gate-reader-' + Date.now();
                    box.id = this.readerId;
                    box.className = 'gate-reader';

                    if (previous) {
                        previous.replaceWith(box);
                    } else {
                        document.getElementById('gate-reader')?.appendChild(box);
                    }

                    try {
                        this.scanner = new Html5Qrcode(this.readerId);

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

                        if (!this.result) {
                            this.result = {
                                valid: false,
                                pass: null,
                                passHtml: null,
                                message: 'Camera unavailable: ' + error + '. Use the USB scanner instead.',
                            };
                            this.beep('bad');
                        }
                    }
                },

                async stopCamera() {
                    this.cameraOn = false;

                    if (!this.scanner) return;

                    // Only stop(): clear() would strip the container the next
                    // start() has already replaced, and the element is thrown
                    // away anyway.
                    try {
                        await this.scanner.stop();
                    } catch (error) {}

                    this.scanner = null;

                    const box = document.getElementById(this.readerId);
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
@endonce
