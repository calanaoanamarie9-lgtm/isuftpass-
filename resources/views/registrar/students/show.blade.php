<x-app-layout>

    <div class="min-h-screen bg-[#f4f7fb] py-8">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                BACK BUTTON
            ========================================================== --}}
            <div class="mb-6">

                <a href="{{ route('registrar.students.index') }}"
                   class="inline-flex items-center gap-2
                          text-sm font-bold
                          text-blue-700
                          hover:text-blue-950
                          transition">

                    <div class="w-8 h-8 rounded-xl
                                bg-white
                                border border-blue-100
                                flex items-center justify-center
                                shadow-sm">

                        <svg class="w-4 h-4"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M15 19l-7-7 7-7"/>

                        </svg>

                    </div>

                    Back to Student Lookup

                </a>

            </div>


            {{-- =========================================================
                STUDENT PROFILE HEADER
            ========================================================== --}}
            <div class="relative overflow-hidden
                        rounded-3xl
                        bg-gradient-to-br
                        from-blue-950
                        via-blue-900
                        to-indigo-950
                        shadow-xl
                        shadow-blue-950/20
                        mb-7">

                {{-- Decorative circles --}}
                <div class="absolute
                            -top-24 -right-24
                            w-72 h-72
                            rounded-full
                            bg-blue-800/30">
                </div>

                <div class="absolute
                            -bottom-32 -left-20
                            w-80 h-80
                            rounded-full
                            bg-indigo-800/20">
                </div>


                <div class="relative p-6 sm:p-8">

                    <div class="flex flex-col lg:flex-row
                                lg:items-center
                                justify-between
                                gap-6">

                        {{-- STUDENT --}}
                        <div class="flex flex-col sm:flex-row
                                    sm:items-center
                                    gap-5">

                            {{-- Avatar --}}
                            <div class="relative shrink-0">

                                <div class="w-24 h-24
                                            rounded-3xl
                                            bg-white/10
                                            border border-white/20
                                            overflow-hidden
                                            flex items-center
                                            justify-center
                                            text-yellow-400
                                            font-extrabold
                                            text-3xl
                                            uppercase
                                            shadow-xl">

                                    @if ($student->studentProfile?->avatar)

                                        <img
                                            src="{{ Storage::url($student->studentProfile->avatar) }}"
                                            alt="Avatar"
                                            class="w-full h-full object-cover">

                                    @else

                                        {{ substr($student->name, 0, 1) }}

                                    @endif

                                </div>


                                {{-- VERIFIED --}}
                                <div class="absolute
                                            -right-2
                                            -bottom-2
                                            w-8 h-8
                                            rounded-full
                                            bg-green-500
                                            border-4
                                            border-blue-950
                                            flex items-center
                                            justify-center">

                                    <svg class="w-4 h-4 text-white"
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


                            {{-- Student Details --}}
                            <div>

                                <div class="flex flex-wrap
                                            items-center gap-2">

                                    <h1 class="text-2xl sm:text-3xl
                                               font-extrabold
                                               text-white">

                                        {{ $student->name }}

                                    </h1>

                                    <span class="inline-flex
                                                 items-center gap-1.5
                                                 px-3 py-1
                                                 rounded-full
                                                 bg-green-500/15
                                                 border border-green-400/20
                                                 text-green-300
                                                 text-[10px]
                                                 font-extrabold
                                                 uppercase
                                                 tracking-wide">

                                        <span class="w-1.5 h-1.5
                                                     rounded-full
                                                     bg-green-400">
                                        </span>

                                        Verified Student

                                    </span>

                                </div>


                                <p class="text-sm
                                          text-blue-200
                                          mt-2">

                                    {{ $student->email }}

                                </p>


                                <div class="flex flex-wrap
                                            items-center
                                            gap-x-4 gap-y-1
                                            mt-2">

                                    <span class="text-xs
                                                 text-blue-300">

                                        {{ $student->studentProfile?->course ?: 'No course set' }}

                                    </span>

                                    @if ($student->studentProfile?->year_level)

                                        <span class="text-blue-500">
                                            •
                                        </span>

                                        <span class="text-xs
                                                     text-blue-300">

                                            Year
                                            {{ $student->studentProfile->year_level }}

                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- ACCOUNT BADGE --}}
                        <div class="self-start lg:self-center
                                    rounded-2xl
                                    bg-white/10
                                    border border-white/10
                                    px-5 py-4
                                    min-w-[190px]">

                            <p class="text-[10px]
                                      uppercase
                                      tracking-widest
                                      font-bold
                                      text-blue-300">

                                Account Status

                            </p>

                            <div class="flex items-center
                                        gap-2 mt-2">

                                <span class="w-2.5 h-2.5
                                             rounded-full
                                             bg-green-400">
                                </span>

                                <span class="text-sm
                                             font-extrabold
                                             text-white">

                                    Active Student

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                STUDENT INFORMATION
            ========================================================== --}}
            <div class="grid lg:grid-cols-3 gap-6 mb-7">

                {{-- BASIC INFORMATION --}}
                <div class="lg:col-span-2
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            overflow-hidden">

                    <div class="px-6 py-5
                                border-b border-gray-100
                                flex items-center gap-3">

                        <div class="w-10 h-10
                                    rounded-xl
                                    bg-blue-50
                                    flex items-center
                                    justify-center">

                            <svg class="w-5 h-5 text-blue-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>

                            </svg>

                        </div>

                        <div>

                            <p class="text-[10px]
                                      font-bold
                                      uppercase
                                      tracking-widest
                                      text-blue-500">

                                Profile

                            </p>

                            <h2 class="text-base
                                       font-extrabold
                                       text-blue-950">

                                Student Information

                            </h2>

                        </div>

                    </div>


                    <div class="p-6">

                        <div class="grid sm:grid-cols-2 gap-4">

                            {{-- NAME --}}
                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[10px]
                                          font-bold
                                          uppercase
                                          tracking-widest
                                          text-gray-400">

                                    Full Name

                                </p>

                                <p class="mt-1.5
                                          text-sm
                                          font-extrabold
                                          text-blue-950">

                                    {{ $student->name }}

                                </p>

                            </div>


                            {{-- EMAIL --}}
                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[10px]
                                          font-bold
                                          uppercase
                                          tracking-widest
                                          text-gray-400">

                                    Email Address

                                </p>

                                <p class="mt-1.5
                                          text-sm
                                          font-extrabold
                                          text-blue-950
                                          break-all">

                                    {{ $student->email }}

                                </p>

                            </div>


                            {{-- COURSE --}}
                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[10px]
                                          font-bold
                                          uppercase
                                          tracking-widest
                                          text-gray-400">

                                    Course

                                </p>

                                <p class="mt-1.5
                                          text-sm
                                          font-extrabold
                                          text-blue-950">

                                    {{ $student->studentProfile?->course ?: 'No course set' }}

                                </p>

                            </div>


                            {{-- YEAR --}}
                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[10px]
                                          font-bold
                                          uppercase
                                          tracking-widest
                                          text-gray-400">

                                    Year Level

                                </p>

                                <p class="mt-1.5
                                          text-sm
                                          font-extrabold
                                          text-blue-950">

                                    @if ($student->studentProfile?->year_level)

                                        Year
                                        {{ $student->studentProfile->year_level }}

                                    @else

                                        Not specified

                                    @endif

                                </p>

                            </div>


                            {{-- ADDRESS --}}
                            @if ($student->studentProfile?->address)

                                <div class="sm:col-span-2
                                            rounded-2xl
                                            bg-[#f4f7fb]
                                            border border-gray-100
                                            p-4">

                                    <p class="text-[10px]
                                              font-bold
                                              uppercase
                                              tracking-widest
                                              text-gray-400">

                                        Address

                                    </p>

                                    <p class="mt-1.5
                                              text-sm
                                              font-extrabold
                                              text-blue-950">

                                        {{ $student->studentProfile->address }}

                                    </p>

                                </div>

                            @endif


                            {{-- CONTACT --}}
                            @if ($student->studentProfile?->contact_number)

                                <div class="sm:col-span-2
                                            rounded-2xl
                                            bg-[#f4f7fb]
                                            border border-gray-100
                                            p-4">

                                    <p class="text-[10px]
                                              font-bold
                                              uppercase
                                              tracking-widest
                                              text-gray-400">

                                        Contact Number

                                    </p>

                                    <p class="mt-1.5
                                              text-sm
                                              font-extrabold
                                              text-blue-950">

                                        {{ $student->studentProfile->contact_number }}

                                    </p>

                                </div>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- ACCOUNT SUMMARY --}}
                <div class="bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            overflow-hidden">

                    <div class="px-6 py-5
                                border-b border-gray-100">

                        <p class="text-[10px]
                                  font-bold
                                  uppercase
                                  tracking-widest
                                  text-blue-500">

                            Account

                        </p>

                        <h2 class="mt-1
                                   text-base
                                   font-extrabold
                                   text-blue-950">

                            Record Summary

                        </h2>

                    </div>


                    <div class="p-6 space-y-4">

                        {{-- MEMBER SINCE --}}
                        <div class="rounded-2xl
                                    bg-blue-50
                                    border border-blue-100
                                    p-4">

                            <p class="text-[10px]
                                      font-bold
                                      uppercase
                                      tracking-widest
                                      text-blue-400">

                                Member Since

                            </p>

                            <p class="mt-1.5
                                      text-sm
                                      font-extrabold
                                      text-blue-950">

                                {{ $student->created_at->format('M j, Y') }}

                            </p>

                        </div>


                        {{-- STUDENT TYPE --}}
                        <div class="rounded-2xl
                                    bg-yellow-50
                                    border border-yellow-100
                                    p-4">

                            <p class="text-[10px]
                                      font-bold
                                      uppercase
                                      tracking-widest
                                      text-yellow-600">

                                Account Type

                            </p>

                            <p class="mt-1.5
                                      text-sm
                                      font-extrabold
                                      text-yellow-900">

                                Student

                            </p>

                        </div>


                        {{-- VERIFICATION --}}
                        <div class="rounded-2xl
                                    bg-green-50
                                    border border-green-100
                                    p-4">

                            <p class="text-[10px]
                                      font-bold
                                      uppercase
                                      tracking-widest
                                      text-green-600">

                                Verification

                            </p>

                            <div class="flex items-center
                                        gap-2 mt-1.5">

                                <span class="w-2 h-2
                                             rounded-full
                                             bg-green-500">
                                </span>

                                <p class="text-sm
                                          font-extrabold
                                          text-green-800">

                                    Verified Account

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                DOCUMENT REQUESTS
            ========================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        overflow-hidden
                        mb-7">

                {{-- Header --}}
                <div class="px-6 py-5
                            border-b border-gray-100
                            bg-gradient-to-r
                            from-blue-50
                            to-white">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center
                                sm:justify-between
                                gap-3">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10
                                        rounded-xl
                                        bg-blue-900
                                        flex items-center
                                        justify-center">

                                <svg class="w-5 h-5 text-yellow-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v16a2 2 0 002 2z"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 13h6m-6 4h6M9 9h2"/>

                                </svg>

                            </div>

                            <div>

                                <p class="text-[10px]
                                          font-bold
                                          uppercase
                                          tracking-widest
                                          text-blue-500">

                                    Transaction History

                                </p>

                                <h2 class="text-base
                                           font-extrabold
                                           text-blue-950">

                                    Document Requests

                                </h2>

                            </div>

                        </div>

                        <span class="text-xs
                                     font-bold
                                     text-gray-400">

                            {{ $student->documentRequests->count() }}
                            Request(s)

                        </span>

                    </div>

                </div>


                {{-- Requests --}}
                @forelse ($student->documentRequests as $request)

                    <div class="group
                                flex flex-col
                                lg:flex-row
                                lg:items-center
                                gap-4
                                px-6 py-5
                                border-b border-gray-50
                                last:border-0
                                hover:bg-blue-50/30
                                transition">

                        {{-- Icon --}}
                        <div class="w-11 h-11
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
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v16a2 2 0 002 2z"/>

                            </svg>

                        </div>


                        {{-- Request Information --}}
                        <div class="flex-1 min-w-0">

                            <p class="text-sm
                                      font-extrabold
                                      text-blue-950
                                      truncate">

                                {{ $request->documentsSummary() }}

                            </p>

                            <div class="flex flex-wrap
                                        items-center
                                        gap-x-2 gap-y-1
                                        mt-1">

                                <span class="text-xs
                                             text-gray-400">

                                    {{ $request->request_number }}

                                </span>

                                <span class="text-gray-300">
                                    •
                                </span>

                                <span class="text-xs
                                             text-gray-400">

                                    {{ $request->created_at->format('M j, Y') }}

                                </span>

                            </div>

                        </div>


                        {{-- Status --}}
                        <span class="self-start
                                     lg:self-center
                                     inline-flex
                                     items-center
                                     gap-1.5
                                     px-3 py-1.5
                                     rounded-full
                                     text-[10px]
                                     font-extrabold
                                     whitespace-nowrap

                            @if ($request->status === 'submitted')
                                bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200

                            @elseif ($request->status === 'processing')
                                bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200

                            @elseif ($request->status === 'for_signature')
                                bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200

                            @elseif ($request->status === 'ready_for_pickup')
                                bg-green-50 text-green-700 ring-1 ring-green-200

                            @elseif ($request->status === 'completed')
                                bg-blue-50 text-blue-700 ring-1 ring-blue-200

                            @else
                                bg-red-50 text-red-600 ring-1 ring-red-200
                            @endif">

                            <span class="w-1.5 h-1.5 rounded-full

                                @if ($request->status === 'submitted')
                                    bg-yellow-500

                                @elseif ($request->status === 'processing')
                                    bg-cyan-500

                                @elseif ($request->status === 'for_signature')
                                    bg-indigo-500

                                @elseif ($request->status === 'ready_for_pickup')
                                    bg-green-500

                                @elseif ($request->status === 'completed')
                                    bg-blue-500

                                @else
                                    bg-red-500
                                @endif">
                            </span>

                            {{ \App\Enums\DocumentRequestStatus::tryFrom($request->status)?->label() ?? ucfirst($request->status) }}

                        </span>

                    </div>

                @empty

                    <div class="p-12 text-center">

                        <div class="mx-auto
                                    w-14 h-14
                                    rounded-2xl
                                    bg-blue-50
                                    flex items-center
                                    justify-center">

                            <svg class="w-7 h-7 text-blue-500"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v16a2 2 0 002 2z"/>

                            </svg>

                        </div>

                        <p class="mt-4
                                  text-sm
                                  font-bold
                                  text-gray-600">

                            No Document Requests

                        </p>

                        <p class="mt-1
                                  text-xs
                                  text-gray-400">

                            This student has no document transactions yet.

                        </p>

                    </div>

                @endforelse

            </div>


            {{-- =========================================================
                APPOINTMENTS
            ========================================================== --}}
            <div class="bg-white
                        rounded-3xl
                        border border-gray-100
                        shadow-sm
                        overflow-hidden
                        mb-7">

                {{-- Header --}}
                <div class="px-6 py-5
                            border-b border-gray-100
                            bg-gradient-to-r
                            from-cyan-50
                            to-white">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center
                                sm:justify-between
                                gap-3">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10
                                        rounded-xl
                                        bg-cyan-700
                                        flex items-center
                                        justify-center">

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

                                <p class="text-[10px]
                                          font-bold
                                          uppercase
                                          tracking-widest
                                          text-cyan-600">

                                    Schedule History

                                </p>

                                <h2 class="text-base
                                           font-extrabold
                                           text-blue-950">

                                    Appointments

                                </h2>

                            </div>

                        </div>

                        <span class="text-xs
                                     font-bold
                                     text-gray-400">

                            {{ $student->appointments->count() }}
                            Appointment(s)

                        </span>

                    </div>

                </div>


                {{-- Appointments --}}
                @forelse ($student->appointments as $appointment)

                    <div class="group
                                flex flex-col
                                lg:flex-row
                                lg:items-center
                                gap-4
                                px-6 py-5
                                border-b border-gray-50
                                last:border-0
                                hover:bg-cyan-50/30
                                transition">

                        {{-- Date Box --}}
                        <div class="w-14 h-14
                                    rounded-2xl
                                    bg-cyan-50
                                    border border-cyan-100
                                    flex flex-col
                                    items-center
                                    justify-center
                                    shrink-0">

                            <span class="text-[9px]
                                         font-extrabold
                                         uppercase
                                         text-cyan-600">

                                {{ $appointment->date->format('M') }}

                            </span>

                            <span class="text-lg
                                         font-extrabold
                                         text-cyan-900
                                         leading-none">

                                {{ $appointment->date->format('d') }}

                            </span>

                        </div>


                        {{-- Appointment Information --}}
                        <div class="flex-1 min-w-0">

                            <p class="text-sm
                                      font-extrabold
                                      text-blue-950
                                      truncate">

                                {{ $appointment->office }}

                            </p>

                            <p class="text-xs
                                      text-gray-500
                                      mt-1
                                      truncate">

                                {{ $appointment->purpose }}

                            </p>

                            <div class="flex flex-wrap
                                        items-center
                                        gap-x-2 gap-y-1
                                        mt-1.5">

                                <span class="text-xs
                                             text-gray-400">

                                    {{ $appointment->date->format('M j, Y') }}

                                </span>

                                <span class="text-gray-300">
                                    •
                                </span>

                                <span class="text-xs
                                             text-gray-400">

                                    {{ $appointment->timeToCome() }}

                                </span>

                                <span class="text-gray-300">
                                    •
                                </span>

                                <span class="text-xs
                                             text-gray-400">

                                    {{ $appointment->reference_code }}

                                </span>

                            </div>

                        </div>


                        {{-- Status --}}
                        <span class="self-start
                                     lg:self-center
                                     inline-flex
                                     items-center
                                     gap-1.5
                                     px-3 py-1.5
                                     rounded-full
                                     text-[10px]
                                     font-extrabold
                                     whitespace-nowrap

                            @if ($appointment->status === 'confirmed')
                                bg-green-50 text-green-700 ring-1 ring-green-200

                            @elseif ($appointment->status === 'checked_in')
                                bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200

                            @elseif ($appointment->status === 'rescheduled')
                                bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200

                            @elseif ($appointment->status === 'completed')
                                bg-blue-50 text-blue-700 ring-1 ring-blue-200

                            @elseif ($appointment->status === 'cancelled')
                                bg-red-50 text-red-600 ring-1 ring-red-200

                            @elseif ($appointment->status === 'no_show')
                                bg-gray-100 text-gray-500 ring-1 ring-gray-200

                            @else
                                bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200
                            @endif">

                            <span class="w-1.5 h-1.5
                                         rounded-full

                                @if ($appointment->status === 'confirmed')
                                    bg-green-500

                                @elseif ($appointment->status === 'checked_in')
                                    bg-cyan-500

                                @elseif ($appointment->status === 'rescheduled')
                                    bg-indigo-500

                                @elseif ($appointment->status === 'completed')
                                    bg-blue-500

                                @elseif ($appointment->status === 'cancelled')
                                    bg-red-500

                                @elseif ($appointment->status === 'no_show')
                                    bg-gray-400

                                @else
                                    bg-yellow-500
                                @endif">
                            </span>

                            {{ \App\Enums\AppointmentStatus::tryFrom($appointment->status)?->label() ?? ucfirst($appointment->status) }}

                        </span>

                    </div>

                @empty

                    <div class="p-12 text-center">

                        <div class="mx-auto
                                    w-14 h-14
                                    rounded-2xl
                                    bg-cyan-50
                                    flex items-center
                                    justify-center">

                            <svg class="w-7 h-7 text-cyan-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                            </svg>

                        </div>

                        <p class="mt-4
                                  text-sm
                                  font-bold
                                  text-gray-600">

                            No Appointments

                        </p>

                        <p class="mt-1
                                  text-xs
                                  text-gray-400">

                            This student has no appointment records yet.

                        </p>

                    </div>

                @endforelse

            </div>


            {{-- =========================================================
                ISUFSTPASS FOOTER
            ========================================================== --}}
            <div class="mt-8
                        px-2
                        flex flex-col sm:flex-row
                        items-center
                        justify-between
                        gap-3">

                <div class="flex items-center gap-2">

                    <div class="w-8 h-8
                                rounded-xl
                                bg-blue-950
                                flex items-center
                                justify-center
                                shadow-sm">

                        <span class="text-yellow-400
                                     text-xs
                                     font-black">

                            P

                        </span>

                    </div>

                    <div>

                        <p class="text-[10px]
                                  font-extrabold
                                  uppercase
                                  tracking-widest
                                  text-blue-950">

                            ISUFSTPASS

                        </p>

                        <p class="text-[9px]
                                  text-gray-400">

                            Secure • Reliable • Official

                        </p>

                    </div>

                </div>


                <p class="text-[10px] text-gray-400">

                    Registrar Student Record

                </p>

            </div>

        </div>

    </div>

</x-app-layout>