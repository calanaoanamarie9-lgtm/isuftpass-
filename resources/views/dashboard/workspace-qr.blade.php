<x-app-layout>

    @php
        /**
         * This page is written once but served from eight route names
         * (cici.qr.index, osas.qr.index, …). Its endpoint is declared beside
         * each scanner route in $workspaceRoutes, so swapping the suffix
         * resolves the check-in URL for whichever desk is looking at it.
         */
        $checkInUrl = route(preg_replace('/\.qr\.index$/', '.qr.check-in', (string) request()->route()?->getName()));
    @endphp

    <div class="py-10" x-data="qrCheckIn()" x-on:beforeunload.window="stopCamera()">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =============================================
                HEADER
            ============================================== --}}
            <div class="mb-8">
                <p class="mb-2 text-sm font-bold uppercase tracking-wide text-blue-600">{{ $office }} Desk</p>
                <h1 class="text-2xl font-extrabold text-gray-900">QR Scanner &amp; Check-in</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Scan an appointment pass or the student's digital ID to record their arrival at {{ $office }}.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

                {{-- =============================================
                    SCANNER
                ============================================== --}}
                <section class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                    <div class="px-6 py-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-gray-900">Scan QR Code</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Hold the pass inside the frame, or type the token below.</p>
                        </div>

                        <button type="button"
                                @click="cameraOn ? stopCamera() : startCamera()"
                                class="shrink-0 rounded-xl px-4 py-2 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="cameraOn
                                    ? 'bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100'
                                    : 'bg-blue-700 text-white hover:bg-blue-800'">
                            <span x-text="cameraOn ? 'Stop camera' : 'Start camera'"></span>
                        </button>
                    </div>

                    <div class="p-6 space-y-5">

                        {{-- VIEWFINDER --}}
                        <div id="qr-reader"
                             class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-gray-950">
                        </div>

                        <p x-show="cameraOn" x-transition class="text-center text-xs font-semibold text-gray-500">
                            Camera on &mdash; scanning continuously.
                        </p>

                        {{-- MANUAL ENTRY (USB scanners, pasted links) --}}
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text"
                                   x-model="token"
                                   @keyup.enter="submit()"
                                   placeholder="Paste the QR link or type the token"
                                   class="flex-1 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-mono">

                            <button type="button"
                                    @click="submit()"
                                    :disabled="!token || loading"
                                    class="rounded-xl bg-blue-700 px-6 py-2.5 text-sm font-bold text-white hover:bg-blue-800 transition disabled:opacity-50">
                                <span x-text="loading ? 'Checking…' : 'Check in'"></span>
                            </button>
                        </div>

                        {{-- RESULT --}}
                        <div x-show="result"
                             x-transition
                             class="rounded-2xl border p-5"
                             :class="panelClass">

                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-bold" x-text="result?.message"></p>

                                <span x-show="result?.status"
                                      x-text="result?.status"
                                      class="shrink-0 rounded-full bg-white/70 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 ring-current">
                                </span>
                            </div>

                            <dl x-show="result?.student"
                               class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-xs">
                                <div class="flex justify-between gap-3 sm:col-span-2 border-b border-black/10 pb-2">
                                    <dt class="font-semibold opacity-70">Student</dt>
                                    <dd class="font-bold text-right" x-text="result?.student"></dd>
                                </div>
                                <div x-show="result?.studentId" class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">Student ID</dt>
                                    <dd class="font-bold text-right" x-text="result?.studentId"></dd>
                                </div>
                                <div x-show="result?.purpose" class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">Purpose</dt>
                                    <dd class="font-bold text-right" x-text="result?.purpose"></dd>
                                </div>
                                <div x-show="result?.schedule" class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">Schedule</dt>
                                    <dd class="font-bold text-right" x-text="result?.schedule"></dd>
                                </div>
                                <div x-show="result?.office" class="flex justify-between gap-3">
                                    <dt class="font-semibold opacity-70">Booked with</dt>
                                    <dd class="font-bold text-right" x-text="result?.office"></dd>
                                </div>
                            </dl>
                        </div>

                    </div>
                </section>

                {{-- =============================================
                    CHECKED IN TODAY
                ============================================== --}}
                <section class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-gray-900">Checked in today</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Arrivals recorded at {{ $office }}</p>
                        </div>

                        <span class="shrink-0 rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-green-200"
                              x-text="checkIns.length + (checkIns.length === 1 ? ' student' : ' students')">
                        </span>
                    </div>

                    <div class="divide-y divide-gray-50 max-h-[28rem] overflow-y-auto">

                        <template x-for="(visit, index) in checkIns" :key="index">
                            <div class="px-6 py-4 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate" x-text="visit.student"></p>
                                    <p class="text-xs text-gray-500 truncate" x-text="visit.slot"></p>
                                </div>

                                <span class="shrink-0 rounded-full bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700 ring-1 ring-green-200"
                                      x-text="visit.time">
                                </span>
                            </div>
                        </template>

                        <div x-show="!checkIns.length" class="px-6 py-10 text-center">
                            <p class="text-sm font-semibold text-gray-600">No check-ins yet today</p>
                            <p class="text-xs text-gray-400 mt-1">Every arrival scanned here is recorded in this list.</p>
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
                checkIns: @js($checkIns),

                get panelClass() {
                    if (!this.result) return '';

                    if (!this.result.valid) {
                        return 'bg-red-50 border-red-100 text-red-800';
                    }

                    return this.result.checkedIn
                        ? 'bg-green-50 border-green-100 text-green-800'
                        : 'bg-amber-50 border-amber-100 text-amber-800';
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
                    } catch (error) {
                        this.result = {
                            valid: false,
                            checkedIn: false,
                            message: 'Could not reach the server. Check the connection and try again.',
                        };
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
