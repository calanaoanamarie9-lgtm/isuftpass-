<x-app-layout>
    <div class="min-h-screen bg-[#f5f7fb] py-6">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= HEADER ================= --}}
            <div class="bg-gradient-to-r from-[#063b91] to-[#0b5ed7]
                        rounded-2xl px-6 py-5 shadow-sm">

                <div class="flex items-center justify-between gap-4">

                    <div class="flex items-center gap-4">

                        {{-- QR Icon --}}
                        <div class="w-11 h-11 bg-white/15 rounded-xl
                                    flex items-center justify-center">
                            <svg class="w-6 h-6 text-white"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h2m0-3v2m0 2h4m-4-7h4"/>
                            </svg>
                        </div>

                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-white">
                                My QR Code
                            </h1>

                            <p class="text-xs sm:text-sm text-blue-100">
                                Your digital verification pass. Present your QR code to authorized staff for document verification.
                            </p>
                        </div>

                    </div>

                    {{-- Secure Verification --}}
                    <div class="hidden sm:flex items-center gap-2
                                bg-white/10 border border-white/20
                                rounded-full px-4 py-2">

                        <span class="w-2 h-2 bg-green-400 rounded-full"></span>

                        <span class="text-xs font-semibold text-white">
                            Secure Verification
                        </span>

                    </div>

                </div>
            </div>


            {{-- ================= INFO BANNER ================= --}}
            <div class="mt-4 bg-blue-50 border border-blue-100
                        rounded-xl px-4 py-3">

                <div class="flex items-start gap-3">

                    <div class="mt-0.5 w-5 h-5 rounded-full
                                bg-blue-600 text-white
                                flex items-center justify-center">

                        <svg class="w-3 h-3"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>

                    </div>

                    <div>
                        <p class="text-xs font-bold text-blue-800">
                            Keep your QR pass accessible
                        </p>

                        <p class="text-xs text-blue-700 mt-0.5">
                            Your QR code is uniquely generated for your document request.
                            Present it to authorized school personnel when claiming or verifying your documents.
                        </p>
                    </div>

                </div>

            </div>


            {{-- ================= QR CARDS ================= --}}
            <div class="mt-6">

                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3v5h5"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-extrabold text-gray-900">
                            Document Request QR Passes
                        </h2>
                        <p class="text-xs text-gray-500">
                            Claim slips for your document requests.
                        </p>
                    </div>
                    <span class="ml-auto inline-flex items-center px-2.5 py-1 rounded-full
                                bg-blue-50 text-blue-700 text-xs font-bold">
                        {{ $documentRequests->count() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                    @forelse ($documentRequests as $request)

                        <div class="bg-white rounded-2xl border border-gray-200
                                    shadow-sm overflow-hidden
                                    hover:shadow-md transition">

                            {{-- Card Header --}}
                            <div class="bg-gradient-to-r from-[#073b91] to-[#155fc4]
                                        px-4 py-3">

                                <div class="flex items-center justify-between">

                                    <div class="flex items-center gap-3">

                                        {{-- Document Icon --}}
                                        <div class="w-9 h-9 rounded-lg
                                                    bg-yellow-400
                                                    flex items-center justify-center">

                                            <svg class="w-5 h-5 text-[#173b75]"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M15 3v5h5M9 13h6M9 17h6"/>

                                            </svg>

                                        </div>

                                        <div>
                                            <h2 class="text-sm font-bold text-white">
                                                {{ $request->documentsSummary() }}
                                            </h2>

                                            <p class="text-[10px] text-blue-100">
                                                Digital Verification Pass
                                            </p>
                                        </div>

                                    </div>


                                    {{-- Status --}}
                                    @if ($request->status === 'completed')
                                        <span class="px-2 py-1 rounded-full
                                                     bg-green-100 text-green-700
                                                     text-[9px] font-bold">
                                            Approved
                                        </span>
                                    @elseif ($request->status === 'ready_for_pickup')
                                        <span class="px-2 py-1 rounded-full
                                                     bg-green-100 text-green-700
                                                     text-[9px] font-bold">
                                            Approved
                                        </span>
                                    @elseif ($request->status === 'cancelled')
                                        <span class="px-2 py-1 rounded-full
                                                     bg-red-100 text-red-700
                                                     text-[9px] font-bold">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full
                                                     bg-red-100 text-red-700
                                                     text-[9px] font-bold">
                                            Pending
                                        </span>
                                    @endif

                                </div>
                            </div>


                            {{-- QR AREA --}}
                            <div class="p-5">

                                <div class="relative bg-white
                                            border border-gray-200
                                            rounded-xl p-5">

                                    {{-- Corner decorations --}}
                                    <span class="absolute top-3 left-3
                                                 w-5 h-5
                                                 border-l-2 border-t-2
                                                 border-blue-400 rounded-tl"></span>

                                    <span class="absolute top-3 right-3
                                                 w-5 h-5
                                                 border-r-2 border-t-2
                                                 border-blue-400 rounded-tr"></span>

                                    <span class="absolute bottom-3 left-3
                                                 w-5 h-5
                                                 border-l-2 border-b-2
                                                 border-blue-400 rounded-bl"></span>

                                    <span class="absolute bottom-3 right-3
                                                 w-5 h-5
                                                 border-r-2 border-b-2
                                                 border-blue-400 rounded-br"></span>


                                    {{-- QR CODE --}}
                                    <div class="flex justify-center py-3">

                                        <img src="{{ $request->qr_data_uri }}"
                                             alt="QR code for {{ $request->request_number }}"
                                             class="w-48 h-48">

                                    </div>

                                </div>


                                {{-- Reference --}}
                                <div class="text-center mt-4">

                                    <p class="text-[9px] uppercase
                                              tracking-[0.18em]
                                              text-gray-400 font-bold">
                                        Reference Code
                                    </p>

                                    <div class="flex items-center justify-center
                                                gap-2 mt-1">

                                        <svg class="w-3 h-3 text-gray-400"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 00-5.657-5.657L5.757 10.757a6 6 0 108.486 8.486L20 13.485"/>

                                        </svg>

                                        <span class="text-[11px] font-bold
                                                     text-gray-700">
                                            {{ $request->request_number }}
                                        </span>

                                    </div>

                                </div>


                                {{-- View Pass --}}
                                <a href="{{ route('student.documents.show', $request) }}"
                                   class="mt-4 w-full inline-flex
                                          items-center justify-center gap-2
                                          px-4 py-2.5
                                          bg-[#1769d2]
                                          hover:bg-[#0d56b8]
                                          text-white text-xs font-bold
                                          rounded-xl transition">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7z"/>

                                    </svg>

                                    View Full Pass

                                    <svg class="w-3.5 h-3.5"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 5l7 7-7 7"/>

                                    </svg>

                                </a>

                            </div>

                        </div>

                    @empty

                        {{-- Empty State --}}
                        <div class="col-span-full bg-white
                                    rounded-2xl border border-gray-200
                                    p-12 text-center">

                            <div class="mx-auto w-14 h-14
                                        bg-blue-50 rounded-2xl
                                        flex items-center justify-center">

                                <svg class="w-7 h-7 text-blue-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6z"/>

                                </svg>

                            </div>

                            <h3 class="mt-4 text-sm font-bold text-gray-800">
                                No QR Codes Available
                            </h3>

                            <p class="mt-1 text-xs text-gray-400">
                                QR codes will appear here once you have submitted a document request.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>


            {{-- ================= APPOINTMENT QR CARDS ================= --}}
            <div class="mt-10">

                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-extrabold text-gray-900">
                            Appointment QR Passes
                        </h2>
                        <p class="text-xs text-gray-500">
                            Entry passes for your upcoming appointments.
                        </p>
                    </div>
                    <span class="ml-auto inline-flex items-center px-2.5 py-1 rounded-full
                                bg-blue-50 text-blue-700 text-xs font-bold">
                        {{ $appointments->count() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                    @forelse ($appointments as $appointment)

                        <div class="bg-white rounded-2xl border border-gray-200
                                    shadow-sm overflow-hidden
                                    hover:shadow-md transition">

                            {{-- Card Header --}}
                            <div class="bg-gradient-to-r from-[#073b91] to-[#155fc4]
                                        px-4 py-3">

                                <div class="flex items-center justify-between">

                                    <div class="flex items-center gap-3">

                                        {{-- Calendar Icon --}}
                                        <div class="w-9 h-9 rounded-lg
                                                    bg-white/15
                                                    flex items-center justify-center">

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
                                            <h2 class="text-sm font-bold text-white">
                                                {{ $appointment->office }}
                                            </h2>

                                            <p class="text-[10px] text-blue-100">
                                                {{ $appointment->date->format('M j, Y') }} •
                                                {{ $appointment->time_slot }}
                                            </p>
                                        </div>

                                    </div>


                                    {{-- Status --}}
                                    <span class="px-2 py-1 rounded-full
                                                 bg-green-100 text-green-700
                                                 text-[9px] font-bold uppercase">
                                        Scheduled
                                    </span>

                                </div>
                            </div>


                            {{-- QR AREA --}}
                            <div class="p-5">

                                <div class="relative bg-white
                                            border border-gray-200
                                            rounded-xl p-5">

                                    {{-- Corner decorations --}}
                                    <span class="absolute top-3 left-3
                                                 w-5 h-5
                                                 border-l-2 border-t-2
                                                 border-blue-400 rounded-tl"></span>

                                    <span class="absolute top-3 right-3
                                                 w-5 h-5
                                                 border-r-2 border-t-2
                                                 border-blue-400 rounded-tr"></span>

                                    <span class="absolute bottom-3 left-3
                                                 w-5 h-5
                                                 border-l-2 border-b-2
                                                 border-blue-400 rounded-bl"></span>

                                    <span class="absolute bottom-3 right-3
                                                 w-5 h-5
                                                 border-r-2 border-b-2
                                                 border-blue-400 rounded-br"></span>


                                    {{-- QR CODE --}}
                                    <div class="flex justify-center py-3">

                                        <img src="{{ $appointment->qr_data_uri }}"
                                             alt="QR code for {{ $appointment->reference_code }}"
                                             class="w-48 h-48">

                                    </div>

                                </div>


                                {{-- Reference --}}
                                <div class="text-center mt-4">

                                    <p class="text-[9px] uppercase
                                              tracking-[0.18em]
                                              text-gray-400 font-bold">
                                        Reference Code
                                    </p>

                                    <div class="flex items-center justify-center
                                                gap-2 mt-1">

                                        <svg class="w-3 h-3 text-gray-400"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 00-5.657-5.657L5.757 10.757a6 6 0 108.486 8.486L20 13.485"/>

                                        </svg>

                                        <span class="text-[11px] font-bold
                                                     text-gray-700">
                                            {{ $appointment->reference_code }}
                                        </span>

                                    </div>

                                </div>


                                {{-- View Pass --}}
                                <a href="{{ route('student.appointments.qr', $appointment) }}"
                                   class="mt-4 w-full inline-flex
                                          items-center justify-center gap-2
                                          px-4 py-2.5
                                          bg-[#1769d2]
                                          hover:bg-[#0d56b8]
                                          text-white text-xs font-bold
                                          rounded-xl transition">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7z"/>

                                    </svg>

                                    View Full Pass

                                    <svg class="w-3.5 h-3.5"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 5l7 7-7 7"/>

                                    </svg>

                                </a>

                            </div>

                        </div>

                    @empty

                        {{-- Empty State --}}
                        <div class="col-span-full bg-white
                                    rounded-2xl border border-gray-200
                                    p-12 text-center">

                            <div class="mx-auto w-14 h-14
                                        bg-blue-50 rounded-2xl
                                        flex items-center justify-center">

                                <svg class="w-7 h-7 text-blue-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                </svg>

                            </div>

                            <h3 class="mt-4 text-sm font-bold text-gray-800">
                                No Appointment Passes
                            </h3>

                            <p class="mt-1 text-xs text-gray-400">
                                QR passes will appear here once you have booked an appointment.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>
    </div>
</x-app-layout>