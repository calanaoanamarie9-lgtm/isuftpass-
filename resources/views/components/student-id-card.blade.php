@props([
    'student',
    'profile',
    'avatar' => null,
])

<div class="max-w-sm mx-auto relative overflow-hidden rounded-2xl shadow-lg
            bg-gradient-to-br from-[#071f67] via-[#06348d] to-[#0647aa]">

    {{-- Decorative background --}}
    <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-blue-400/10"></div>

    <div class="absolute -left-14 bottom-14 w-44 h-44 rounded-full bg-blue-300/10 blur-2xl"></div>

    <div class="absolute right-0 top-0 opacity-10">
        <svg width="180" height="160" viewBox="0 0 340 300" fill="none">
            <path d="M10 230C90 170 150 170 330 30"
                  stroke="white"
                  stroke-width="8"/>
            <path d="M10 260C100 200 170 200 330 70"
                  stroke="white"
                  stroke-width="5"/>
            <path d="M80 250V150M150 220V110M220 180V80"
                  stroke="white"
                  stroke-width="8"/>
        </svg>
    </div>


    {{-- ================= CARD HEADER ================= --}}
    <div class="relative px-4 pt-4 pb-3">

        <div class="flex items-center gap-2.5">

            <div class="w-11 h-11 shrink-0">
                <img src="{{ asset('img/isufstpass_circle_transparent.png') }}"
                     alt="ISUFSTPASS"
                     class="w-full h-full object-contain">
            </div>

            <div class="flex-1 min-w-0">
                <h2 class="text-lg font-black text-white tracking-tight leading-none">
                    ISUFSTPASS
                </h2>

                <p class="text-blue-100 text-[11px] font-semibold mt-0.5">
                    Digital ID & Access Portal
                </p>
            </div>

            @isset($label)

                <span class="shrink-0 max-w-[90px] rounded-full bg-yellow-400 px-2 py-1
                            text-[7px] leading-tight font-black tracking-widest uppercase
                            text-blue-900 text-center">
                    {{ $label }}
                </span>

            @endisset

        </div>

    </div>


    {{-- Yellow divider --}}
    <div class="h-1 bg-yellow-400"></div>


    {{-- ================= STUDENT INFORMATION ================= --}}
    <div class="relative px-4 py-4">

        <div class="flex gap-3 items-start">

            {{-- Student photo --}}
            <div class="w-20 h-24 shrink-0 rounded-xl overflow-hidden
                        border-2 border-white/80 bg-blue-900 shadow">

                @if ($avatar)

                    <img src="{{ $avatar }}"
                         alt="{{ $student->name }}"
                         class="w-full h-full object-cover">

                @else

                    <div class="w-full h-full flex items-center justify-center
                                text-3xl font-black text-yellow-400">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>

                @endif

            </div>


            {{-- Student details --}}
            <div class="text-white flex-1 min-w-0">

                <p class="text-sm font-black leading-tight">
                    {{ $student->name }}
                </p>

                <p class="mt-1 text-yellow-400 font-black text-xs">
                    {{ $profile?->student_id ?? 'STUDENT ID' }}
                </p>

                <p class="text-blue-200 text-[10px]">
                    Student ID
                </p>


                <div class="mt-2.5 space-y-1.5">

                    {{-- Course --}}
                    <div class="flex items-center gap-1.5">

                        <div class="w-5 h-5 rounded bg-yellow-400/15
                                    flex items-center justify-center shrink-0">

                            <svg class="w-3.5 h-3.5 text-yellow-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M5 12v5c3 2 7 3 7 3s4-1 7-3v-5"/>
                            </svg>

                        </div>

                        <div class="min-w-0">

                            <p class="font-bold text-[11px] truncate">
                                {{ $profile?->course ?? 'No Course Set' }}
                            </p>

                        </div>

                    </div>


                    {{-- Year --}}
                    <div class="flex items-center gap-1.5">

                        <div class="w-5 h-5 rounded bg-yellow-400/15
                                    flex items-center justify-center shrink-0">

                            <svg class="w-3.5 h-3.5 text-yellow-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                            </svg>

                        </div>

                        <div>

                            <p class="font-bold text-[11px]">
                                {{ $profile?->year_level ?? '—' }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ================= TRANSACTION QRS (SLOT) ================= --}}
        <div class="mt-3">
            {{ $slot }}
        </div>

    </div>


    {{-- ================= CARD FOOTER ================= --}}
    <div class="relative bg-yellow-400 px-4 py-2.5">

        <div class="flex items-center gap-2">

            <div class="w-6 h-6 rounded-full bg-blue-900
                        flex items-center justify-center shrink-0">

                <svg class="w-4 h-4 text-yellow-400"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="3"
                          d="M5 13l4 4L19 7"/>
                </svg>

            </div>

            <div>

                <p class="font-black text-blue-900 text-xs leading-none">
                    ISUFSTPASS VERIFIED
                </p>

                <p class="text-[9px] font-semibold text-blue-800 mt-0.5">
                    Secure • Reliable • Official
                </p>

            </div>

        </div>

    </div>

</div>
