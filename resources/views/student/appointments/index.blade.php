<x-app-layout>

    <div class="min-h-screen bg-[#f6f8fb] py-8">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                 HEADER
            ========================================================== --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">

                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-[#174b91] mb-1">
                        ISUFSTPASS
                    </p>

                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900">
                        My Appointments
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        View and manage your campus office appointments.
                    </p>
                </div>

                <a href="{{ route('student.appointments.create') }}"
                   class="inline-flex items-center justify-center gap-2
                          px-5 py-2.5
                          bg-[#123b78] hover:bg-[#0d2d5d]
                          text-white text-sm font-bold
                          rounded-xl transition shadow-sm">

                    <svg class="w-4 h-4"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>

                    Book Appointment
                </a>

            </div>


            {{-- =========================================================
                 FLASH MESSAGES
            ========================================================== --}}
            @if (session('status'))
                <div class="mb-5 rounded-xl border border-green-200
                            bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded-xl border border-red-200
                            bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif


            {{-- =========================================================
                 TABS
            ========================================================== --}}
            <div class="bg-white border border-gray-200 rounded-xl p-1
                        inline-flex flex-wrap gap-1 mb-6 shadow-sm">

                @foreach ([
                    'upcoming' => 'Upcoming',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled'
                ] as $key => $label)

                    <a href="{{ route('student.appointments.index', ['tab' => $key]) }}"
                       class="px-4 py-2 rounded-lg text-sm font-semibold transition
                       {{ $tab === $key
                            ? 'bg-[#123b78] text-white'
                            : 'text-gray-600 hover:bg-gray-100' }}">

                        {{ $label }}

                    </a>

                @endforeach

            </div>


            {{-- =========================================================
                 APPOINTMENT LIST
            ========================================================== --}}
            <div class="space-y-4">

                @forelse ($appointments as $appointment)

                    <div class="bg-white rounded-2xl border border-gray-200
                                shadow-sm overflow-hidden">

                        {{-- =================================================
                             APPOINTMENT HEADER
                        ================================================== --}}
                        <div class="p-5 sm:p-6">

                            <div class="flex flex-col lg:flex-row
                                        lg:items-center lg:justify-between gap-4">

                                <div class="flex items-center gap-4">

                                    {{-- Calendar Icon --}}
                                    <div class="w-11 h-11 rounded-xl
                                                bg-blue-50
                                                flex items-center justify-center
                                                flex-shrink-0">

                                        <svg class="w-5 h-5 text-[#174b91]"
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

                                        <h2 class="text-base sm:text-lg
                                                   font-black text-gray-900">

                                            {{ $appointment->office }}

                                        </h2>

                                        <p class="text-sm text-gray-500 mt-0.5">
                                            {{ $appointment->purpose }}
                                        </p>

                                    </div>

                                </div>


                                {{-- Status --}}
                                <span class="inline-flex items-center self-start
                                             lg:self-center
                                             px-3 py-1.5 rounded-full
                                             text-xs font-bold border

                                    @if ($appointment->status === 'confirmed')
                                        bg-green-50 text-green-700 border-green-200

                                    @elseif ($appointment->status === 'checked_in')
                                        bg-cyan-50 text-cyan-700 border-cyan-200

                                    @elseif ($appointment->status === 'rescheduled')
                                        bg-indigo-50 text-indigo-700 border-indigo-200

                                    @elseif ($appointment->status === 'for_reschedule')
                                        bg-orange-50 text-orange-700 border-orange-200

                                    @elseif ($appointment->status === 'pending')
                                        bg-red-50 text-red-700 border-red-200

                                    @elseif ($appointment->status === 'completed')
                                        bg-blue-50 text-blue-700 border-blue-200

                                    @elseif ($appointment->status === 'no_show')
                                        bg-gray-100 text-gray-600 border-gray-200

                                    @else
                                        bg-red-50 text-red-600 border-red-200
                                    @endif
                                ">

                                    <span class="w-1.5 h-1.5 rounded-full mr-2

                                        @if ($appointment->status === 'confirmed')
                                            bg-green-600
                                        @elseif ($appointment->status === 'checked_in')
                                            bg-cyan-600
                                        @elseif ($appointment->status === 'rescheduled')
                                            bg-indigo-600
                                        @elseif ($appointment->status === 'for_reschedule')
                                            bg-orange-600
                                        @elseif ($appointment->status === 'pending')
                                            bg-red-600
                                        @elseif ($appointment->status === 'completed')
                                            bg-blue-600
                                        @else
                                            bg-gray-500
                                        @endif
                                    "></span>

                                    {{ \App\Enums\AppointmentStatus::tryFrom($appointment->status)?->label()
                                        ?? ucfirst($appointment->status) }}

                                </span>

                            </div>


                            {{-- =================================================
                                 DETAILS
                            ================================================== --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3
                                        gap-3 mt-6">

                                {{-- Date --}}
                                <div class="border border-gray-200
                                            rounded-xl px-4 py-3">

                                    <p class="text-[10px] uppercase
                                              tracking-widest
                                              text-gray-400 font-bold">
                                        Appointment Date
                                    </p>

                                    <p class="text-sm font-bold text-gray-800 mt-1">
                                        {{ $appointment->date->format('F j, Y') }}
                                    </p>

                                </div>


                                {{-- Time to come --}}
                                <div class="border border-gray-200
                                            rounded-xl px-4 py-3">

                                    <p class="text-[10px] uppercase
                                              tracking-widest
                                              text-gray-400 font-bold">

                                        {{ $appointment->confirmed_time
                                            ? 'Come At'
                                            : 'Time Slot Asked' }}

                                    </p>

                                    <p class="text-sm font-bold text-gray-800 mt-1">
                                        {{ $appointment->timeToCome() }}
                                    </p>

                                    @if ($appointment->confirmed_time
                                        && $appointment->time_slot
                                        && $appointment->time_slot !== $appointment->confirmed_time)

                                        <p class="text-[11px]
                                                  text-gray-400
                                                  mt-1">

                                            You asked for
                                            {{ $appointment->time_slot }}

                                        </p>

                                    @endif

                                </div>


                                {{-- Booked --}}
                                <div class="border border-gray-200
                                            rounded-xl px-4 py-3">

                                    <p class="text-[10px] uppercase
                                              tracking-widest
                                              text-gray-400 font-bold">
                                        Date Booked
                                    </p>

                                    <p class="text-sm font-bold text-gray-800 mt-1">
                                        {{ $appointment->created_at->format('M j, Y g:i A') }}
                                    </p>

                                </div>

                            </div>


                            {{-- =================================================
                                 RESCHEDULE NOTICE
                            ================================================== --}}
                            @if ($appointment->original_date)

                                <div class="mt-4 rounded-xl
                                            border border-indigo-100
                                            bg-indigo-50 px-4 py-3">

                                    <p class="text-[10px] uppercase
                                              tracking-widest
                                              font-black text-indigo-600">

                                        {{ $appointment->status === 'for_reschedule'
                                            ? 'Needs Rescheduling'
                                            : 'Previous Schedule' }}

                                    </p>

                                    <p class="text-sm text-indigo-800 mt-1">

                                        Originally
                                        {{ $appointment->original_date->format('F j, Y') }}

                                        @if ($appointment->original_time_slot)
                                            · {{ $appointment->original_time_slot }}
                                        @endif

                                        @if ($appointment->reschedule_reason)
                                            · {{ strtolower($appointment->reschedule_reason) }}
                                        @endif

                                    </p>


                                    @if ($appointment->status === 'for_reschedule')

                                        <p class="text-xs text-orange-600
                                                  mt-2 font-semibold">

                                            This slot is full. Please wait for the
                                            Registrar to move your appointment to
                                            the next available schedule.

                                        </p>

                                    @endif

                                </div>

                            @endif


                            {{-- =================================================
                                 ACTIONS
                            ================================================== --}}
                            @if ($appointment->isUpcoming())

                                <div class="mt-6 pt-5 border-t border-gray-100
                                            flex flex-col sm:flex-row
                                            sm:items-center
                                            sm:justify-between gap-3">



                                    <div class="flex flex-wrap gap-2">

                                        {{-- QR --}}
                                        <a href="{{ route('student.appointments.qr', $appointment) }}"
                                           class="inline-flex items-center gap-2
                                                  px-4 py-2.5 rounded-xl
                                                  bg-[#123b78]
                                                  hover:bg-[#0d2d5d]
                                                  text-white text-sm font-bold
                                                  transition">

                                            <svg class="w-4 h-4"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M3 9a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 3h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5a2 2 0 012-2zM3 15h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 012-2zM17 13l2 2m0 0l2-2m2 2v-6"/>

                                            </svg>

                                          View QR Code Details

                                        </a>


                                        {{-- EDIT / CANCEL (before office approval) --}}
                                        @if ($appointment->isModifiableByStudent())

                                            <a href="{{ route('student.appointments.edit', $appointment) }}"
                                               class="inline-flex items-center gap-2
                                                      px-4 py-2.5 rounded-xl
                                                      border border-gray-200
                                                      bg-white
                                                      text-gray-700 text-sm font-bold
                                                      hover:bg-gray-50
                                                      transition">

                                                <svg class="w-4 h-4"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>

                                                </svg>

                                                Edit

                                            </a>

                                            <form method="POST"
                                                  action="{{ route('student.appointments.cancel', $appointment) }}"
                                                  onsubmit="return confirm('Cancel this appointment? This cannot be undone.');">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex items-center gap-2
                                                               px-4 py-2.5 rounded-xl
                                                               border border-red-200
                                                               bg-white
                                                               text-red-600 text-sm font-bold
                                                               hover:bg-red-50
                                                               transition">

                                                    <svg class="w-4 h-4"
                                                         fill="none"
                                                         stroke="currentColor"
                                                         viewBox="0 0 24 24">

                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"/>

                                                    </svg>

                                                    Cancel

                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </div>

                            @endif

                        </div>

                    </div>

                @empty

                    {{-- =====================================================
                         EMPTY STATE
                    ====================================================== --}}
                    <div class="bg-white rounded-2xl
                                border border-gray-200
                                shadow-sm p-12 text-center">

                        <div class="w-14 h-14 mx-auto rounded-2xl
                                    bg-blue-50
                                    flex items-center justify-center">

                            <svg class="w-7 h-7 text-[#174b91]"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                            </svg>

                        </div>

                        <h3 class="mt-4 text-base font-black text-gray-800">
                            No {{ $tab }} appointments
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Book an appointment with a campus office to get started.
                        </p>

                        @if ($tab === 'upcoming')

                            <a href="{{ route('student.appointments.create') }}"
                               class="inline-flex items-center gap-2 mt-5
                                      px-4 py-2.5 rounded-xl
                                      bg-[#123b78] hover:bg-[#0d2d5d]
                                      text-white text-sm font-bold transition">

                                Book Appointment

                            </a>

                        @endif

                    </div>

                @endforelse

            </div>


            {{-- =========================================================
                 PAGINATION
            ========================================================== --}}
            @if ($appointments->hasPages())

                <div class="mt-6">
                    {{ $appointments->links() }}
                </div>

            @endif

        </div>

    </div>

</x-app-layout>