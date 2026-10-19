<x-app-layout>

    <div class="min-h-screen bg-slate-50 py-8">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                BACK
            ========================================================== --}}
            <a href="{{ route('registrar.appointments.show', $appointment) }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500
                      hover:text-blue-800 transition mb-6">

                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>

                Back to Appointment
            </a>


            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="relative overflow-hidden rounded-3xl
                        bg-gradient-to-r from-blue-950 via-blue-900 to-indigo-900
                        shadow-lg mb-6">

                {{-- Decorative circles --}}
                <div class="absolute -right-16 -top-20 w-64 h-64
                            rounded-full bg-yellow-400/10"></div>

                <div class="absolute -right-10 -bottom-28 w-72 h-72
                            rounded-full bg-white/5"></div>

                <div class="relative p-6 sm:p-8">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center sm:justify-between gap-5">

                        <div class="flex items-start gap-4">

                            {{-- Icon --}}
                            <div class="w-12 h-12 rounded-2xl
                                        bg-yellow-400 text-blue-950
                                        flex items-center justify-center
                                        shadow-lg shrink-0">

                                <svg class="w-6 h-6"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 4h10
                                             M5 21h14a2 2 0 002-2V8
                                             a2 2 0 00-2-2H5a2 2 0 00-2 2v11
                                             a2 2 0 002 2z"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 15l2 2 4-4"/>
                                </svg>

                            </div>


                            <div>

                                <p class="text-[11px] font-bold uppercase
                                          tracking-[0.2em] text-blue-200">
                                    ISUFSTPASS • Appointment Management
                                </p>

                                <h1 class="text-2xl sm:text-3xl font-extrabold
                                           text-white mt-1">
                                    Reschedule Appointment
                                </h1>

                                <p class="text-sm text-blue-100 mt-2 max-w-xl">
                                    Select a new available date and time for the student's
                                    appointment.
                                </p>

                            </div>

                        </div>


                        {{-- Reference --}}
                        <div class="sm:text-right">

                            <p class="text-[10px] font-bold uppercase
                                      tracking-widest text-blue-200">
                                Reference
                            </p>

                            <div class="inline-flex items-center gap-2 mt-1
                                        px-3 py-2 rounded-xl
                                        bg-white/10 border border-white/10">

                                <span class="w-2 h-2 rounded-full bg-yellow-400"></span>

                                <span class="text-sm font-bold text-white">
                                    {{ $appointment->reference_code }}
                                </span>

                            </div>

                        </div>

                    </div>

                </div>
            </div>


            {{-- =========================================================
                STUDENT INFORMATION
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-slate-200
                        shadow-sm overflow-hidden mb-6">

                <div class="px-6 py-5 border-b border-slate-100">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-xl
                                    bg-blue-50 text-blue-800
                                    flex items-center justify-center">

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0
                                         4 4 0 018 0z
                                         M12 14a7 7 0 00-7 7h14
                                         a7 7 0 00-7-7z"/>
                            </svg>

                        </div>

                        <div>

                            <p class="text-[11px] font-bold uppercase
                                      tracking-wider text-blue-700">
                                Student Information
                            </p>

                            <h2 class="text-lg font-extrabold text-slate-900">
                                {{ $appointment->user->name }}
                            </h2>

                        </div>

                    </div>

                </div>


                <div class="grid grid-cols-1 sm:grid-cols-3
                            divide-y sm:divide-y-0 sm:divide-x
                            divide-slate-100">

                    <div class="px-6 py-5">

                        <p class="text-[10px] font-bold uppercase
                                  tracking-widest text-slate-400">
                            Student ID
                        </p>

                        <p class="text-sm font-bold text-slate-800 mt-1">
                            {{ $appointment->user->studentProfile?->student_id ?? '—' }}
                        </p>

                    </div>


                    <div class="px-6 py-5">

                        <p class="text-[10px] font-bold uppercase
                                  tracking-widest text-slate-400">
                            Office
                        </p>

                        <p class="text-sm font-bold text-slate-800 mt-1">
                            {{ $appointment->office }}
                        </p>

                    </div>


                    <div class="px-6 py-5">

                        <p class="text-[10px] font-bold uppercase
                                  tracking-widest text-slate-400">
                            Purpose
                        </p>

                        <p class="text-sm font-bold text-slate-800 mt-1">
                            {{ $appointment->purpose }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                CURRENT SCHEDULE
            ========================================================== --}}
            <div class="bg-blue-950 rounded-2xl shadow-md overflow-hidden mb-6">

                <div class="p-6">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center sm:justify-between gap-5">

                        <div>

                            <div class="flex items-center gap-2 mb-3">

                                <div class="w-8 h-8 rounded-lg
                                            bg-white/10
                                            flex items-center justify-center">

                                    <svg class="w-4 h-4 text-yellow-400"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M8 7V3m8 4V3m-9 4h10
                                                 M5 21h14a2 2 0 002-2V8
                                                 a2 2 0 00-2-2H5v11
                                                 a2 2 0 002 2z"/>
                                    </svg>

                                </div>

                                <p class="text-[11px] font-bold uppercase
                                          tracking-widest text-blue-200">
                                    Current Schedule
                                </p>

                            </div>


                            <p class="text-2xl font-extrabold text-white">
                                {{ $appointment->date->format('F j, Y') }}
                            </p>

                            <p class="text-sm text-blue-200 mt-1">
                                {{ $appointment->timeToCome() }}
                            </p>

                        </div>


                        <div class="sm:text-right">

                            <p class="text-[10px] font-bold uppercase
                                      tracking-widest text-blue-300">
                                Office
                            </p>

                            <p class="text-sm font-bold text-white mt-1">
                                {{ $appointment->office }}
                            </p>

                            @if ($appointment->original_date)

                                <p class="text-xs text-blue-300 mt-3">
                                    Originally scheduled:
                                </p>

                                <p class="text-sm font-semibold text-white">
                                    {{ $appointment->original_date->format('F j, Y') }}
                                </p>

                            @endif

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                ERRORS
            ========================================================== --}}
            @if ($errors->any())

                <div class="mb-6 bg-red-50 border border-red-200
                            rounded-2xl p-4">

                    <div class="flex gap-3">

                        <div class="w-9 h-9 rounded-xl bg-red-100
                                    flex items-center justify-center shrink-0">

                            <svg class="w-5 h-5 text-red-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 9v2m0 4h.01
                                         M10.29 3.86l-7.82 14
                                         a1 1 0 00.87 1.5h17.32
                                         a1 1 0 00.87-1.5l-7.82-14
                                         a1 1 0 00-1.74 0z"/>
                            </svg>

                        </div>

                        <div>

                            <p class="text-sm font-bold text-red-800">
                                Please correct the following:
                            </p>

                            <ul class="mt-2 text-sm text-red-700
                                       list-disc list-inside space-y-1">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                RESCHEDULE FORM
            ========================================================== --}}
            <form
                method="POST"
                action="{{ route('registrar.appointments.reschedule.update', $appointment) }}"

                data-confirm="The appointment will be moved to the new date."
                data-confirm-title="Reschedule this appointment?"
                data-confirm-ok="Yes, reschedule"
                data-confirm-icon="question"

                x-data="{
                    date: '{{ old('date') }}'
                }"
            >

                @csrf
                @method('PUT')


                {{-- =====================================================
                    FORM CONTAINER
                ====================================================== --}}
                <div class="bg-white rounded-2xl border border-slate-200
                            shadow-sm overflow-hidden">


                    {{-- FORM HEADER --}}
                    <div class="px-6 py-5 border-b border-slate-100
                                bg-white">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10 rounded-xl
                                        bg-yellow-50 text-yellow-600
                                        flex items-center justify-center">

                                <svg class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 8v4l3 2"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M21 12a9 9 0 11-18 0
                                             9 9 0 0118 0z"/>

                                </svg>

                            </div>

                            <div>

                                <h2 class="font-extrabold text-slate-900">
                                    Select New Schedule
                                </h2>

                                <p class="text-xs text-slate-400 mt-0.5">
                                    Choose a new date for this appointment.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="p-6 sm:p-8 space-y-8">


                        {{-- =================================================
                            STEP 1 — DATE
                        ================================================== --}}
                        <div>

                            <div class="flex flex-col sm:flex-row
                                        sm:items-center sm:justify-between
                                        gap-3 mb-4">

                                <div>

                                    <div class="flex items-center gap-2">

                                        <span class="w-7 h-7 rounded-lg
                                                     bg-blue-900 text-white
                                                     flex items-center justify-center
                                                     text-xs font-extrabold">
                                            1
                                        </span>

                                        <label class="text-sm font-extrabold text-slate-800">
                                            Select New Date
                                        </label>

                                    </div>

                                    <p class="text-xs text-slate-400 mt-1 ml-9">
                                        Pick the day this appointment moves to.
                                    </p>

                                </div>

                            </div>


                            <input
                                id="date"
                                name="date"
                                type="date"
                                min="{{ now()->toDateString() }}"
                                value="{{ old('date', $appointment->date->toDateString()) }}"
                                x-model="date"
                                required
                                class="w-full sm:max-w-xs rounded-xl border-slate-300
                                       bg-white text-sm text-slate-800
                                       shadow-sm
                                       focus:border-blue-600
                                       focus:ring-blue-600"
                            >

                        </div>


                        <div class="border-t border-slate-100"></div>


                        {{-- =================================================
                            STEP 2 — REASON
                        ================================================== --}}
                        <div>

                            <div class="flex items-center gap-2 mb-1">

                                <span class="w-7 h-7 rounded-lg
                                             bg-blue-900 text-white
                                             flex items-center justify-center
                                             text-xs font-extrabold">
                                    2
                                </span>

                                <label
                                    for="reschedule_reason"
                                    class="text-sm font-extrabold text-slate-800">

                                    Reason for Reschedule

                                </label>

                            </div>

                            <p class="text-xs text-slate-400 ml-9 mb-4">
                                Explain why the original appointment needs to be changed.
                            </p>


                            <textarea
                                id="reschedule_reason"
                                name="reschedule_reason"
                                rows="4"
                                required
                                placeholder="Example: The student requested a date the office cannot accommodate."
                                class="w-full rounded-2xl border-slate-200
                                       bg-slate-50 px-4 py-3 text-sm
                                       focus:bg-white
                                       focus:border-blue-500
                                       focus:ring-blue-500">{{ old('reschedule_reason') }}</textarea>


                            <div class="flex items-start gap-2 mt-3">

                                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01
                                             M12 21a9 9 0 100-18
                                             9 9 0 000 18z"/>

                                </svg>

                                <p class="text-xs text-slate-400">
                                    This reason will be visible to the student and included
                                    in the appointment rescheduling email.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =====================================================
                        FOOTER / ACTIONS
                    ====================================================== --}}
                    <div class="px-6 sm:px-8 py-5
                                bg-slate-50 border-t border-slate-200">

                        <div class="flex flex-col lg:flex-row
                                    lg:items-center lg:justify-between gap-5">


                            {{-- Notification --}}
                            <div class="flex items-start gap-3">

                                <div class="w-10 h-10 rounded-xl
                                            bg-yellow-50
                                            flex items-center justify-center
                                            shrink-0">

                                    <svg class="w-5 h-5 text-yellow-600"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M15 17h5l-1.405-1.405
                                                 A2.032 2.032 0 0118 14.158V11
                                                 a6.002 6.002 0 00-4-5.659V5
                                                 a2 2 0 10-4 0v.341
                                                 C7.67 6.165 6 8.388 6 11v3.159
                                                 c0 .538-.214 1.055-.595 1.436
                                                 L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>

                                    </svg>

                                </div>

                                <div>

                                    <p class="text-sm font-bold text-slate-700">
                                        Student notification
                                    </p>

                                    <p class="text-xs text-slate-400 mt-0.5 max-w-md">
                                        The student will receive an email after the
                                        reschedule is confirmed.
                                    </p>

                                </div>

                            </div>


                            {{-- Buttons --}}
                            <div class="flex flex-col sm:flex-row
                                        items-stretch sm:items-center gap-3">

                                <a
                                    href="{{ route('registrar.appointments.show', $appointment) }}"
                                    class="inline-flex items-center justify-center
                                           px-5 py-2.5
                                           bg-white border border-slate-200
                                           text-slate-600 text-sm font-bold
                                           rounded-xl hover:bg-slate-100
                                           transition">

                                    Cancel

                                </a>


                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center
                                           gap-2 px-6 py-2.5
                                           bg-blue-900 text-white
                                           text-sm font-bold rounded-xl
                                           hover:bg-blue-950
                                           shadow-sm hover:shadow-md
                                           transition">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M3 10h10a4 4 0 014 4v2"/>

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M7 6l-4 4 4 4"/>

                                    </svg>

                                    Save &amp; Notify Student

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>
