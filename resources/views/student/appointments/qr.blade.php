<x-app-layout>

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


            {{--
                THE PASS
                The card itself is the shared x-appointment-pass-card — the
                same component the scanned page and the registrar's station
                render — so this view only adds what belongs to the student's
                own copy: the download buttons.
            --}}
            <x-appointment-pass-card :appointment="$appointment">

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

            </x-appointment-pass-card>


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
                PROGRESS
                Below the pass and below the student's own actions, so the
                track is this page's word on where the booking stands
                rather than part of the pass a scanner verifies. The panel
                leaves the top edge to the track's own spacing.
            ========================================================== --}}
            <div class="mt-6 bg-white rounded-2xl
                        border border-gray-200
                        shadow-sm p-5 sm:p-6 pt-0 sm:pt-0">

                <x-appointment-progress :appointment="$appointment" />

            </div>


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
