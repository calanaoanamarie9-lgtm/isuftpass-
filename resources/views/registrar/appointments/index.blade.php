<x-app-layout>

    <div class="min-h-screen bg-slate-50 py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8">

                <div class="flex items-start gap-4">

                    {{-- Page Icon --}}
                    <div class="w-14 h-14 rounded-2xl bg-blue-100
                                flex items-center justify-center shrink-0">

                        <svg class="w-7 h-7 text-blue-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 4h10
                                     M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z"/>
                        </svg>

                    </div>

                    <div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold
                                   tracking-tight text-slate-900">

                            Appointments Management

                        </h1>

                        <p class="text-sm sm:text-base text-slate-500 mt-1">

                            Manage and process student appointments.

                        </p>

                        <div class="flex items-center gap-2 mt-2">

                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>

                            <span class="text-xs font-semibold text-slate-500">

                                {{ auth()->user()->officeScope() }} Office

                            </span>

                        </div>

                    </div>

                </div>


                {{-- Calendar Button --}}
                <a href="#"
                   class="inline-flex items-center justify-center gap-2
                          px-5 py-3
                          bg-blue-700
                          text-white
                          text-sm font-bold
                          rounded-xl
                          shadow-sm
                          hover:bg-blue-800
                          transition">

                    <svg class="w-5 h-5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M8 7V3m8 4V3m-9 4h10
                                 M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z"/>

                    </svg>

                    Schedule Calendar

                </a>

            </div>


            {{-- =========================================================
                STATISTICS
            ========================================================== --}}
            @php

                $totalAppointments = $appointments->total();

                $pendingCount = $appointments->where('status', 'pending')->count();

                $confirmedCount = $appointments->where('status', 'confirmed')->count();

                $rescheduleCount = $appointments->where('status', 'for_reschedule')->count();

            @endphp


            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-7">


                {{-- TOTAL --}}
                <div class="bg-white rounded-2xl border border-slate-200
                            shadow-sm p-5
                            hover:shadow-md transition">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wider text-slate-400">

                                Total Appointments

                            </p>

                            <p class="text-3xl font-extrabold
                                      text-slate-900 mt-2">

                                {{ $totalAppointments }}

                            </p>

                            <p class="text-xs text-slate-400 mt-1">

                                All appointments

                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-blue-50
                                    flex items-center justify-center">

                            <svg class="w-6 h-6 text-blue-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 4h10
                                         M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z"/>

                            </svg>

                        </div>

                    </div>

                </div>


                {{-- PENDING --}}
                <div class="bg-white rounded-2xl border border-slate-200
                            shadow-sm p-5
                            hover:shadow-md transition">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wider text-slate-400">

                                Pending

                            </p>

                            <p class="text-3xl font-extrabold
                                      text-red-600 mt-2">

                                {{ $pendingCount }}

                            </p>

                            <p class="text-xs text-slate-400 mt-1">

                                Awaiting review

                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-red-50
                                    flex items-center justify-center">

                            <svg class="w-6 h-6 text-red-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 8v4l3 2
                                         m6-2a9 9 0 11-18 0
                                         9 9 0 0118 0z"/>

                            </svg>

                        </div>

                    </div>

                </div>


                {{-- CONFIRMED --}}
                <div class="bg-white rounded-2xl border border-slate-200
                            shadow-sm p-5
                            hover:shadow-md transition">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wider text-slate-400">

                                Confirmed

                            </p>

                            <p class="text-3xl font-extrabold
                                      text-emerald-600 mt-2">

                                {{ $confirmedCount }}

                            </p>

                            <p class="text-xs text-slate-400 mt-1">

                                Scheduled students

                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-emerald-50
                                    flex items-center justify-center">

                            <svg class="w-6 h-6 text-emerald-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M5 13l4 4L19 7"/>

                            </svg>

                        </div>

                    </div>

                </div>


                {{-- RESCHEDULE --}}
                <div class="bg-white rounded-2xl border border-orange-200
                            shadow-sm p-5
                            hover:shadow-md transition">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wider text-slate-400">

                                For Reschedule

                            </p>

                            <p class="text-3xl font-extrabold
                                      text-orange-600 mt-2">

                                {{ $rescheduleCount }}

                            </p>

                            <p class="text-xs text-slate-400 mt-1">

                                Needs new schedule

                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-orange-50
                                    flex items-center justify-center">

                            <svg class="w-6 h-6 text-orange-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 8V4
                                         m0 0l-3 3m3-3l3 3
                                         M6 12a6 6 0 1012 0"/>

                            </svg>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                SEARCH & FILTER
            ========================================================== --}}
            <div class="bg-white rounded-2xl
                        border border-slate-200
                        shadow-sm p-5 mb-7">

                <div class="flex items-center gap-2 mb-4">

                    <div class="w-8 h-8 rounded-lg bg-blue-50
                                flex items-center justify-center">

                        <svg class="w-4 h-4 text-blue-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L2.293 6.707A1 1 0 013 6V4z"/>

                        </svg>

                    </div>

                    <div>

                        <h2 class="text-sm font-extrabold text-slate-900">

                            Search & Filter

                        </h2>

                        <p class="text-xs text-slate-400">

                            Find appointments quickly

                        </p>

                    </div>

                </div>


                <form method="GET"
                      action="{{ route('registrar.appointments.index') }}"
                      class="grid grid-cols-1 lg:grid-cols-12 gap-3">

                    {{-- SEARCH --}}
                    <div class="lg:col-span-6 relative">

                        <svg class="absolute left-3.5 top-1/2
                                    -translate-y-1/2
                                    w-5 h-5 text-slate-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M21 21l-4.35-4.35
                                     m2.35-5.65a7 7 0 11-14 0
                                     7 7 0 0114 0z"/>

                        </svg>

                        <input
                            type="text"
                            placeholder="Search student name or reference number..."
                            class="w-full pl-11 pr-4 py-3
                                   rounded-xl
                                   border-slate-200
                                   bg-slate-50
                                   text-sm
                                   focus:bg-white
                                   focus:border-blue-500
                                   focus:ring-blue-500">

                    </div>


                    {{-- STATUS --}}
                    <div class="lg:col-span-3">

                        <select
                            name="status"
                            onchange="this.form.submit()"
                            class="w-full rounded-xl
                                   border-slate-200
                                   bg-slate-50
                                   text-sm text-slate-600
                                   focus:bg-white
                                   focus:border-blue-500
                                   focus:ring-blue-500">

                            <option value="all" @selected(! $statusFilter || $statusFilter === 'all')>
                                All Status
                            </option>

                            <option value="pending" @selected($statusFilter === 'pending')>
                                Pending
                            </option>

                            <option value="approved" @selected($statusFilter === 'approved')>
                                Approved
                            </option>

                            <option value="rescheduled" @selected($statusFilter === 'rescheduled')>
                                Rescheduled
                            </option>

                            <option value="cancelled" @selected($statusFilter === 'cancelled')>
                                Cancelled
                            </option>

                        </select>

                    </div>


                    {{-- DATE --}}
                    <div class="lg:col-span-3">

                        <input
                            type="date"
                            class="w-full rounded-xl
                                   border-slate-200
                                   bg-slate-50
                                   text-sm text-slate-600
                                   focus:bg-white
                                   focus:border-blue-500
                                   focus:ring-blue-500">

                    </div>

                </form>

            </div>


            {{-- =========================================================
                APPOINTMENT LIST
            ========================================================== --}}
            <div class="mb-4">

                <h2 class="text-lg font-extrabold text-slate-900">

                    Incoming Appointments

                </h2>

                <p class="text-sm text-slate-500 mt-1">

                    Review and manage student appointment transactions.

                </p>

            </div>


            <div class="bg-white rounded-2xl
                        border border-slate-200
                        shadow-sm overflow-hidden">


                {{-- TABLE HEADER --}}
                <div class="hidden lg:grid
                            grid-cols-12 gap-4
                            px-6 py-4
                            bg-slate-50
                            border-b border-slate-200
                            text-[11px]
                            font-extrabold
                            uppercase
                            tracking-wider
                            text-slate-400">

                    <div class="col-span-4">

                        Student / Appointment

                    </div>

                    <div class="col-span-3">

                        Schedule

                    </div>

                    <div class="col-span-2">

                        Status

                    </div>

                    <div class="col-span-3 text-right">

                        Actions

                    </div>

                </div>


                {{-- =====================================================
                    APPOINTMENTS
                ====================================================== --}}
                @forelse ($appointments as $appointment)

                    <div class="group
                                px-5 lg:px-6
                                py-5
                                border-b border-slate-100
                                last:border-0
                                hover:bg-blue-50/30
                                transition">


                        <div class="grid grid-cols-1
                                    lg:grid-cols-12
                                    gap-5
                                    items-center">


                            {{-- =================================================
                                STUDENT
                            ================================================= --}}
                            <a href="{{ route('registrar.appointments.show', $appointment) }}"
                               class="lg:col-span-4 min-w-0">

                                <div class="flex items-start gap-3">


                                    {{-- Avatar --}}
                                    <div class="w-11 h-11
                                                rounded-xl
                                                bg-blue-100
                                                text-blue-800
                                                flex items-center
                                                justify-center
                                                font-extrabold
                                                text-sm
                                                shrink-0">

                                        {{ strtoupper(substr($appointment->user->name, 0, 1)) }}

                                    </div>


                                    <div class="min-w-0">

                                        <div class="flex items-center
                                                    gap-2 flex-wrap">

                                            <p class="font-bold
                                                      text-slate-900
                                                      text-sm">

                                                {{ $appointment->user->name }}

                                            </p>


                                            <span class="text-[10px]
                                                         font-bold
                                                         uppercase
                                                         tracking-wide
                                                         text-blue-700
                                                         bg-blue-50
                                                         px-2.5 py-1
                                                         rounded-full">

                                                {{ $appointment->reference_code }}

                                            </span>

                                        </div>


                                        <p class="text-xs
                                                  text-slate-500
                                                  mt-1">

                                            {{ $appointment->office }}

                                        </p>


                                        <p class="text-xs
                                                  text-slate-400
                                                  mt-1
                                                  truncate">

                                            {{ $appointment->purpose }}

                                        </p>

                                    </div>

                                </div>

                            </a>


                            {{-- =================================================
                                SCHEDULE
                            ================================================== --}}
                            <div class="lg:col-span-3">

                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10
                                                rounded-xl
                                                bg-blue-50
                                                flex items-center
                                                justify-center
                                                shrink-0">

                                        <svg class="w-5 h-5 text-blue-700"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 4h10
                                                     M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z"/>

                                        </svg>

                                    </div>


                                    <div>

                                        <p class="text-sm
                                                  font-bold
                                                  text-slate-800">

                                            {{ $appointment->date->format('M j, Y') }}

                                        </p>

                                        <p class="text-xs
                                                  text-slate-500
                                                  mt-0.5">

                                            {{ $appointment->timeToCome() }}

                                        </p>

                                    </div>

                                </div>


                                @if ($appointment->original_date)

                                    <div class="mt-2 ml-13">

                                        <span class="inline-flex
                                                     items-center
                                                     px-2.5 py-1
                                                     rounded-lg
                                                     bg-indigo-50
                                                     text-indigo-600
                                                     text-[11px]
                                                     font-semibold">

                                            Rescheduled from
                                            {{ $appointment->original_date->format('M j, Y') }}

                                        </span>

                                    </div>

                                @endif

                            </div>


                            {{-- =================================================
                                STATUS
                            ================================================== --}}
                            <div class="lg:col-span-2">

                                <span class="inline-flex
                                             items-center
                                             gap-1.5
                                             px-3 py-1.5
                                             rounded-full
                                             text-xs
                                             font-bold

                                    @if ($appointment->status === 'confirmed')
                                        bg-emerald-50
                                        text-emerald-700
                                        ring-1 ring-emerald-200

                                    @elseif ($appointment->status === 'checked_in')
                                        bg-cyan-50
                                        text-cyan-700
                                        ring-1 ring-cyan-200

                                    @elseif ($appointment->status === 'rescheduled')
                                        bg-indigo-50
                                        text-indigo-700
                                        ring-1 ring-indigo-200

                                    @elseif ($appointment->status === 'for_reschedule')
                                        bg-orange-50
                                        text-orange-700
                                        ring-1 ring-orange-200

                                    @elseif ($appointment->status === 'completed')
                                        bg-blue-50
                                        text-blue-700
                                        ring-1 ring-blue-200

                                    @elseif ($appointment->status === 'cancelled')
                                        bg-red-50
                                        text-red-600
                                        ring-1 ring-red-200

                                    @elseif ($appointment->status === 'no_show')
                                        bg-slate-100
                                        text-slate-500
                                        ring-1 ring-slate-200

                                    @else
                                        bg-red-50
                                        text-red-700
                                        ring-1 ring-red-200
                                    @endif
                                ">

                                    <span class="w-1.5 h-1.5
                                                 rounded-full
                                                 bg-current">
                                    </span>


                                    {{ \App\Enums\AppointmentStatus::tryFrom($appointment->status)?->label() ?? ucfirst($appointment->status) }}

                                </span>

                            </div>


                            {{-- =================================================
                                ACTIONS
                            ================================================== --}}
                            <div class="lg:col-span-3
                                        flex flex-wrap
                                        lg:justify-end
                                        gap-2">


                                @unless (
                                    in_array(
                                        $appointment->status,
                                        ['completed', 'cancelled', 'no_show']
                                    )
                                )

                                    <button
                                        type="button"

                                        @click="$dispatch('open-reschedule', {
                                            id: {{ $appointment->id }},
                                            student: @js($appointment->user->name),
                                            studentId: @js($appointment->user->studentProfile?->student_id),
                                            reference: @js($appointment->reference_code),
                                            office: @js($appointment->office),
                                            dateLabel: @js($appointment->date->format('F j, Y')),
                                            timeSlot: @js($appointment->timeToCome()),
                                            reason: @js($appointment->reschedule_reason)
                                        })"

                                        class="inline-flex
                                               items-center
                                               justify-center
                                               gap-1.5
                                               px-3.5 py-2.5
                                               bg-indigo-50
                                               border border-indigo-200
                                               text-indigo-700
                                               text-xs
                                               font-bold
                                               rounded-xl
                                               hover:bg-indigo-100
                                               transition">

                                        <svg class="w-4 h-4"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 4h10
                                                     M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2v12a2 2 0 002 2z"/>

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 15l2 2 4-4"/>

                                        </svg>

                                        Reschedule

                                    </button>

                                @endunless


                                {{-- VIEW DETAILS --}}
                                <a href="{{ route('registrar.appointments.show', $appointment) }}"
                                   class="inline-flex
                                          items-center
                                          justify-center
                                          gap-1.5
                                          px-3.5 py-2.5
                                          bg-blue-700
                                          text-white
                                          text-xs
                                          font-bold
                                          rounded-xl
                                          hover:bg-blue-800
                                          transition
                                          shadow-sm">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0
                                                 3 3 0 016 0z"/>

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M2.458 12C3.732 7.943
                                                 7.523 5 12 5
                                                 c4.478 0 8.268 2.943
                                                 9.542 7
                                                 -1.274 4.057
                                                 -5.064 7
                                                 -9.542 7
                                                 -4.477 0
                                                 -8.268-2.943
                                                 -9.542-7z"/>

                                    </svg>

                                    View Details

                                </a>

                            </div>

                        </div>

                    </div>

                @empty

                    {{-- EMPTY STATE --}}
                    <div class="py-20 text-center">

                        <div class="w-16 h-16 mx-auto
                                    rounded-2xl
                                    bg-blue-50
                                    flex items-center
                                    justify-center
                                    mb-4">

                            <svg class="w-8 h-8 text-blue-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 4h10
                                         M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z"/>

                            </svg>

                        </div>


                        <p class="font-bold text-slate-700">

                            No appointments found

                        </p>


                        <p class="text-sm text-slate-400 mt-1">

                            There are currently no appointments matching your criteria.

                        </p>

                    </div>

                @endforelse

            </div>


            {{-- =========================================================
                PAGINATION
            ========================================================== --}}
            <div class="mt-6">

                {{ $appointments->links() }}

            </div>

        </div>

    </div>


    {{-- =========================================================
        RESCHEDULE MODAL
    ========================================================== --}}
    @include('registrar.appointments.partials.reschedule-modal')


</x-app-layout>