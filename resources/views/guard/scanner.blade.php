<x-app-layout>

    <div class="py-6 sm:py-8"
         x-data="{
             clock: '',
             today: '',
             init() {
                 const campus = 'Asia/Manila';
                 const tick = () => {
                     const now = new Date();
                     this.clock = now.toLocaleTimeString('en-PH', {
                         timeZone: campus, hour: 'numeric', minute: '2-digit', second: '2-digit',
                     });
                     this.today = now.toLocaleDateString('en-PH', {
                         timeZone: campus, weekday: 'short', month: 'short', day: 'numeric', year: 'numeric',
                     });
                 };
                 tick();
                 setInterval(tick, 1000);
             },
         }">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-5">

                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#12347d]
                                 px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-widest
                                 text-blue-100 ring-1 ring-white/10">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 7V5a1 1 0 011-1h2M20 7V5a1 1 0 00-1-1h-2M4 17v2a1 1 0 001 1h2M20 17v2a1 1 0 01-1 1h-2M8 12h8"/>
                        </svg>
                        {{ $gateName }} · Scan station
                    </span>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold text-gray-900">
                        Scan Pass / QR Verification
                    </h1>

                    <p class="text-sm text-gray-500 mt-1 max-w-2xl">
                        Scan an Appointment Verification Pass, a Visitor Gate Pass, a Digital
                        Claim Pass or a Digital ID. The pass details appear instantly and entry
                        is allowed only while the pass is valid.
                    </p>
                </div>

                <div class="shrink-0 rounded-2xl bg-white border border-gray-100 shadow-sm
                            px-5 py-4 text-right min-w-[10.5rem]">
                    <p class="text-2xl font-extrabold text-gray-900 tabular-nums leading-none"
                       x-text="clock">—</p>
                    <p class="text-xs text-gray-500 mt-1.5" x-text="today">Today</p>
                </div>
            </div>


            {{-- Desk counters --}}
            <div class="mb-6 grid grid-cols-3 gap-3 sm:gap-4">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-4 py-3">
                    <p class="text-2xl font-extrabold text-blue-700 leading-none tabular-nums">{{ $expectedCount }}</p>
                    <p class="text-[10px] sm:text-[11px] text-gray-500 mt-1.5 font-bold uppercase tracking-wide">
                        Expected Today
                    </p>
                </div>
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-4 py-3">
                    <p class="text-2xl font-extrabold text-slate-900 leading-none tabular-nums">{{ $todayCount }}</p>
                    <p class="text-[10px] sm:text-[11px] text-gray-500 mt-1.5 font-bold uppercase tracking-wide">
                        Checked-In Today
                    </p>
                </div>
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm px-4 py-3">
                    <p class="text-2xl font-extrabold text-emerald-600 leading-none tabular-nums">{{ $insideCount }}</p>
                    <p class="text-[10px] sm:text-[11px] text-gray-500 mt-1.5 font-bold uppercase tracking-wide">
                        Inside Now
                    </p>
                </div>
            </div>


            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">

                {{-- The camera desk --}}
                <div class="xl:col-span-2">
                    <x-gate-scanner :gate-name="$gateName" />
                </div>


                {{-- Desk guide --}}
                <section class="space-y-4">

                    <div class="rounded-2xl bg-[#12347d] text-white p-5 shadow-sm">
                        <h2 class="text-sm font-bold">Gate verification rules</h2>

                        <ul class="mt-3 space-y-3 text-xs text-blue-100">
                            <li class="flex gap-2.5">
                                <span class="mt-0.5 shrink-0 w-4 h-4 rounded-full bg-emerald-400/20
                                             text-emerald-300 flex items-center justify-center text-[10px] font-black">✓</span>
                                <span>Appointments are accepted only on their scheduled date, while approved or pending.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <span class="mt-0.5 shrink-0 w-4 h-4 rounded-full bg-emerald-400/20
                                             text-emerald-300 flex items-center justify-center text-[10px] font-black">✓</span>
                                <span>Visitor Gate Passes must be active and not past their valid-until date.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <span class="mt-0.5 shrink-0 w-4 h-4 rounded-full bg-emerald-400/20
                                             text-emerald-300 flex items-center justify-center text-[10px] font-black">✓</span>
                                <span>Claim passes open the gate only once the document is ready for pickup.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <span class="mt-0.5 shrink-0 w-4 h-4 rounded-full bg-red-400/20
                                             text-red-300 flex items-center justify-center text-[10px] font-black">✕</span>
                                <span>Cancelled, completed, no-show or expired passes are refused and never logged.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                        <h2 class="text-sm font-bold text-gray-900">Entry and exit</h2>
                        <p class="text-xs text-gray-500 mt-1.5">
                            The first valid scan of a pass today records the time in. The next
                            scan of the same pass records the time out. Every scan that verifies
                            is written to the Daily Logbook.
                        </p>

                        <div class="mt-4 flex flex-col gap-2">
                            <a href="{{ route('guard.logbook') }}"
                               class="inline-flex items-center justify-between rounded-xl bg-slate-50
                                      px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                                View today's logbook
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>

                </section>

            </div>

        </div>
    </div>

</x-app-layout>
