<x-app-layout>

    @php
        $jsAppointments = $appointments->map(fn($a) => [
            'id' => $a->id,
            'reference_code' => $a->reference_code,
            'purpose' => $a->purpose,
            'date' => $a->date->format('M j, Y'),
            'raw_date' => $a->date->format('Y-m-d'),
            'time_slot' => $a->time_slot,
            'status' => $a->status,
            'reschedule_reason' => $a->reschedule_reason,
            'original_time_slot' => $a->original_time_slot,
            'student_name' => $a->user->name,
            'student_email' => $a->user->email,
        ])->values();

        $totalAppointments = $appointments->total();
        $pendingCount = $appointments->where('status', 'pending')->count();
        $confirmedCount = $appointments->where('status', 'confirmed')->count();
        $completedCount = $appointments->where('status', 'completed')->count();
    @endphp


    <div
        class="min-h-screen bg-slate-50 py-8"
        x-data="appointmentManager()"
    >

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">


            {{-- ================================================= --}}
            {{-- HEADER --}}
            {{-- ================================================= --}}

            <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <div class="mb-2 flex items-center gap-2 text-sm text-blue-600">
                        <a
                            href="{{ url()->previous() }}"
                            class="font-semibold hover:text-blue-800"
                        >
                            ← Back
                        </a>

                        <span class="text-slate-300">/</span>

                        <span class="text-slate-500">
                            Appointments
                        </span>
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">
                        {{ $office }} Appointments
                    </h1>

                    <p class="mt-2 text-base text-slate-500">
                        Manage and reschedule student appointments for {{ $office }}.
                    </p>

                </div>


                {{-- Office Badge --}}
                <div
                    class="flex w-fit items-center gap-3 rounded-2xl border border-blue-100 bg-blue-50 px-5 py-3"
                >

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white">

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>

                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                            Office
                        </p>

                        <p class="font-bold text-slate-900">
                            {{ $office }}
                        </p>
                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- STAT CARDS --}}
            {{-- ================================================= --}}

            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">


                {{-- TOTAL --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-medium text-slate-500">
                                Total Appointments
                            </p>

                            <p class="mt-2 text-3xl font-extrabold text-slate-900">
                                {{ $totalAppointments }}
                            </p>

                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">

                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>

                        </div>

                    </div>

                </div>


                {{-- PENDING --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-medium text-slate-500">
                                Pending
                            </p>

                            <p class="mt-2 text-3xl font-extrabold text-slate-900">
                                {{ $pendingCount }}
                            </p>

                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">

                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>

                        </div>

                    </div>

                </div>


                {{-- CONFIRMED --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-medium text-slate-500">
                                Confirmed
                            </p>

                            <p class="mt-2 text-3xl font-extrabold text-slate-900">
                                {{ $confirmedCount }}
                            </p>

                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 text-green-600">

                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 13l4 4L19 7"/>
                            </svg>

                        </div>

                    </div>

                </div>


                {{-- COMPLETED --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-medium text-slate-500">
                                Completed
                            </p>

                            <p class="mt-2 text-3xl font-extrabold text-slate-900">
                                {{ $completedCount }}
                            </p>

                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">

                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m5.5-1.5a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/>
                            </svg>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- APPOINTMENTS --}}
            {{-- ================================================= --}}

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">


                {{-- TABLE HEADER --}}
                <div class="border-b border-slate-200 px-6 py-5">

                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

                        <div>

                            <h2 class="text-lg font-bold text-slate-900">
                                Appointment Requests
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                View student appointment information and manage schedules.
                            </p>

                        </div>

                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500">
                            {{ $appointments->total() }} records
                        </div>

                    </div>

                </div>


                {{-- DESKTOP TABLE --}}
                <div class="hidden overflow-x-auto lg:block">

                    <table class="w-full">

                        <thead class="bg-slate-50">

                            <tr class="border-b border-slate-200">

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Student
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Appointment
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Reference
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Status
                                </th>

                                <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100">

                            @forelse ($appointments as $appointment)

                                <tr class="transition hover:bg-blue-50/30">

                                    {{-- STUDENT --}}
                                    <td class="px-6 py-5">

                                        <div class="flex items-center gap-3">

                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">

                                                {{ strtoupper(substr($appointment->user->name, 0, 2)) }}

                                            </div>

                                            <div class="min-w-0">

                                                <p class="font-bold text-slate-900">
                                                    {{ $appointment->user->name }}
                                                </p>

                                                <p class="mt-0.5 max-w-[220px] truncate text-xs text-slate-500">
                                                    {{ $appointment->user->email }}
                                                </p>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- APPOINTMENT --}}
                                    <td class="px-6 py-5">

                                        <p class="font-semibold text-slate-900">
                                            {{ $appointment->purpose }}
                                        </p>

                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500">

                                            <span>
                                                {{ $appointment->date->format('M j, Y') }}
                                            </span>

                                            <span class="text-slate-300">
                                                •
                                            </span>

                                            <span>
                                                {{ $appointment->time_slot }}
                                            </span>

                                        </div>

                                    </td>


                                    {{-- REFERENCE --}}
                                    <td class="px-6 py-5">

                                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-bold text-slate-600">
                                            {{ $appointment->reference_code }}
                                        </span>

                                    </td>


                                    {{-- STATUS --}}
                                    <td class="px-6 py-5">

                                        @php
                                            $statusClasses = match($appointment->status) {
                                                'pending' =>
                                                    'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                                'confirmed' =>
                                                    'bg-green-50 text-green-700 ring-1 ring-green-200',
                                                'completed' =>
                                                    'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
                                                'cancelled' =>
                                                    'bg-red-50 text-red-600 ring-1 ring-red-200',
                                                'rescheduled' =>
                                                    'bg-purple-50 text-purple-700 ring-1 ring-purple-200',
                                                default =>
                                                    'bg-slate-100 text-slate-600',
                                            };
                                        @endphp

                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide {{ $statusClasses }}">

                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>

                                            {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}

                                        </span>

                                    </td>


                                    {{-- ACTION --}}
                                    <td class="px-6 py-5 text-right">

                                        <button
                                            @click="openDetails(all[{{ $loop->index }}])"
                                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-blue-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50"
                                        >

                                            View Details

                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M9 5l7 7-7 7"
                                                />
                                            </svg>

                                        </button>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5" class="px-6 py-16 text-center">

                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">

                                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>

                                        </div>

                                        <h3 class="mt-4 font-bold text-slate-900">
                                            No appointments yet
                                        </h3>

                                        <p class="mt-1 text-sm text-slate-500">
                                            There are currently no appointments for {{ $office }}.
                                        </p>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- MOBILE CARDS --}}
                <div class="divide-y divide-slate-100 lg:hidden">

                    @forelse ($appointments as $appointment)

                        <div class="p-5">

                            <div class="flex items-start gap-3">

                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">
                                    {{ strtoupper(substr($appointment->user->name, 0, 2)) }}
                                </div>

                                <div class="min-w-0 flex-1">

                                    <div class="flex items-start justify-between gap-3">

                                        <div>
                                            <p class="font-bold text-slate-900">
                                                {{ $appointment->user->name }}
                                            </p>

                                            <p class="text-xs text-slate-500">
                                                {{ $appointment->user->email }}
                                            </p>
                                        </div>

                                    </div>

                                    <div class="mt-4 rounded-xl bg-slate-50 p-4">

                                        <p class="font-semibold text-slate-900">
                                            {{ $appointment->purpose }}
                                        </p>

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $appointment->date->format('M j, Y') }}
                                            •
                                            {{ $appointment->time_slot }}
                                        </p>

                                    </div>

                                    <div class="mt-4 flex items-center justify-between">

                                        <span class="text-xs font-bold text-slate-400">
                                            {{ $appointment->reference_code }}
                                        </span>

                                        <button
                                            @click="openDetails(all[{{ $loop->index }}])"
                                            class="font-semibold text-blue-600"
                                        >
                                            View Details →
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="px-6 py-16 text-center text-sm text-slate-400">
                            No appointments for {{ $office }} yet.
                        </div>

                    @endforelse

                </div>

            </div>


            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $appointments->links() }}
            </div>

        </div>


        {{-- ================================================= --}}
        {{-- DETAIL MODAL --}}
        {{-- ================================================= --}}

        <div
            x-show="selected"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex justify-center p-4 overflow-y-auto bg-slate-950/50 backdrop-blur-sm"
            @keydown.escape.window="selected = null"
            @click.self="selected = null"
        >

            <div
                x-show="selected"
                x-transition
                class="my-8 w-full max-w-xl overflow-y-auto rounded-3xl bg-white shadow-2xl"
            >

                {{-- MODAL HEADER --}}
                <div class="sticky top-0 z-10 border-b border-slate-100 bg-white px-6 py-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase tracking-wider text-blue-600">
                                Appointment
                            </p>

                            <h3 class="mt-1 text-xl font-extrabold text-slate-900">
                                Appointment Details
                            </h3>

                        </div>

                        <button
                            @click="selected = null"
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200"
                        >
                            &times;
                        </button>

                    </div>

                </div>


                <template x-if="selected">

                    <div class="p-6">


                        {{-- STUDENT PROFILE --}}
                        <div class="rounded-2xl bg-gradient-to-r from-blue-700 to-blue-600 p-5 text-white">

                            <div class="flex items-center gap-4">

                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-full bg-white/15 text-lg font-bold"
                                    x-text="selected.student_name.substring(0,2).toUpperCase()"
                                ></div>

                                <div>

                                    <p
                                        class="text-lg font-bold"
                                        x-text="selected.student_name"
                                    ></p>

                                    <p
                                        class="text-sm text-blue-100"
                                        x-text="selected.student_email"
                                    ></p>

                                </div>

                            </div>

                        </div>


                        {{-- APPOINTMENT INFO --}}
                        <div class="mt-5 rounded-2xl border border-slate-200">

                            <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">

                                <div class="p-4">

                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                        Date
                                    </p>

                                    <p
                                        class="mt-1 font-bold text-slate-900"
                                        x-text="selected.date"
                                    ></p>

                                </div>

                                <div class="p-4">

                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                        Time Slot
                                    </p>

                                    <p
                                        class="mt-1 font-bold text-slate-900"
                                        x-text="selected.time_slot"
                                    ></p>

                                </div>

                            </div>

                        </div>


                        {{-- DETAILS --}}
                        <div class="mt-5 space-y-4">

                            <div class="flex items-start justify-between gap-4">

                                <span class="text-sm text-slate-500">
                                    Reference Code
                                </span>

                                <span
                                    class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-slate-700"
                                    x-text="selected.reference_code"
                                ></span>

                            </div>


                            <div class="flex items-start justify-between gap-4">

                                <span class="text-sm text-slate-500">
                                    Purpose
                                </span>

                                <span
                                    class="text-right text-sm font-semibold text-slate-900"
                                    x-text="selected.purpose"
                                ></span>

                            </div>


                            <div class="flex items-center justify-between">

                                <span class="text-sm text-slate-500">
                                    Status
                                </span>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold uppercase"
                                    :class="statusClass(selected.status)"
                                    x-text="selected.status.replace('_', ' ')"
                                ></span>

                            </div>

                        </div>


                        {{-- RESCHEDULE INFORMATION --}}
                        <template x-if="selected.original_time_slot">

                            <div class="mt-5 rounded-xl border border-purple-100 bg-purple-50 p-4">

                                <p class="text-xs font-bold uppercase tracking-wider text-purple-600">
                                    Previous Time Slot
                                </p>

                                <p
                                    class="mt-1 font-semibold text-purple-900"
                                    x-text="selected.original_time_slot"
                                ></p>

                                <template x-if="selected.reschedule_reason">

                                    <div class="mt-3">

                                        <p class="text-xs font-bold uppercase tracking-wider text-purple-600">
                                            Reschedule Reason
                                        </p>

                                        <p
                                            class="mt-1 text-sm text-purple-900"
                                            x-text="selected.reschedule_reason"
                                        ></p>

                                    </div>

                                </template>

                            </div>

                        </template>


                        {{-- ACTIONS --}}
                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">


                            {{-- RESCHEDULE --}}
                            <button
                                x-show="selected.status === 'pending' || selected.status === 'confirmed'"
                                @click="openReschedule()"
                                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700"
                            >

                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>

                                Reschedule

                            </button>


                            {{-- CLOSE --}}
                            <button
                                @click="selected = null"
                                class="flex-1 rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                            >
                                Close
                            </button>

                        </div>

                    </div>

                </template>

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- RESCHEDULE MODAL --}}
        {{-- ================================================= --}}

        <div
            x-show="rescheduleOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[60] flex justify-center p-4 overflow-y-auto bg-slate-950/50 backdrop-blur-sm"
            @keydown.escape.window="rescheduleOpen = false"
            @click.self="rescheduleOpen = false"
        >

            <div class="relative w-full max-w-lg p-6 bg-white rounded-2xl shadow-xl overflow-y-auto max-h-[90vh] my-8">

                {{-- Header --}}
                <div class="flex justify-between items-center pb-4 mb-4 border-b">
                    <h3 class="text-lg font-bold text-gray-800">Reschedule Appointment</h3>
                    <button
                        @click="rescheduleOpen = false"
                        class="w-9 h-9 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition flex items-center justify-center"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="space-y-4">

                    {{-- Student --}}
                    <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl">
                        <span class="text-xs font-semibold text-blue-500 uppercase">Student</span>
                        <p class="font-bold text-gray-800" x-text="selected?.student_name"></p>
                        <p class="text-sm text-gray-600" x-text="selected?.student_email"></p>
                    </div>

                    {{-- Current Schedule Display Box --}}
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Current Schedule</span>
                        <p class="font-bold text-gray-800" x-text="selected?.date"></p>
                        <p class="text-sm text-gray-600" x-text="selected?.time_slot"></p>
                    </div>

                    {{-- New Date Input --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Date</label>
                        <input
                            type="date"
                            x-model="rescheduleDate"
                            :min="today"
                            @change="loadRescheduleSlots()"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        >
                    </div>

                    {{-- New Time Slot Dropdown --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Time Slot</label>
                        <select
                            x-model="rescheduleTime"
                            :disabled="!rescheduleDate || loadingSlots"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white disabled:bg-gray-50 disabled:text-gray-400"
                        >
                            <option value="" disabled>
                                <span x-text="rescheduleDate ? (loadingSlots ? 'Loading slots...' : 'Select available time') : 'Pick a date first'"></span>
                            </option>
                            <template x-for="slot in availableSlots" :key="slot.time">
                                <option :value="slot.time" x-text="slot.time + ' — ' + slot.remaining + ' seat(s) left'"></option>
                            </template>
                        </select>
                        <p x-show="slotError" x-text="slotError" class="mt-1.5 text-xs text-red-600"></p>
                    </div>

                    {{-- Reason Textarea --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <textarea
                            x-model="rescheduleReason"
                            rows="3"
                            maxlength="500"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="e.g. {{ $office }} Office schedule adjustment..."
                        ></textarea>
                    </div>

                    {{-- Notice --}}
                    <div class="flex gap-3 rounded-lg border border-blue-100 bg-blue-50 p-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                        </svg>
                        <p class="text-xs leading-5 text-blue-800">
                            The student will automatically receive an <strong>ISUFSTPASS email notification</strong> after the appointment is rescheduled.
                        </p>
                    </div>

                    {{-- Error Box --}}
                    <div x-show="rescheduleError" x-transition class="rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                        <p class="text-sm font-medium text-red-700" x-text="rescheduleError"></p>
                    </div>

                </div>

                {{-- Footer / Actions --}}
                <div class="flex justify-end gap-3 pt-4 mt-6 border-t">
                    <button
                        type="button"
                        @click="rescheduleOpen = false"
                        :disabled="submitting"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitReschedule()"
                        :disabled="submitting || !rescheduleDate || !rescheduleTime || !rescheduleReason.trim()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <span x-text="submitting ? 'Rescheduling...' : 'Save Changes'"></span>
                    </button>
                </div>

            </div>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- ALPINE --}}
    {{-- ================================================= --}}

    <script>

        function appointmentManager() {

            return {

                selected: null,

                rescheduleOpen: false,

                rescheduleDate: '',
                rescheduleTime: '',
                rescheduleReason: '',
                availableSlots: [],
                loadingSlots: false,
                slotError: '',
                rescheduleError: '',
                submitting: false,

                today: new Date().toISOString().split('T')[0],

                all: @json($jsAppointments),


                openDetails(appointment) {

                    this.selected = appointment;

                },


                openReschedule() {

                    if (!this.selected) return;

                    this.rescheduleDate = '';

                    this.rescheduleTime = '';
                    this.rescheduleReason = '';
                    this.availableSlots = [];
                    this.slotError = '';
                    this.rescheduleError = '';
                    this.submitting = false;

                    this.rescheduleOpen = true;

                },


                async loadRescheduleSlots() {

                    if (!this.rescheduleDate || !this.selected) {
                        this.availableSlots = [];
                        return;
                    }

                    this.loadingSlots = true;
                    this.slotError = '';
                    this.rescheduleTime = '';

                    try {
                        const res = await fetch(
                            '{{ route(strtolower($office) . ".appointments.slots") }}?office=' +
                            encodeURIComponent(this.selected.office || '{{ auth()->user()->officeScope() }}') +
                            '&date=' + this.rescheduleDate +
                            '&ignore_id=' + this.selected.id,
                            { headers: { 'Accept': 'application/json' } }
                        );
                        const data = await res.json();
                        this.availableSlots = data.filter(s => s.is_open && s.remaining > 0);
                        if (this.availableSlots.length === 0) {
                            this.slotError = 'No open slots on this date. Try another date.';
                        }
                    } catch (e) {
                        this.slotError = 'Could not load slots. Please try again.';
                    } finally {
                        this.loadingSlots = false;
                    }

                },


                async submitReschedule() {

                    if (!this.selected || this.submitting) return;

                    this.submitting = true;
                    this.rescheduleError = '';

                    try {
                        const res = await fetch('{{ url(strtolower($office)) }}/appointments/' + this.selected.id + '/reschedule', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                date: this.rescheduleDate,
                                time_slot: this.rescheduleTime,
                                reschedule_reason: this.rescheduleReason.trim(),
                            }),
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            this.submitting = false;
                            this.rescheduleError = data.errors
                                ? Object.values(data.errors)[0][0]
                                : (data.message || 'Could not reschedule the appointment.');
                            return;
                        }

                        window.location.href = data.redirect;

                    } catch (e) {
                        this.submitting = false;
                        this.rescheduleError = 'Network error. Please try again.';
                    }

                },


                statusClass(status) {

                    return {

                        'bg-amber-50 text-amber-700 ring-1 ring-amber-200':
                            status === 'pending',

                        'bg-green-50 text-green-700 ring-1 ring-green-200':
                            status === 'confirmed',

                        'bg-blue-50 text-blue-700 ring-1 ring-blue-200':
                            status === 'completed',

                        'bg-red-50 text-red-600 ring-1 ring-red-200':
                            status === 'cancelled',

                        'bg-purple-50 text-purple-700 ring-1 ring-purple-200':
                            status === 'rescheduled',

                    };

                }

            }

        }

    </script>

</x-app-layout>