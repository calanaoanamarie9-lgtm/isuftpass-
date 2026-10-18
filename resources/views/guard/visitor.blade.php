<x-app-layout>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100
                            px-5 py-4 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif


            <div class="mb-6 flex items-center justify-between gap-3">
                <a href="{{ route('guard.visitors.index') }}"
                   class="text-sm font-bold text-blue-700 hover:text-blue-900">
                    ← All visitor passes
                </a>

                <a href="{{ route('guard.dashboard') }}"
                   class="text-sm font-bold text-gray-500 hover:text-gray-800">
                    Scanner
                </a>
            </div>


            {{-- THE PASS --}}
            <div class="rounded-3xl bg-slate-900 text-white shadow-xl overflow-hidden">

                <div class="px-6 py-4 bg-slate-950 flex items-center
                            justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('img/isufstpass_circle_transparent.png') }}"
                             alt="ISUFSTPASS"
                             class="w-9 h-9 rounded-lg bg-white p-0.5">
                        <div>
                            <p class="text-sm font-extrabold leading-none">
                                ISUFSTPASS
                            </p>
                            <p class="text-[10px] uppercase tracking-widest
                                      text-amber-300 mt-1">
                                Visitor Gate Pass
                            </p>
                        </div>
                    </div>

                    @php
                        $tone = $visitor->isValid()
                            ? 'bg-emerald-400/15 text-emerald-300 ring-emerald-400/30'
                            : 'bg-red-400/15 text-red-300 ring-red-400/30';
                    @endphp

                    <span class="rounded-full px-3 py-1 text-[10px] font-bold
                                 uppercase tracking-widest ring-1 {{ $tone }}">
                        {{ $visitor->statusLabel() }}
                    </span>
                </div>


                <div class="p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-3 gap-6">

                    <div class="flex flex-col items-center gap-4 sm:col-span-1">

                        <div class="w-32 h-32 rounded-2xl overflow-hidden
                                    bg-white/10 ring-1 ring-white/15
                                    flex items-center justify-center">
                            @if ($visitor->photoUrl())
                                <img src="{{ $visitor->photoUrl() }}"
                                     alt="Visitor photo"
                                     class="w-full h-full object-cover">
                            @else
                                <span class="text-4xl font-black text-white/80">
                                    {{ strtoupper(substr($visitor->name, 0, 1)) }}
                                </span>
                            @endif
                        </div>

                        <div class="rounded-2xl bg-white p-3">
                            <img src="{{ $qrDataUri }}"
                                 alt="Visitor Gate Pass QR"
                                 class="w-40 h-40">
                        </div>

                        <p class="text-[10px] text-white/50 text-center">
                            Scan at the gate for entry and exit
                        </p>

                    </div>


                    <div class="sm:col-span-2 space-y-4">

                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-white/50">
                                Visitor
                            </p>
                            <p class="text-2xl font-extrabold leading-tight">
                                {{ $visitor->name }}
                            </p>
                            <p class="text-sm text-amber-300 font-bold mt-1">
                                {{ $visitor->reference_code }}
                            </p>
                        </div>


                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt class="text-[10px] uppercase tracking-widest text-white/50">
                                    ID presented
                                </dt>
                                <dd class="font-bold">{{ $visitor->id_number ?: '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-[10px] uppercase tracking-widest text-white/50">
                                    Contact
                                </dt>
                                <dd class="font-bold">{{ $visitor->contact_number ?: '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-[10px] uppercase tracking-widest text-white/50">
                                    Destination
                                </dt>
                                <dd class="font-bold">{{ $visitor->destination_office }}</dd>
                            </div>

                            <div>
                                <dt class="text-[10px] uppercase tracking-widest text-white/50">
                                    Person to visit
                                </dt>
                                <dd class="font-bold">{{ $visitor->host_name ?: '—' }}</dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-[10px] uppercase tracking-widest text-white/50">
                                    Purpose
                                </dt>
                                <dd class="font-bold">{{ $visitor->purpose }}</dd>
                            </div>

                            <div>
                                <dt class="text-[10px] uppercase tracking-widest text-white/50">
                                    Valid until
                                </dt>
                                <dd class="font-bold">
                                    {{ $visitor->valid_until?->format('F j, Y') ?? 'No expiry' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-[10px] uppercase tracking-widest text-white/50">
                                    Issued by
                                </dt>
                                <dd class="font-bold">
                                    {{ $visitor->creator?->name ?? $gateName }}
                                </dd>
                            </div>
                        </dl>

                    </div>

                </div>

            </div>


            {{-- ACTIONS --}}
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">

                <button type="button" onclick="window.print()"
                        class="rounded-xl bg-white border border-gray-200 px-5 py-2.5
                               text-sm font-bold text-gray-700 hover:bg-gray-50
                               transition">
                    Print pass
                </button>

                @if ($visitor->isValid())
                    <form method="POST"
                          action="{{ route('guard.visitors.revoke', $visitor) }}"
                          onsubmit="return confirm('Revoke this pass?');">
                        @csrf
                        <button type="submit"
                                class="rounded-xl bg-red-600 px-5 py-2.5 text-sm
                                       font-bold text-white hover:bg-red-700 transition">
                            Revoke pass
                        </button>
                    </form>
                @endif

            </div>

        </div>
    </div>

</x-app-layout>
