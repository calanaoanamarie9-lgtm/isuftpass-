<x-app-layout>

    @php
        $statusLabel =
            \App\Enums\AppointmentStatus::tryFrom($appointment->status)?->label()
            ?? ucfirst(str_replace('_', ' ', $appointment->status));
    @endphp

    <div class="min-h-screen bg-[#f5f7fa] py-8 overflow-x-hidden">

        <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                BACK
            ========================================================== --}}
            <div class="mb-5">
                <a href="{{ route('student.appointments.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-blue-800 transition">

                    <svg class="w-4 h-4"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 19l-7-7 7-7"/>
                    </svg>

                    Back to My Appointments
                </a>
            </div>


            {{-- =========================================================
                MAIN APPOINTMENT PASS
            ========================================================== --}}
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">

                {{-- =====================================================
                    BLUE HEADER
                ====================================================== --}}
                <div class="relative bg-gradient-to-r from-[#071d49] via-[#0b3478] to-[#1254a3]">

                    {{-- subtle design --}}
                    <div class="absolute right-0 top-0 w-72 h-full opacity-10">
                        <svg viewBox="0 0 300 180"
                             fill="none"
                             class="w-full h-full">

                            <path d="M20 150C80 100 130 100 290 20"
                                  stroke="white"
                                  stroke-width="7"/>

                            <path d="M60 180C120 130 180 120 300 55"
                                  stroke="white"
                                  stroke-width="4"/>
                        </svg>
                    </div>

                    <div class="relative px-6 sm:px-9 py-7">

                        {{-- Logo / Brand --}}
                        <div class="flex items-center gap-3">

                            <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0">
                                <img src="{{ asset('img/isufstpass-logo.png') }}"
                                     alt="ISUFSTPASS"
                                     class="w-full h-full object-contain">
                            </div>

                            <div>
                                <p class="text-[10px] sm:text-xs font-bold tracking-[0.18em] text-blue-200 uppercase">
                                    ILOILO STATE UNIVERSITY
                                </p>

                                <h2 class="text-lg sm:text-xl font-black text-white tracking-wide">
                                    ISUFSTPASS
                                </h2>
                            </div>

                        </div>


                        {{-- Title + Status --}}
                        <div class="mt-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">

                            <div>

                                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                                    Appointment Verification Pass
                                </h1>

                                <p class="mt-1 text-sm sm:text-base text-blue-100">
                                    Present this QR code to authorized staff for verification.
                                </p>

                            </div>


                            {{-- Status --}}
                            <div class="shrink-0">

                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full
                                    text-xs font-black uppercase tracking-wide

                                    @if ($appointment->status === 'confirmed')
                                        bg-green-400 text-green-950
                                    @elseif ($appointment->status === 'checked_in')
                                        bg-cyan-300 text-cyan-950
                                    @elseif ($appointment->status === 'rescheduled')
                                        bg-indigo-300 text-indigo-950
                                    @elseif ($appointment->status === 'completed')
                                        bg-blue-300 text-blue-950
                                    @elseif ($appointment->status === 'cancelled')
                                        bg-red-300 text-red-950
                                    @elseif ($appointment->status === 'no_show')
                                        bg-gray-300 text-gray-800
                                    @elseif ($appointment->status === 'pending')
                                        bg-red-300 text-red-950
                                    @else
                                        bg-yellow-300 text-yellow-950
                                    @endif
                                ">

                                    <span class="w-2 h-2 rounded-full bg-current"></span>

                                    {{ $statusLabel }}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                    CONTENT
                ====================================================== --}}
                <div class="p-6 sm:p-9">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">


                        {{-- =================================================
                            LEFT: APPOINTMENT INFORMATION
                        ================================================== --}}
                        <div class="lg:col-span-7">

                            <p class="text-[11px] font-black tracking-[0.18em] text-blue-700 uppercase">
                                Appointment Information
                            </p>

                            <h2 class="mt-2 text-2xl sm:text-3xl font-black text-gray-900">
                                {{ $appointment->office }}
                            </h2>

                            <p class="mt-2 text-sm text-gray-500">
                                Your appointment has been registered through ISUFSTPASS.
                            </p>


                            {{-- Information Grid --}}
                            <div class="mt-7 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">

                                {{-- Reference --}}
                                <div>
                                    <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                        Reference Number
                                    </p>

                                    <p class="mt-1 text-sm font-black text-[#102d5b] break-all">
                                        {{ $appointment->reference_code }}
                                    </p>
                                </div>


                                {{-- Date --}}
                                <div>
                                    <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                        Appointment Date
                                    </p>

                                    <p class="mt-1 text-sm font-black text-gray-900">
                                        {{ $appointment->date->format('F j, Y') }}
                                    </p>
                                </div>


                                {{-- Time --}}
                                <div>
                                    <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                        Time Slot
                                    </p>

                                    <p class="mt-1 text-sm font-black text-gray-900">
                                        {{ $appointment->time_slot }}
                                    </p>
                                </div>


                                {{-- Purpose --}}
                                <div>
                                    <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                        Concern / Purpose
                                    </p>

                                    <p class="mt-1 text-sm font-black text-gray-900">
                                        {{ $appointment->purpose }}
                                    </p>
                                </div>


                                {{-- Date Booked --}}
                                <div>
                                    <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                        Date Booked
                                    </p>

                                    <p class="mt-1 text-sm font-black text-gray-900">
                                        {{ $appointment->created_at->format('F j, Y') }}
                                    </p>
                                </div>


                                {{-- Last Rescheduled --}}
                                @if ($appointment->rescheduled_at)

                                    <div>
                                        <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                            Last Rescheduled
                                        </p>

                                        <p class="mt-1 text-sm font-black text-gray-900">
                                            {{ $appointment->rescheduled_at->format('F j, Y g:i A') }}
                                        </p>
                                    </div>

                                @endif

                            </div>


                            {{-- Notes --}}
                            @if ($appointment->notes)

                                <div class="mt-7 pt-6 border-t border-gray-100">

                                    <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                        Notes
                                    </p>

                                    <p class="mt-2 text-sm font-medium text-gray-700">
                                        {{ $appointment->notes }}
                                    </p>

                                </div>

                            @endif


                            {{-- =================================================
                                VERIFICATION INSTRUCTIONS
                            ================================================== --}}
                            <div class="mt-8 rounded-2xl bg-blue-50 border border-blue-100 p-5">

                                <div class="flex items-start gap-3">

                                    <div class="w-9 h-9 shrink-0 rounded-xl bg-blue-700
                                                flex items-center justify-center">

                                        <svg class="w-5 h-5 text-white"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>

                                        </svg>

                                    </div>


                                    <div>

                                        <h3 class="text-sm font-black text-[#102d5b]">
                                            Verification Instructions
                                        </h3>

                                        <p class="mt-1 text-xs sm:text-sm text-blue-800/80">
                                            Keep this QR code available when visiting the university office.
                                        </p>

                                        <p class="mt-1 text-xs sm:text-sm text-blue-800/80">
                                            Staff may scan this code to verify your appointment.
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                            RIGHT: QR CODE
                        ================================================== --}}
                        <div class="lg:col-span-5">

                            @if ($appointment->isUpcoming())

                                <div class="rounded-2xl border border-gray-200 bg-white
                                            p-5 sm:p-6 text-center">

                                    {{-- Yellow accent --}}
                                    <div class="mx-auto w-fit border-[7px] border-yellow-400
                                                rounded-3xl p-2">

                                        <div class="bg-white rounded-2xl border border-gray-300 p-4">

                                            <p class="text-[10px] font-black tracking-[0.2em]
                                                      text-gray-500 uppercase mb-3">
                                                Scan to Verify
                                            </p>

                                            <img src="{{ $qrCodeDataUri }}"
                                                 alt="Appointment QR Code"
                                                 class="w-52 h-52 sm:w-60 sm:h-60 object-contain mx-auto">

                                            <div class="mt-3 pt-3 border-t border-gray-100">

                                                <p class="text-[10px] font-semibold text-gray-400">
                                                    REFERENCE NUMBER
                                                </p>

                                                <p class="mt-1 text-xs font-black text-gray-800 tracking-wide break-all">
                                                    {{ $appointment->reference_code }}
                                                </p>

                                            </div>

                                        </div>

                                    </div>


                                    <p class="mt-4 text-xs text-gray-500">
                                        Present this QR code to authorized personnel.
                                    </p>


                                    {{-- Download --}}
                                    <a href="{{ route('student.appointments.qr.download', $appointment) }}"
                                       class="mt-5 w-full inline-flex items-center justify-center gap-2
                                              px-5 py-3 rounded-xl bg-[#102d5b]
                                              text-white text-sm font-bold
                                              hover:bg-[#0b2348] transition">

                                        <svg class="w-5 h-5"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M12 3v12m0 0l4-4m-4 4l-4-4M5 21h14"/>

                                        </svg>

                                        Download QR Code

                                    </a>

                                    {{-- The button above serves PNG, which a phone
                                         can actually open. Keep vector reachable
                                         for print and for scaling without loss. --}}
                                    <a href="{{ route('student.appointments.qr.download', ['appointment' => $appointment, 'format' => 'svg']) }}"
                                       class="mt-2 inline-flex items-center gap-1.5
                                              text-[11px] font-semibold text-gray-400
                                              hover:text-gray-600 transition">

                                        Vector version (SVG) for printing

                                    </a>

                                </div>

                            @else

                                <div class="rounded-2xl border border-gray-200
                                            bg-gray-50 p-8 text-center">

                                    <div class="w-14 h-14 mx-auto rounded-full bg-gray-200
                                                flex items-center justify-center">

                                        <svg class="w-7 h-7 text-gray-500"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>

                                        </svg>

                                    </div>

                                    <h3 class="mt-4 text-base font-black text-gray-800">
                                        QR Code Unavailable
                                    </h3>

                                    <p class="mt-1 text-sm text-gray-500">
                                        This appointment is no longer upcoming.
                                    </p>

                                </div>

                            @endif

                        </div>

                    </div>


                    {{-- =====================================================
                        REMINDERS
                    ====================================================== --}}
                    <div class="mt-8 pt-7 border-t border-gray-100">

                        <div class="flex items-start gap-4">

                            <div class="w-10 h-10 shrink-0 rounded-xl bg-yellow-100
                                        flex items-center justify-center">

                                <svg class="w-5 h-5 text-yellow-700"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>

                                </svg>

                            </div>


                            <div>

                                <h3 class="text-sm font-black text-[#102d5b]">
                                    REMINDERS
                                </h3>

                                <ul class="mt-3 space-y-2 text-sm text-gray-600">

                                    <li class="flex gap-2">
                                        <span class="text-blue-700 font-bold">•</span>
                                        <span>Arrive during your scheduled date and time slot.</span>
                                    </li>

                                    <li class="flex gap-2">
                                        <span class="text-blue-700 font-bold">•</span>
                                        <span>Present this QR code to the authorized personnel.</span>
                                    </li>

                                    <li class="flex gap-2">
                                        <span class="text-blue-700 font-bold">•</span>
                                        <span>This QR code is valid only for this appointment.</span>
                                    </li>

                                    <li class="flex gap-2">
                                        <span class="text-blue-700 font-bold">•</span>
                                        <span>Do not share this QR code with other people.</span>
                                    </li>

                                </ul>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                ACTIONS
            ========================================================== --}}
            @if ($appointment->isModifiableByStudent())

                <div class="mt-6 flex flex-col sm:flex-row gap-3">

                    <a href="{{ route('student.appointments.edit', $appointment) }}"
                       class="inline-flex items-center justify-center gap-2
                              px-5 py-3 rounded-xl
                              bg-[#102d5b] text-white text-sm font-bold
                              hover:bg-[#0b2348] transition">

                        <svg class="w-4 h-4"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>

                        </svg>

                        Edit Appointment

                    </a>


                    @if ($appointment->isModifiableByStudent())

                        <form method="POST"
                              action="{{ route('student.appointments.cancel', $appointment) }}">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center
                                           justify-center gap-2 px-5 py-3 rounded-xl
                                           bg-white border border-red-200
                                           text-red-600 text-sm font-bold
                                           hover:bg-red-50 transition">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"/>

                                </svg>

                                Cancel Appointment

                            </button>

                        </form>

                    @endif

                </div>

            @endif


            {{-- =========================================================
                FOOTER NOTICE
            ========================================================== --}}
            <div class="mt-6 px-5 py-4 rounded-2xl
                        bg-blue-50 border border-blue-100">

                <div class="flex items-start gap-3">

                    <svg class="w-5 h-5 text-blue-700 shrink-0 mt-0.5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 15v2m0-8v4m0-9a9 9 0 100 18 9 9 0 000-18z"/>

                    </svg>

                    <div>

                        <p class="text-sm font-bold text-blue-900">
                            Secure Appointment Verification
                        </p>

                        <p class="mt-1 text-xs sm:text-sm text-blue-700">
                            This QR code is generated by the ISUFSTPASS Digital Campus Transaction System
                            and is intended only for authorized appointment verification.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>