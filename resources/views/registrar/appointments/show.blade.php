<x-app-layout>

    <div class="min-h-screen bg-[#f4f7fb] py-8">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                BACK TO APPOINTMENTS
            ========================================================== --}}
            <a href="{{ route('registrar.appointments.index') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold
                      text-slate-500 hover:text-blue-800 transition">

                <span class="w-8 h-8 rounded-lg bg-white border border-slate-200
                             flex items-center justify-center shadow-sm">

                    <svg class="w-4 h-4"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 19l-7-7 7-7"/>

                    </svg>

                </span>

                Back to Appointments
            </a>


            {{-- =========================================================
                SUCCESS MESSAGE
            ========================================================== --}}
            @if (session('status'))

                <div class="mt-5 flex items-start gap-3
                            bg-emerald-50 border border-emerald-200
                            rounded-2xl px-5 py-4">

                    <div class="w-8 h-8 rounded-lg bg-emerald-100
                                flex items-center justify-center shrink-0">

                        <svg class="w-4 h-4 text-emerald-600"
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
                        <p class="text-xs font-bold uppercase tracking-wider
                                  text-emerald-600">
                            Success
                        </p>

                        <p class="text-sm font-semibold text-emerald-800 mt-0.5">
                            {{ session('status') }}
                        </p>
                    </div>

                </div>

            @endif


            {{-- =========================================================
                MAIN HEADER
            ========================================================== --}}
            <div class="mt-6 bg-white rounded-3xl border border-slate-200
                        shadow-sm overflow-hidden">

                {{-- Blue Header --}}
                <div class="relative overflow-hidden
                            bg-gradient-to-br from-blue-950
                            via-blue-900 to-indigo-900">

                    {{-- Decorative circles --}}
                    <div class="absolute -right-16 -top-20
                                w-64 h-64 rounded-full
                                bg-yellow-400/10"></div>

                    <div class="absolute right-24 -bottom-24
                                w-48 h-48 rounded-full
                                bg-white/5"></div>

                    <div class="relative p-6 sm:p-8">

                        <div class="flex flex-col lg:flex-row
                                    lg:items-center lg:justify-between gap-6">

                            {{-- Student --}}
                            <div class="flex items-center gap-4">

                                <div class="relative">

                                    <div class="w-16 h-16 rounded-2xl
                                                bg-white/10
                                                border border-white/20
                                                text-white
                                                flex items-center justify-center
                                                text-xl font-extrabold">

                                        {{ strtoupper(substr($appointment->user->name, 0, 1)) }}

                                    </div>

                                    {{-- Active indicator --}}
                                    <span class="absolute -right-1 -bottom-1
                                                 w-4 h-4 rounded-full
                                                 bg-emerald-400
                                                 border-2 border-blue-900">
                                    </span>

                                </div>


                                <div>

                                    <div class="flex flex-wrap items-center gap-2">

                                        <h1 class="text-2xl sm:text-3xl
                                                   font-extrabold text-white">

                                            {{ $appointment->user->name }}

                                        </h1>

                                    </div>


                                    <div class="flex flex-wrap items-center gap-2 mt-2">

                                        <span class="px-2.5 py-1 rounded-lg
                                                     bg-white/10
                                                     border border-white/10
                                                     text-[10px] font-bold
                                                     tracking-wider text-blue-100">

                                            {{ $appointment->reference_code }}

                                        </span>

                                        <span class="text-xs text-blue-200">
                                            Student Appointment
                                        </span>

                                    </div>


                                    <p class="text-xs text-blue-200 mt-2">
                                        {{ $appointment->user->email }}
                                    </p>

                                </div>

                            </div>


                            {{-- STATUS --}}
                            <div class="lg:text-right">

                                <p class="text-[10px] font-bold uppercase
                                          tracking-[0.18em] text-blue-200 mb-2">

                                    Appointment Status

                                </p>


                                <span class="inline-flex items-center gap-2
                                             px-4 py-2 rounded-full
                                             text-xs font-bold shadow-sm

                                    @if ($appointment->status === 'confirmed')
                                        bg-emerald-400 text-emerald-950

                                    @elseif ($appointment->status === 'checked_in')
                                        bg-cyan-400 text-cyan-950

                                    @elseif ($appointment->status === 'rescheduled')
                                        bg-indigo-300 text-indigo-950

                                    @elseif ($appointment->status === 'for_reschedule')
                                        bg-orange-300 text-orange-950

                                    @elseif ($appointment->status === 'completed')
                                        bg-blue-300 text-blue-950

                                    @elseif ($appointment->status === 'cancelled')
                                        bg-red-300 text-red-950

                                    @elseif ($appointment->status === 'no_show')
                                        bg-slate-300 text-slate-800

                                    @else
                                        bg-yellow-300 text-yellow-950
                                    @endif
                                ">

                                    <span class="w-2 h-2 rounded-full bg-current"></span>

                                    {{ \App\Enums\AppointmentStatus::tryFrom($appointment->status)?->label() ?? ucfirst($appointment->status) }}

                                </span>

                            </div>

                        </div>

                    </div>
                </div>


                {{-- =====================================================
                    ACTION BAR
                ====================================================== --}}
                <div class="px-6 sm:px-8 py-4 bg-white
                            border-t border-slate-100">

                    <div class="flex flex-wrap items-center gap-2">

                        {{-- Approve --}}
                        @if (in_array($appointment->status, ['pending', 'for_reschedule']))

                            <form method="POST"
                                  action="{{ route('registrar.appointments.confirm', $appointment) }}"
                                  data-confirm="Confirm this appointment for the selected slot?"
                                  data-confirm-ok="Yes, confirm"
                                  data-confirm-icon="question"
                                  data-confirm-title="Confirm appointment?">

                                @csrf

                                <button type="submit"
                                        class="inline-flex items-center gap-2
                                               px-4 py-2.5 rounded-xl
                                               bg-blue-800 text-white
                                               text-xs font-bold
                                               hover:bg-blue-900
                                               shadow-sm hover:shadow-md
                                               transition">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M5 13l4 4L19 7"/>

                                    </svg>

                                    Approve Appointment

                                </button>

                            </form>

                        @endif


                        {{-- Reschedule --}}
                        @unless (in_array($appointment->status, ['completed', 'cancelled', 'no_show']))

                            <button
                                type="button"

                                @click="$dispatch('open-reschedule', {
                                    id: {{ $appointment->id }},
                                    student: @js($appointment->user->name),
                                    studentId: @js($appointment->user->studentProfile?->student_id),
                                    reference: @js($appointment->reference_code),
                                    office: @js($appointment->office),
                                    dateLabel: @js($appointment->date->format('F j, Y')),
                                    timeSlot: @js($appointment->time_slot),
                                    reason: @js($appointment->reschedule_reason)
                                })"

                                class="inline-flex items-center gap-2
                                       px-4 py-2.5 rounded-xl
                                       bg-indigo-50
                                       border border-indigo-200
                                       text-indigo-700
                                       text-xs font-bold
                                       hover:bg-indigo-100
                                       transition">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 4h10M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2v11a2 2 0 002 2z"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 15l2 2 4-4"/>

                                </svg>

                                Reschedule

                            </button>

                        @endunless


                        {{-- Cancel --}}
                        @unless (in_array($appointment->status, ['completed', 'cancelled']))

                            <form method="POST"
                                  action="{{ route('registrar.appointments.cancel', $appointment) }}"
                                  data-confirm="This will cancel the student's appointment and notify them."
                                  data-confirm-ok="Yes, cancel it">

                                @csrf

                                <button type="submit"
                                        class="inline-flex items-center gap-2
                                               px-4 py-2.5 rounded-xl
                                               bg-white border border-red-200
                                               text-red-600
                                               text-xs font-bold
                                               hover:bg-red-50
                                               transition">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12"/>

                                    </svg>

                                    Cancel Appointment

                                </button>

                            </form>

                        @endunless

                    </div>

                </div>

            </div>


            {{-- =========================================================
                CONTENT GRID
            ========================================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">


                {{-- =====================================================
                    LEFT
                ====================================================== --}}
                <div class="lg:col-span-2 space-y-6">


                    {{-- =================================================
                        CURRENT SCHEDULE
                    ================================================== --}}
                    <div class="relative overflow-hidden
                                rounded-3xl shadow-lg
                                bg-gradient-to-br from-blue-950
                                via-blue-900 to-indigo-900">

                        {{-- Yellow decorative accent --}}
                        <div class="absolute -right-16 -top-16
                                    w-48 h-48 rounded-full
                                    bg-yellow-400/10"></div>

                        <div class="absolute right-20 -bottom-20
                                    w-56 h-56 rounded-full
                                    bg-white/5"></div>


                        <div class="relative p-7">

                            <div class="flex items-center gap-3 mb-7">

                                <div class="w-10 h-10 rounded-xl
                                            bg-yellow-400
                                            text-blue-950
                                            flex items-center justify-center
                                            shadow-lg">

                                    <svg class="w-5 h-5"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M8 7V3m8 4V3m-9 4h10M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2v11a2 2 0 002 2z"/>

                                    </svg>

                                </div>

                                <div>

                                    <p class="text-[11px] font-bold uppercase
                                              tracking-[0.18em] text-blue-200">

                                        Current Schedule

                                    </p>

                                    <p class="text-xs text-blue-100 mt-0.5">
                                        Confirmed appointment information
                                    </p>

                                </div>

                            </div>


                            <div class="grid sm:grid-cols-2 gap-5">

                                {{-- Date --}}
                                <div class="rounded-2xl
                                            bg-white/10
                                            border border-white/10
                                            p-5">

                                    <p class="text-xs text-blue-200">
                                        Appointment Date
                                    </p>

                                    <p class="text-xl sm:text-2xl
                                              font-extrabold text-white mt-2">

                                        {{ $appointment->date->format('F j, Y') }}

                                    </p>

                                </div>


                                {{-- Time --}}
                                <div class="rounded-2xl
                                            bg-white/10
                                            border border-white/10
                                            p-5">

                                    <p class="text-xs text-blue-200">
                                        Time Slot
                                    </p>

                                    <p class="text-xl sm:text-2xl
                                              font-extrabold text-white mt-2">

                                        {{ $appointment->time_slot }}

                                    </p>

                                </div>

                            </div>


                            <div class="grid sm:grid-cols-2 gap-5 mt-5">

                                {{-- Office --}}
                                <div class="pt-5 border-t border-white/10">

                                    <p class="text-xs text-blue-200">
                                        Office
                                    </p>

                                    <p class="text-sm font-bold text-white mt-1">
                                        {{ $appointment->office }}
                                    </p>

                                </div>


                                {{-- Capacity --}}
                                <div class="pt-5 border-t border-white/10">

                                    <p class="text-xs text-blue-200">
                                        Slot Capacity
                                    </p>

                                    <p class="text-sm font-bold text-white mt-1">

                                        {{ \App\Models\Appointment::SLOT_LIMITS[$appointment->office] ?? 6 }}

                                        students per slot

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        APPOINTMENT DETAILS
                    ================================================== --}}
                    <div class="bg-white rounded-3xl border border-slate-200
                                shadow-sm overflow-hidden">

                        <div class="px-6 py-5 border-b border-slate-100">

                            <div class="flex items-center gap-3">

                                <div class="w-10 h-10 rounded-xl
                                            bg-blue-50
                                            flex items-center justify-center">

                                    <svg class="w-5 h-5 text-blue-700"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>

                                    </svg>

                                </div>

                                <div>

                                    <h2 class="font-bold text-slate-900">
                                        Appointment Details
                                    </h2>

                                    <p class="text-xs text-slate-400 mt-0.5">
                                        Information submitted by the student
                                    </p>

                                </div>

                            </div>

                        </div>


                        <div class="p-6">

                            <dl class="grid sm:grid-cols-2 gap-x-8 gap-y-7">

                                <div>
                                    <dt class="text-[10px] font-bold uppercase
                                              tracking-[0.15em] text-slate-400">
                                        Student ID
                                    </dt>

                                    <dd class="mt-1.5 text-sm font-semibold text-slate-800">
                                        {{ $appointment->user->studentProfile?->student_id ?? '—' }}
                                    </dd>
                                </div>


                                <div>
                                    <dt class="text-[10px] font-bold uppercase
                                              tracking-[0.15em] text-slate-400">
                                        Email Address
                                    </dt>

                                    <dd class="mt-1.5 text-sm font-semibold
                                              text-slate-800 break-all">
                                        {{ $appointment->user->email }}
                                    </dd>
                                </div>


                                <div>
                                    <dt class="text-[10px] font-bold uppercase
                                              tracking-[0.15em] text-slate-400">
                                        Target Office
                                    </dt>

                                    <dd class="mt-1.5 text-sm font-semibold text-slate-800">
                                        {{ $appointment->office }}
                                    </dd>
                                </div>


                                <div>
                                    <dt class="text-[10px] font-bold uppercase
                                              tracking-[0.15em] text-slate-400">
                                        Purpose
                                    </dt>

                                    <dd class="mt-1.5 text-sm font-semibold text-slate-800">
                                        {{ $appointment->purpose }}
                                    </dd>
                                </div>


                                @if ($appointment->notes)

                                    <div class="sm:col-span-2">

                                        <dt class="text-[10px] font-bold uppercase
                                                  tracking-[0.15em] text-slate-400">

                                            Student Notes

                                        </dt>

                                        <dd class="mt-2 p-4
                                                   bg-slate-50
                                                   border border-slate-100
                                                   rounded-2xl
                                                   text-sm text-slate-600
                                                   leading-relaxed">

                                            {{ $appointment->notes }}

                                        </dd>

                                    </div>

                                @endif

                            </dl>

                        </div>

                    </div>


                    {{-- =================================================
                        RESCHEDULE HISTORY
                    ================================================== --}}
                    @if ($appointment->rescheduled_at)

                        <div class="bg-white rounded-3xl
                                    border border-indigo-100
                                    shadow-sm overflow-hidden">

                            <div class="px-6 py-5
                                        bg-indigo-50
                                        border-b border-indigo-100">

                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 rounded-xl
                                                bg-white
                                                flex items-center justify-center
                                                shadow-sm">

                                        <svg class="w-5 h-5 text-indigo-700"
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
                                                  d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>

                                        </svg>

                                    </div>

                                    <div>

                                        <h2 class="font-bold text-indigo-900">
                                            Reschedule History
                                        </h2>

                                        <p class="text-xs text-indigo-600 mt-0.5">
                                            Previous appointment information
                                        </p>

                                    </div>

                                </div>

                            </div>


                            <div class="p-6">

                                <div class="relative pl-8">

                                    <div class="absolute left-2 top-1 bottom-1
                                                w-px bg-indigo-200"></div>

                                    <div class="absolute left-0 top-1
                                                w-5 h-5 rounded-full
                                                bg-indigo-100
                                                border-4 border-white
                                                ring-1 ring-indigo-200">
                                    </div>


                                    <p class="text-[10px] font-bold uppercase
                                              tracking-[0.15em] text-slate-400">

                                        Rescheduled

                                    </p>


                                    <p class="text-sm text-slate-700 mt-2 leading-relaxed">

                                        Moved from

                                        <strong class="text-slate-900">

                                            {{ $appointment->original_date?->format('F j, Y') }}

                                            ·

                                            {{ $appointment->original_time_slot ?? '—' }}

                                        </strong>

                                        to

                                        <strong class="text-indigo-700">

                                            {{ $appointment->date->format('F j, Y') }}

                                            ·

                                            {{ $appointment->time_slot }}

                                        </strong>

                                    </p>


                                    <p class="text-xs text-slate-400 mt-2">

                                        {{ $appointment->rescheduled_at->format('M j, Y g:i A') }}

                                    </p>


                                    @if ($appointment->reschedule_reason)

                                        <div class="mt-4 p-4 rounded-2xl
                                                    bg-indigo-50
                                                    border border-indigo-100">

                                            <p class="text-[10px] font-bold uppercase
                                                      tracking-wider text-indigo-500">

                                                Reason

                                            </p>

                                            <p class="text-sm text-indigo-800 mt-1">
                                                {{ $appointment->reschedule_reason }}
                                            </p>

                                        </div>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @endif

                </div>


                {{-- =====================================================
                    RIGHT SIDEBAR
                ====================================================== --}}
                <div class="space-y-6">


                    {{-- Appointment Summary --}}
                    <div class="bg-white rounded-3xl
                                border border-slate-200
                                shadow-sm overflow-hidden">

                        <div class="px-5 py-4
                                    bg-blue-950">

                            <div class="flex items-center gap-2">

                                <div class="w-8 h-8 rounded-lg
                                            bg-yellow-400
                                            flex items-center justify-center">

                                    <svg class="w-4 h-4 text-blue-950"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>

                                    </svg>

                                </div>

                                <p class="text-xs font-bold uppercase
                                          tracking-wider text-white">

                                    Appointment Summary

                                </p>

                            </div>

                        </div>


                        <div class="p-5 space-y-5">

                            <div>

                                <p class="text-[10px] font-bold uppercase
                                          tracking-wider text-slate-400">

                                    Reference Number

                                </p>

                                <p class="text-sm font-bold text-slate-800 mt-1">

                                    {{ $appointment->reference_code }}

                                </p>

                            </div>


                            <div>

                                <p class="text-[10px] font-bold uppercase
                                          tracking-wider text-slate-400">

                                    Office

                                </p>

                                <p class="text-sm font-bold text-slate-800 mt-1">

                                    {{ $appointment->office }}

                                </p>

                            </div>


                            <div>

                                <p class="text-[10px] font-bold uppercase
                                          tracking-wider text-slate-400">

                                    Purpose

                                </p>

                                <p class="text-sm font-bold text-slate-800 mt-1">

                                    {{ $appointment->purpose }}

                                </p>

                            </div>


                            <div class="pt-4 border-t border-slate-100">

                                <div class="flex items-center justify-between">

                                    <div>

                                        <p class="text-[10px] font-bold uppercase
                                                  tracking-wider text-slate-400">

                                            Slot Capacity

                                        </p>

                                        <p class="text-xs text-slate-400 mt-1">
                                            Maximum students
                                        </p>

                                    </div>


                                    <span class="px-3 py-1.5 rounded-xl
                                                 bg-blue-50
                                                 text-blue-800
                                                 text-sm font-extrabold">

                                        {{ \App\Models\Appointment::SLOT_LIMITS[$appointment->office] ?? 6 }}

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Student Notification --}}
                    <div class="relative overflow-hidden
                                bg-gradient-to-br
                                from-blue-50 to-indigo-50
                                rounded-3xl
                                border border-blue-100 p-5">

                        <div class="absolute -right-8 -top-8
                                    w-24 h-24 rounded-full
                                    bg-blue-200/30"></div>

                        <div class="relative flex gap-3">

                            <div class="w-10 h-10 rounded-xl
                                        bg-white
                                        border border-blue-100
                                        flex items-center justify-center
                                        shrink-0 shadow-sm">

                                <svg class="w-5 h-5 text-blue-700"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>

                                </svg>

                            </div>


                            <div>

                                <p class="text-sm font-bold text-blue-950">
                                    Student Notifications
                                </p>

                                <p class="text-xs text-blue-700 mt-1 leading-relaxed">

                                    The student will receive an email when the
                                    appointment is approved, rescheduled,
                                    or cancelled.

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- ISUFSTPASS Verification --}}
                    <div class="bg-white rounded-3xl
                                border border-slate-200
                                shadow-sm p-5">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10 rounded-xl
                                        bg-yellow-50
                                        flex items-center justify-center">

                                <svg class="w-5 h-5 text-yellow-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12l2 2 4-4m5.618-4.016A11.955
                                             11.955 0 0112 2.944a11.955
                                             11.955 0 01-8.618 3.04A12.02
                                             12.02 0 003 9c0 5.591 3.824
                                             10.29 9 11.622 5.176-1.332
                                             9-6.03 9-11.622 0-1.042-.133
                                             -2.052-.382-3.016z"/>

                                </svg>

                            </div>


                            <div>

                                <p class="text-sm font-bold text-slate-900">
                                    ISUFSTPASS Verified
                                </p>

                                <p class="text-xs text-slate-400 mt-0.5">
                                    Secure university transaction
                                </p>

                            </div>

                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-100">

                            <div class="flex items-center gap-2">

                                <span class="w-2 h-2 rounded-full
                                             bg-emerald-500"></span>

                                <span class="text-xs font-semibold
                                             text-slate-600">

                                    Appointment record is securely stored

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                ERROR
            ========================================================== --}}
            @if (session('error'))

                <div class="mt-6 flex items-start gap-3
                            bg-red-50 border border-red-200
                            text-red-700 rounded-2xl px-5 py-4">

                    <div class="w-8 h-8 rounded-lg bg-red-100
                                flex items-center justify-center shrink-0">

                        <svg class="w-4 h-4"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 9v2m0 4h.01M10.29 3.86l-7.82 14a1 1 0 00.87 1.5h17.32a1 1 0 00.87-1.5l-7.82-14-1.74 0z"/>

                        </svg>

                    </div>

                    <p class="text-sm font-semibold mt-1">
                        {{ session('error') }}
                    </p>

                </div>

            @endif

        </div>
    </div>


    {{-- Existing Reschedule Modal --}}
    @include('registrar.appointments.partials.reschedule-modal')

</x-app-layout>