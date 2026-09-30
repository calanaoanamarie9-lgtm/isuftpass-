<x-app-layout>

    <div class="min-h-screen bg-[#f8fafc] py-8">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="flex flex-col sm:flex-row
                        sm:items-center
                        sm:justify-between
                        gap-5
                        mb-8">

                {{-- LEFT --}}
                <div class="flex items-center gap-4">

                    {{-- LOGO --}}
                    <div class="w-16 h-16
                                rounded-2xl
                                bg-gray-900
                                flex items-center justify-center
                                shadow-md
                                overflow-hidden
                                shrink-0">

                        <img src="{{ asset('img/isufstpass-logo.png') }}"
                             alt="ISUFSTPASS"
                             class="w-full h-full object-contain">

                    </div>

                    {{-- TITLE --}}
                    <div>

                        <h1 class="text-2xl sm:text-3xl
                                   font-extrabold
                                   text-gray-950
                                   tracking-tight">

                            New Request

                        </h1>

                        <p class="mt-1 text-sm sm:text-base text-gray-500">
                            ISUFSTPASS Student Services
                        </p>

                    </div>

                </div>


                {{-- ONLINE STATUS --}}
                <div class="flex items-center gap-2
                            text-sm font-medium
                            text-gray-600">

                    <span class="relative flex h-3 w-3">

                        <span class="absolute inline-flex
                                     h-full w-full
                                     rounded-full
                                     bg-green-400
                                     opacity-75
                                     animate-ping">
                        </span>

                        <span class="relative inline-flex
                                     rounded-full
                                     h-3 w-3
                                     bg-green-500">
                        </span>

                    </span>

                    Online Student Portal

                </div>

            </div>


            {{-- =========================================================
                BLUE INFORMATION BANNER
            ========================================================== --}}
            <div class="relative overflow-hidden
                        rounded-2xl
                        bg-gradient-to-r
                        from-blue-950
                        via-blue-900
                        to-indigo-950
                        shadow-lg
                        mb-10">

                <div class="absolute -right-20 -top-24
                            w-72 h-72
                            rounded-full
                            bg-white/5
                            pointer-events-none">
                </div>

                <div class="absolute right-20 -bottom-28
                            w-64 h-64
                            rounded-full
                            bg-blue-400/10
                            pointer-events-none">
                </div>


                <div class="relative
                            flex items-center
                            gap-5
                            px-6 sm:px-8
                            py-7">

                    {{-- ICON --}}
                    <div class="w-14 h-14
                                rounded-2xl
                                bg-white/10
                                border border-white/10
                                flex items-center justify-center
                                shrink-0">

                        <svg class="w-7 h-7 text-yellow-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                        </svg>

                    </div>


                    {{-- TEXT --}}
                    <div>

                        <h2 class="text-xl sm:text-2xl
                                   font-extrabold
                                   text-white">

                            Request School Services

                        </h2>

                        <p class="mt-1
                                  text-sm sm:text-base
                                  text-blue-100">

                            Submit academic document requests or schedule
                            an appointment with an ISUFST office.

                        </p>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                QUESTION
            ========================================================== --}}
            <div class="mb-5">

                <h2 class="text-lg
                           font-extrabold
                           text-gray-900">

                    What would you like to request?

                </h2>

                <p class="mt-1 text-sm text-gray-500">

                    Select one of the services below.

                </p>

            </div>


            {{-- =========================================================
                REQUEST OPTIONS
            ========================================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">


                {{-- =====================================================
                    APPOINTMENT REQUEST
                ====================================================== --}}
                <a href="{{ route('student.appointments.create') }}"
                   class="group relative
                          flex items-start gap-5
                          bg-white
                          rounded-2xl
                          border-2 border-gray-200
                          p-6
                          shadow-sm
                          transition-all duration-200
                          hover:border-blue-700
                          hover:shadow-md
                          hover:-translate-y-0.5">

                    {{-- ICON --}}
                    <div class="w-14 h-14
                                rounded-2xl
                                bg-yellow-50
                                text-yellow-700
                                flex items-center justify-center
                                shrink-0
                                transition
                                group-hover:bg-yellow-100">

                        <svg class="w-7 h-7"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                        </svg>

                    </div>


                    {{-- CONTENT --}}
                    <div class="flex-1 min-w-0">

                        <h3 class="text-lg
                                   font-extrabold
                                   text-gray-900">

                            Appointment Request

                        </h3>

                        <p class="mt-2
                                  text-sm
                                  leading-6
                                  text-gray-500">

                            Schedule a visit with a school office or
                            department based on available dates and
                            time slots.

                        </p>

                        <div class="mt-4
                                    inline-flex
                                    items-center gap-2
                                    text-sm
                                    font-bold
                                    text-blue-800
                                    group-hover:gap-3
                                    transition-all">

                            Schedule Appointment

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 7l5 5m0 0l-5 5m5-5H6"/>

                            </svg>

                        </div>

                    </div>

                </a>


                {{-- =====================================================
                    DOCUMENT REQUEST
                ====================================================== --}}
                <a href="{{ route('student.documents.create') }}"
                   class="group relative
                          flex items-start gap-5
                          bg-white
                          rounded-2xl
                          border-2 border-blue-950
                          p-6
                          shadow-sm
                          transition-all duration-200
                          hover:shadow-md
                          hover:-translate-y-0.5">

                    {{-- SELECTED CHECK --}}
                    <div class="absolute
                                top-5
                                right-5
                                w-7 h-7
                                rounded-full
                                bg-blue-950
                                text-white
                                flex items-center justify-center">

                        <svg class="w-4 h-4"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M5 13l4 4L19 7"/>

                        </svg>

                    </div>


                    {{-- ICON --}}
                    <div class="w-14 h-14
                                rounded-2xl
                                bg-blue-950
                                text-white
                                flex items-center justify-center
                                shrink-0
                                shadow-sm">

                        <svg class="w-7 h-7"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                        </svg>

                    </div>


                    {{-- CONTENT --}}
                    <div class="flex-1 min-w-0 pr-7">

                        <h3 class="text-lg
                                   font-extrabold
                                   text-gray-900">

                            Document Request

                        </h3>

                        <p class="mt-2
                                  text-sm
                                  leading-6
                                  text-gray-500">

                            Request academic records and documents from
                            the records office using the official
                            requisition form.

                        </p>

                        <div class="mt-4
                                    inline-flex
                                    items-center gap-2
                                    text-sm
                                    font-bold
                                    text-blue-800
                                    group-hover:gap-3
                                    transition-all">

                            Request Document

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 7l5 5m0 0l-5 5m5-5H6"/>

                            </svg>

                        </div>

                    </div>

                </a>

            </div>


            {{-- =========================================================
                INFORMATION BOX
            ========================================================== --}}
            <div class="mt-8
                        rounded-2xl
                        border border-blue-100
                        bg-blue-50
                        px-5 py-4">

                <div class="flex items-start gap-3">

                    <div class="w-8 h-8
                                rounded-full
                                bg-blue-100
                                text-blue-700
                                flex items-center justify-center
                                shrink-0">

                        <svg class="w-4 h-4"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>

                        </svg>

                    </div>

                    <div>

                        <p class="text-sm font-semibold text-blue-900">
                            Need to check an existing request?
                        </p>

                        <p class="mt-1 text-sm text-blue-700">

                            View your

                            <a href="{{ route('student.documents.index') }}"
                               class="font-bold underline hover:text-blue-950">

                                My Requests

                            </a>

                            or

                            <a href="{{ route('student.appointments.index') }}"
                               class="font-bold underline hover:text-blue-950">

                                My Appointments

                            </a>.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>