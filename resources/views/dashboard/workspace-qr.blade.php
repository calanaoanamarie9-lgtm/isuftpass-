@php
    /**
     * This page is written once but served from nine route names
     * (cici.qr.index, osas.qr.index, …). Its check-in endpoint is declared
     * beside each scanner route in $workspaceRoutes, so swapping the suffix
     * resolves the arrival URL for whichever desk is looking at it.
     *
     * This view owns the <x-app-layout> tag — the nine office wrappers
     * around it are plain @includes, so the page renders one sidebar, not two.
     */
    $checkInUrl = route(preg_replace('/\.qr\.index$/', '.qr.check-in', (string) request()->route()?->getName()));

    $appointmentIsOurs = $appointment && $appointment->office === $office;
    $canCheckIn = $appointment
        && \App\Support\WorkspaceQrVerification::canCheckIn($appointment, $office);
    $hasResult = $student || $appointment || $claimSlip;
@endphp

<x-app-layout>

    <style>
        /*
         * The camera feed fills the viewfinder frame whatever the video's
         * intrinsic size, and the library's own dashboard chrome stays out
         * of it.
         *
         * position and inset carry !important because html5-qrcode forces an
         * inline "position: relative" on whatever element it binds to: an
         * inline style beats a plain declaration, so without it the container
         * collapses to the height of its hidden canvas and the feed renders
         * at zero height — a black viewfinder on a working camera.
         */
        #qr-reader {
            position: absolute !important;
            inset: 0 !important;
            background: #020617;
        }

        #qr-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            position: absolute;
            inset: 0;
        }

        #qr-reader__dashboard,
        #qr-reader__status_span {
            display: none !important;
        }

        /* The sweep line that runs across the frame while scanning. */
        @keyframes qr-sweep {
            0%   { transform: translateY(0);      opacity: 0; }
            12%  { opacity: 1; }
            88%  { opacity: 1; }
            100% { transform: translateY(1000%);  opacity: 0; }
        }
    </style>

    <div class="min-h-screen bg-[#f4f7fb] py-8">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="mb-8">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                    <div>
                        <div class="flex items-center gap-3">

                            <div class="w-12 h-12 rounded-2xl bg-blue-900
                                        flex items-center justify-center
                                        shadow-lg shadow-blue-900/20">

                                <svg class="w-6 h-6 text-yellow-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 4v1m6.364 1.636l-.707.707M20 12h-1M18.364 18.364l-.707-.707M12 19v1M6.343 17.657l-.707.707M5 12H4m2.343-5.657l-.707-.707M9 12a3 3 0 106 0 3 3 0 00-6 0z"/>

                                </svg>

                            </div>

                            <div>
                                <h1 class="text-2xl sm:text-3xl font-extrabold text-blue-950">
                                    QR Pass Verification
                                </h1>

                                <p class="text-sm text-gray-500 mt-1">
                                    Verify student identity, document requests, and appointments.
                                </p>
                            </div>

                        </div>

                        {{-- Which desk is answering --}}
                        <div class="mt-4 inline-flex items-center gap-2
                                    rounded-full bg-blue-50 border border-blue-100
                                    px-3.5 py-1.5">

                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>

                            <span class="text-xs font-bold uppercase tracking-wide text-blue-700">
                                {{ $office }} Desk
                            </span>

                        </div>
                    </div>


                    {{-- VERIFIED BADGE --}}
                    <div class="inline-flex items-center gap-2
                                px-4 py-2 rounded-full
                                bg-white border border-blue-100
                                shadow-sm self-start sm:self-center">

                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>

                        <span class="text-xs font-bold text-gray-600">
                            ISUFSTPASS SECURE VERIFICATION
                        </span>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                QR VERIFICATION CARD
            ========================================================== --}}
            <div class="relative overflow-hidden
                        bg-gradient-to-br from-blue-950 via-blue-900 to-indigo-950
                        rounded-3xl shadow-xl shadow-blue-950/20
                        mb-7">

                {{-- Decorative circles --}}
                <div class="absolute -top-20 -right-20 w-64 h-64
                            rounded-full bg-blue-800/30"></div>

                <div class="absolute -bottom-24 -left-20 w-72 h-72
                            rounded-full bg-indigo-800/20"></div>

                <div class="relative p-6 sm:p-8">

                    <div class="flex items-start gap-4 mb-6">

                        <div class="w-12 h-12 rounded-2xl
                                    bg-yellow-400
                                    flex items-center justify-center
                                    shrink-0 shadow-lg">

                            <svg class="w-6 h-6 text-blue-950"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 4a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM15 4a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V4zM3 16a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1v-4zM16 15h1m-1 4h1m3-4h1m-1 4h1M12 12h.01"/>

                            </svg>

                        </div>

                        <div>

                            <h2 class="text-lg font-extrabold text-white">
                                Verify QR Pass
                            </h2>

                            <p class="text-sm text-blue-200 mt-1">
                                Scan a student's QR code or enter their identifier below.
                            </p>

                        </div>

                    </div>


                    <form method="GET"
                          action="{{ request()->url() }}"
                          id="qr-form">

                        <div class="flex flex-col sm:flex-row gap-3">

                            <div class="relative flex-1">

                                <svg class="absolute left-4 top-1/2
                                            -translate-y-1/2
                                            w-5 h-5 text-gray-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>

                                </svg>

                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $query }}"
                                    id="qr-input"
                                    placeholder="Scan QR code, paste QR link, name, or email"
                                    autocomplete="off"
                                    autofocus
                                    class="w-full pl-12 pr-4 py-3.5
                                           rounded-2xl
                                           border-0
                                           bg-white
                                           text-sm text-gray-800
                                           shadow-lg
                                           focus:ring-4
                                           focus:ring-yellow-400/30
                                           focus:outline-none"
                                >

                            </div>

                            <button type="submit"
                                    class="inline-flex items-center justify-center
                                           gap-2 px-7 py-3.5
                                           rounded-2xl
                                           bg-yellow-400
                                           text-blue-950
                                           text-sm font-extrabold
                                           shadow-lg
                                           hover:bg-yellow-300
                                           transition">

                                <svg class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 5l7 7-7 7"/>

                                </svg>

                                Verify

                            </button>

                        </div>


                        {{-- CAMERA BUTTON --}}
                        <button type="button"
                                onclick="startQrCamera()"
                                class="mt-4 w-full
                                       inline-flex items-center
                                       justify-center gap-2
                                       px-5 py-3
                                       rounded-2xl
                                       border border-white/20
                                       bg-white/10
                                       backdrop-blur-sm
                                       text-white
                                       text-sm font-bold
                                       hover:bg-white/20
                                       transition">

                            <svg class="w-5 h-5 text-yellow-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 7h4l2-3h6l2 3h4v12H3V7z"/>

                                <circle cx="12"
                                        cy="13"
                                        r="3"
                                        stroke-width="2"/>

                            </svg>

                            Scan QR Code with Camera

                        </button>


                        {{-- QR CAMERA --}}
                        <div id="qr-camera"
                             class="hidden mt-4">

                            <div class="relative aspect-[4/3] w-full
                                        overflow-hidden rounded-2xl
                                        border border-white/20
                                        bg-slate-950">

                                <div id="qr-reader" class="hidden"></div>

                                {{-- Corner brackets --}}
                                <div class="pointer-events-none absolute inset-0 z-10">

                                    <span class="absolute left-4 top-4 h-9 w-9
                                                 border-l-[3px] border-t-[3px]
                                                 border-emerald-400 rounded-tl-lg"></span>

                                    <span class="absolute right-4 top-4 h-9 w-9
                                                 border-r-[3px] border-t-[3px]
                                                 border-emerald-400 rounded-tr-lg"></span>

                                    <span class="absolute left-4 bottom-4 h-9 w-9
                                                 border-l-[3px] border-b-[3px]
                                                 border-emerald-400 rounded-bl-lg"></span>

                                    <span class="absolute right-4 bottom-4 h-9 w-9
                                                 border-r-[3px] border-b-[3px]
                                                 border-emerald-400 rounded-br-lg"></span>

                                    <div class="absolute left-[18%] right-[18%]
                                                top-[12%] h-[3px] rounded-full
                                                bg-emerald-400/90
                                                shadow-[0_0_14px_3px_rgba(52,211,153,0.55)]"
                                         style="animation: qr-sweep 2.6s ease-in-out infinite;">
                                    </div>

                                </div>

                            </div>


                            <div class="mt-3 flex flex-wrap items-center
                                        justify-between gap-3">

                                <span class="inline-flex items-center gap-2
                                             rounded-full bg-emerald-500/15
                                             border border-emerald-400/30
                                             px-3 py-1.5 text-xs font-bold
                                             text-emerald-200">

                                    <span class="w-1.5 h-1.5 rounded-full
                                                 bg-emerald-400 animate-pulse"></span>

                                    Scanning — hold the pass inside the frame

                                </span>

                                <button type="button"
                                        onclick="stopQrCamera()"
                                        class="rounded-xl border border-white/20
                                               bg-white/10 px-4 py-2
                                               text-xs font-bold text-white
                                               hover:bg-white/20 transition">

                                    Stop camera

                                </button>

                            </div>

                        </div>


                        <div class="flex items-start gap-2 mt-4">

                            <svg class="w-4 h-4 text-blue-300 mt-0.5 shrink-0"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-8h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>

                            </svg>

                            <p class="text-xs text-blue-200 leading-relaxed">
                                USB QR scanners are also supported. Keep the input field focused
                                while scanning a student pass, claim slip, or appointment QR.
                            </p>

                        </div>

                    </form>

                </div>

            </div>


            {{-- =========================================================
                NOTHING MATCHED
            ========================================================== --}}
            @if ($query !== '' && ! $hasResult)

                <div class="bg-white rounded-3xl border border-red-100
                            shadow-sm p-10 text-center mb-6">

                    <div class="mx-auto w-16 h-16 rounded-2xl bg-red-50
                                flex items-center justify-center">

                        <svg class="w-8 h-8 text-red-500"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>

                        </svg>

                    </div>

                    <h2 class="mt-5 text-lg font-extrabold text-gray-900">
                        No Student Found
                    </h2>

                    <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
                        The QR code or identifier does not match any registered
                        student, appointment, or claim slip.
                    </p>

                    <p class="mt-4 text-xs text-gray-400">
                        Check the pass, or search using the student's name, email,
                        or student number.
                    </p>

                </div>

            @endif


            {{-- =========================================================
                VERIFIED STUDENT
            ========================================================== --}}
            @if ($student)

                <div class="bg-white rounded-3xl border border-green-100
                            shadow-sm overflow-hidden mb-6">

                    <div class="px-6 py-5 bg-gradient-to-r
                                from-green-50 to-emerald-50
                                border-b border-green-100">

                        <div class="flex flex-wrap items-center
                                    justify-between gap-3">

                            <div class="flex items-center gap-3">

                                <div class="w-11 h-11 rounded-xl bg-green-600
                                            flex items-center justify-center">

                                    <svg class="w-6 h-6 text-white"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M5 13l4 4L19 7"/>

                                    </svg>

                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase
                                              tracking-widest text-green-600">
                                        Identity Verification
                                    </p>

                                    <h2 class="text-lg font-extrabold text-green-900">
                                        Verified Student
                                    </h2>
                                </div>

                            </div>

                            <span class="inline-flex items-center gap-2
                                         px-3.5 py-1.5 rounded-full
                                         bg-green-50 text-green-700
                                         text-xs font-extrabold
                                         ring-1 ring-green-200">

                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>

                                Verified
                            </span>

                        </div>

                    </div>


                    <div class="p-6">

                        <div class="flex flex-col sm:flex-row sm:items-center gap-5">

                            <div class="relative shrink-0">

                                <div class="w-20 h-20 rounded-2xl bg-blue-950
                                            overflow-hidden flex items-center
                                            justify-center text-yellow-400
                                            font-extrabold text-2xl shadow-lg">

                                    @if ($student->studentProfile?->avatar)
                                        <img src="{{ $student->studentProfile->avatar_url }}"
                                             alt="Avatar"
                                             class="w-full h-full object-cover">
                                    @else
                                        {{ substr($student->name, 0, 1) }}
                                    @endif

                                </div>

                                <div class="absolute -right-2 -bottom-2
                                            w-7 h-7 rounded-full bg-green-500
                                            border-4 border-white
                                            flex items-center justify-center">

                                    <svg class="w-3.5 h-3.5 text-white"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="3"
                                              d="M5 13l4 4L19 7"/>

                                    </svg>

                                </div>

                            </div>


                            <div class="flex-1 min-w-0">

                                <h2 class="text-xl font-extrabold text-blue-950">
                                    {{ $student->name }}
                                </h2>

                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $student->email }}
                                </p>

                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $student->studentProfile?->course ?: 'No course set' }}

                                    @if ($student->studentProfile?->year_level)
                                        &middot; {{ $student->studentProfile->year_level }}
                                    @endif

                                    @if ($student->studentProfile?->student_id)
                                        &middot; {{ $student->studentProfile->student_id }}
                                    @endif
                                </p>

                            </div>

                        </div>


                        {{-- This office's history with the student --}}
                        <div class="mt-6">

                            <h3 class="text-xs font-extrabold uppercase
                                       tracking-widest text-gray-400 mb-3">
                                Appointments with {{ $office }}
                            </h3>

                            <div class="space-y-2">

                                @forelse ($officeAppointments as $visit)

                                    <div class="flex flex-wrap items-center
                                                justify-between gap-3
                                                rounded-2xl border border-gray-100
                                                bg-white px-4 py-3.5">

                                        <div class="flex items-center gap-3 min-w-0">

                                            <div class="w-9 h-9 rounded-xl bg-cyan-50
                                                        flex items-center justify-center
                                                        shrink-0">

                                                <svg class="w-4 h-4 text-cyan-700"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                                </svg>

                                            </div>

                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-gray-800 truncate">
                                                    {{ $visit->date->format('M j, Y') }} &middot; {{ $visit->timeToCome() }}
                                                </p>

                                                <p class="text-xs text-gray-500 truncate">
                                                    {{ $visit->purpose }}
                                                </p>
                                            </div>

                                        </div>

                                        <span class="shrink-0 px-2.5 py-1
                                                     rounded-full bg-cyan-50
                                                     text-cyan-700 text-[10px]
                                                     font-extrabold">
                                            {{ $visit->statusLabel() }}
                                        </span>

                                    </div>

                                @empty

                                    <p class="text-sm text-gray-400 py-2">
                                        No appointments with {{ $office }} on record.
                                    </p>

                                @endforelse

                            </div>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                APPOINTMENT
            ========================================================== --}}
            @if ($appointment)

                <div class="bg-white rounded-3xl border shadow-sm
                            overflow-hidden mb-6
                            {{ $appointmentIsOurs ? 'border-cyan-100' : 'border-amber-200' }}">

                    <div class="px-6 py-5 border-b
                                {{ $appointmentIsOurs
                                    ? 'bg-gradient-to-r from-cyan-50 to-blue-50 border-cyan-100'
                                    : 'bg-gradient-to-r from-amber-50 to-orange-50 border-amber-200' }}">

                        <div class="flex flex-wrap items-center justify-between gap-4">

                            <div class="flex items-center gap-3">

                                <div class="w-11 h-11 rounded-xl flex items-center
                                            justify-center
                                            {{ $appointmentIsOurs ? 'bg-cyan-700' : 'bg-amber-600' }}">

                                    <svg class="w-5 h-5 text-white"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                    </svg>

                                </div>

                                <div>

                                    <p class="text-xs font-bold uppercase
                                              tracking-widest
                                              {{ $appointmentIsOurs ? 'text-cyan-600' : 'text-amber-600' }}">
                                        Schedule Verification
                                    </p>

                                    <h2 class="text-lg font-extrabold text-blue-950">
                                        Appointment Found
                                    </h2>

                                    @if ($appointment->user)
                                        <p class="text-sm font-bold text-blue-950/70">
                                            {{ $appointment->user->name }}
                                        </p>
                                    @endif

                                </div>

                            </div>

                            <span class="inline-flex items-center
                                         px-3.5 py-1.5 rounded-full
                                         text-xs font-extrabold
                                @if ($appointment->status === 'confirmed')
                                    bg-green-50 text-green-700 ring-1 ring-green-200
                                @elseif ($appointment->status === 'checked_in')
                                    bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200
                                @elseif ($appointment->status === 'rescheduled')
                                    bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200
                                @else
                                    bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200
                                @endif">

                                {{ $appointment->statusLabel() }}

                            </span>

                        </div>

                    </div>


                    <div class="p-6">

                        @unless ($appointmentIsOurs)

                            <div class="mb-5 rounded-2xl border border-amber-200
                                        bg-amber-50 px-4 py-3.5">

                                <p class="text-xs font-bold text-amber-800">
                                    Not this desk's booking
                                </p>

                                <p class="mt-1 text-[11px] leading-relaxed text-amber-700">
                                    This visit is booked with {{ $appointment->office }}.
                                    {{ $office }} cannot check it in — send the student to
                                    {{ $appointment->office }}.
                                </p>

                            </div>

                        @endunless


                        <x-appointment-pass-card :appointment="$appointment" />


                        @if ($appointmentIsOurs && $appointment->status === 'checked_in')

                            <div class="mt-5 flex items-center gap-3
                                        rounded-2xl border border-cyan-100
                                        bg-cyan-50 px-4 py-3.5">

                                <span class="w-9 h-9 rounded-full bg-white
                                             flex items-center justify-center
                                             text-cyan-700 font-black shrink-0">
                                    ✓
                                </span>

                                <div>
                                    <p class="text-xs font-bold text-cyan-800">
                                        Arrival recorded
                                    </p>

                                    <p class="text-[11px] text-cyan-700 mt-0.5">
                                        Checked in at {{ $appointment->checked_in_at?->format('g:i A') ?? 'an earlier scan' }}.
                                    </p>
                                </div>

                            </div>

                        @elseif ($canCheckIn)

                            <button type="button"
                                    onclick="recordArrival(this, @js($appointment->qr_token))"
                                    class="mt-5 w-full inline-flex items-center
                                           justify-center gap-2 px-5 py-3.5
                                           rounded-2xl bg-blue-900 text-white
                                           text-sm font-extrabold
                                           hover:bg-blue-950 transition
                                           disabled:opacity-60
                                           disabled:cursor-not-allowed">

                                <svg class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M5 13l4 4L19 7"/>

                                </svg>

                                Record Arrival at {{ $office }}

                            </button>

                        @elseif ($appointmentIsOurs)

                            <p class="mt-5 text-xs text-gray-500">
                                This visit cannot be checked in from this desk —
                                it is {{ $appointment->statusLabel() }}
                                @if (! $appointment->date->isToday())
                                    and scheduled for {{ $appointment->date->format('M j, Y') }}
                                @endif
                                .
                            </p>

                        @endif


                        <p id="arrival-message"
                           class="hidden mt-4 rounded-2xl px-4 py-3
                                  text-xs font-bold"></p>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                DOCUMENT CLAIM SLIP
            ========================================================== --}}
            @if ($claimSlip)

                <div class="bg-white rounded-3xl border border-indigo-100
                            shadow-sm overflow-hidden mb-6">

                    <div class="px-6 py-5 bg-gradient-to-r
                                from-indigo-50 to-blue-50
                                border-b border-indigo-100">

                        <div class="flex flex-wrap items-center
                                    justify-between gap-3">

                            <div class="flex items-center gap-3">

                                <div class="w-11 h-11 rounded-xl bg-blue-900
                                            flex items-center justify-center">

                                    <svg class="w-5 h-5 text-yellow-400"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>

                                    </svg>

                                </div>

                                <div>

                                    <p class="text-xs font-bold uppercase
                                              tracking-widest text-indigo-500">
                                        Digital Claim Pass
                                    </p>

                                    <h2 class="text-lg font-extrabold text-blue-950">
                                        Document Request Found
                                    </h2>

                                </div>

                            </div>

                            <span class="inline-flex items-center px-3.5 py-1.5
                                         rounded-full text-xs font-extrabold

                                @if ($claimSlip->status === 'cancelled')
                                    bg-red-50 text-red-600 ring-1 ring-red-200
                                @elseif ($claimSlip->status === 'ready_for_pickup')
                                    bg-green-50 text-green-700 ring-1 ring-green-200
                                @else
                                    bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200
                                @endif">

                                {{ \App\Enums\DocumentRequestStatus::tryFrom($claimSlip->status)?->label() }}
                            </span>

                        </div>

                    </div>


                    <div class="p-6">

                        <div class="grid sm:grid-cols-2 gap-4">

                            <div class="rounded-2xl bg-[#f4f7fb]
                                        border border-gray-100 p-4">

                                <p class="text-[11px] font-bold uppercase
                                          tracking-widest text-gray-400">
                                    Reference Number
                                </p>

                                <p class="mt-1 text-sm font-extrabold text-blue-950">
                                    {{ $claimSlip->request_number }}
                                </p>

                            </div>

                            <div class="rounded-2xl bg-[#f4f7fb]
                                        border border-gray-100 p-4">

                                <p class="text-[11px] font-bold uppercase
                                          tracking-widest text-gray-400">
                                    Student
                                </p>

                                <p class="mt-1 text-sm font-extrabold text-blue-950">
                                    {{ $claimSlip->student_name }}
                                </p>

                            </div>

                        </div>


                        @if ($claimSlip->documents->isNotEmpty())

                            <div class="mt-5 flex flex-wrap gap-2">

                                @foreach ($claimSlip->documents as $document)

                                    <span class="inline-flex items-center gap-2
                                                 px-3.5 py-2 rounded-xl
                                                 bg-blue-50 border border-blue-100
                                                 text-xs font-bold text-blue-800">

                                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>

                                        {{ $document->name }}

                                    </span>

                                @endforeach

                            </div>

                        @endif


                        <div class="mt-5 rounded-2xl border border-amber-200
                                    bg-amber-50 px-4 py-3.5">

                            <p class="text-xs font-bold text-amber-800">
                                Released by the Registrar
                            </p>

                            <p class="mt-1 text-[11px] leading-relaxed text-amber-700">
                                Claim slips are honoured at the Registrar's Office, not at
                                {{ $office }}. This scan only confirms the request above.
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                TODAY'S ARRIVALS
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-100
                        shadow-sm overflow-hidden">

                <div class="px-6 py-5 border-b border-gray-100
                            flex flex-wrap items-center justify-between gap-3">

                    <div>
                        <h2 class="font-bold text-gray-900">Checked in today</h2>

                        <p class="text-xs text-gray-500 mt-0.5">
                            Arrivals recorded at {{ $office }}
                        </p>
                    </div>

                    <span class="shrink-0 rounded-full bg-emerald-50
                                 px-3 py-1 text-xs font-bold text-emerald-700
                                 ring-1 ring-emerald-200">
                        {{ count($checkIns) }}
                        {{ \Illuminate\Support\Str::plural('student', count($checkIns)) }}
                    </span>

                </div>


                <div class="divide-y divide-gray-50 max-h-[24rem] overflow-y-auto">

                    @forelse ($checkIns as $visit)

                        <div class="px-6 py-4 flex items-center
                                    justify-between gap-3">

                            <div class="flex items-center gap-3 min-w-0">

                                <span class="shrink-0 w-9 h-9 rounded-full
                                             bg-blue-50 text-blue-700
                                             flex items-center justify-center
                                             text-sm font-extrabold uppercase">
                                    {{ substr($visit['student'], 0, 1) }}
                                </span>

                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $visit['student'] }}
                                    </p>

                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $visit['slot'] }}
                                    </p>
                                </div>

                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-50
                                         px-2.5 py-1 text-xs font-bold
                                         text-emerald-700 ring-1 ring-emerald-200">
                                {{ $visit['time'] }}
                            </span>

                        </div>

                    @empty

                        <div class="px-6 py-10 text-center">

                            <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100
                                        flex items-center justify-center mb-3">

                                <svg class="w-6 h-6 text-slate-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>

                                </svg>

                            </div>

                            <p class="text-sm font-semibold text-gray-600">
                                No check-ins yet today
                            </p>

                            <p class="text-xs text-gray-400 mt-1">
                                Every arrival recorded here appears in this list.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        let qrScanner = null;

        const checkInUrl = @js($checkInUrl);

        function startQrCamera() {
            const wrap = document.getElementById('qr-camera');
            const box = document.getElementById('qr-reader');

            if (typeof Html5Qrcode === 'undefined') {
                alert('Camera scanner could not load. Check your internet connection or use a USB scanner.');
                return;
            }

            wrap.classList.remove('hidden');
            box.classList.remove('hidden');

            if (qrScanner) return;

            qrScanner = new Html5Qrcode('qr-reader');

            qrScanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 230, height: 230 } },
                (decoded) => {
                    stopQrCamera();

                    const input = document.getElementById('qr-input');
                    input.value = decoded.trim();
                    input.closest('form').submit();
                },
                () => {}
            ).catch((error) => {
                box.classList.add('hidden');
                alert('Unable to access the camera: ' + error);
            });
        }

        function stopQrCamera() {
            if (!qrScanner) return;

            qrScanner.stop()
                .then(() => {
                    document.getElementById('qr-reader').classList.add('hidden');
                    document.getElementById('qr-camera').classList.add('hidden');

                    qrScanner.clear();
                    qrScanner = null;
                })
                .catch(() => {});
        }

        /**
         * Recording an arrival is a write, so it is the desk's own POST — the
         * verification above only answers questions. A successful check-in
         * reloads the query, which re-reads the appointment (now Checked In)
         * and today's arrivals from the server.
         */
        async function recordArrival(button, token) {
            const message = document.getElementById('arrival-message');

            button.disabled = true;
            button.textContent = 'Recording arrival…';

            try {
                const response = await fetch(checkInUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ token: token }),
                });

                const data = await response.json();

                if (data.valid && data.checkedIn) {
                    window.location.reload();
                    return;
                }

                message.textContent = data.message || 'This visit could not be checked in.';
                message.className = 'mt-4 rounded-2xl px-4 py-3 text-xs font-bold '
                    + (data.valid
                        ? 'bg-amber-50 text-amber-800 border border-amber-200'
                        : 'bg-red-50 text-red-700 border border-red-200');
            } catch (error) {
                message.textContent = 'Could not reach the server. Check the connection and try again.';
                message.className = 'mt-4 rounded-2xl px-4 py-3 text-xs font-bold bg-red-50 text-red-700 border border-red-200';
            }

            button.disabled = false;
            button.innerHTML = 'Record Arrival at ' + @js($office);
        }

        window.addEventListener('beforeunload', stopQrCamera);
    </script>

</x-app-layout>
