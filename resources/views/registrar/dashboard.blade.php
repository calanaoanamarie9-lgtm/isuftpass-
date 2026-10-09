<x-app-layout>

    <div class="min-h-screen bg-[#f4f7fb] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =====================================================
                WELCOME BANNER
            ====================================================== --}}
            <div class="relative overflow-hidden
                        rounded-3xl
                        bg-gradient-to-br
                        from-blue-950
                        via-blue-900
                        to-blue-800
                        shadow-xl">

                {{-- Decorative circles --}}
                <div class="absolute -top-24 -right-24
                            w-72 h-72
                            rounded-full
                            bg-blue-600/30
                            blur-3xl
                            pointer-events-none">
                </div>

                <div class="absolute -bottom-32 left-1/3
                            w-72 h-72
                            rounded-full
                            bg-indigo-500/20
                            blur-3xl
                            pointer-events-none">
                </div>


                <div class="relative
                            px-6 py-8
                            sm:px-8
                            lg:px-10
                            flex flex-col
                            lg:flex-row
                            lg:items-center
                            lg:justify-between
                            gap-7">

                    {{-- LEFT --}}
                    <div>

                        <div class="flex items-center gap-3 mb-4">

                            <div class="w-11 h-11
                                        rounded-2xl
                                        bg-white/10
                                        border border-white/10
                                        flex items-center
                                        justify-center">

                                <svg class="w-5 h-5 text-yellow-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.042-.133-2.052-.382-3.016z"/>

                                </svg>

                            </div>

                            <div>

                                <p class="text-[10px]
                                          font-extrabold
                                          uppercase
                                          tracking-[0.2em]
                                          text-yellow-400">

                                    ISUFSTPASS

                                </p>

                                <p class="text-[10px]
                                          font-semibold
                                          uppercase
                                          tracking-wider
                                          text-blue-300">

                                    Registrar Dashboard

                                </p>

                            </div>

                        </div>


                        <h1 class="text-2xl
                                   sm:text-3xl
                                   font-extrabold
                                   text-white
                                   tracking-tight">

                            Welcome, {{ Auth::user()->name }}!

                        </h1>


                        <p class="mt-2
                                  max-w-2xl
                                  text-sm
                                  sm:text-base
                                  leading-6
                                  text-blue-200">

                            Review document requests, manage student appointments,
                            approve transactions, and monitor issued documents.

                        </p>

                    </div>


                    {{-- RIGHT ACTIONS --}}
                    <div class="relative
                                flex flex-col
                                sm:flex-row
                                gap-3
                                shrink-0">

                        <a
                            href="{{ route('registrar.document-requests.index') }}"
                            class="inline-flex
                                   items-center
                                   justify-center
                                   gap-2
                                   px-5 py-3
                                   rounded-xl
                                   bg-yellow-400
                                   text-blue-950
                                   text-sm
                                   font-extrabold
                                   shadow-lg
                                   shadow-black/10
                                   hover:bg-yellow-300
                                   transition"
                        >

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>

                            </svg>

                            View Requests

                        </a>


                        <a
                            href="{{ route('registrar.qr.index') }}"
                            class="inline-flex
                                   items-center
                                   justify-center
                                   gap-2
                                   px-5 py-3
                                   rounded-xl
                                   bg-white/10
                                   border border-white/20
                                   text-white
                                   text-sm
                                   font-bold
                                   hover:bg-white/20
                                   transition"
                        >

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 3h7v7H3V3zm11 0h7v7h-7V3zM3 14h7v7H3v-7zm11 0h3v3h-3v-3zm4 4h3v3h-3v-3z"/>

                            </svg>

                            Scan QR

                        </a>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                QUICK STATS
            ====================================================== --}}
            <div class="mt-7
                        grid
                        grid-cols-2
                        lg:grid-cols-4
                        gap-4">


                {{-- PENDING --}}
                <a href="{{ route('registrar.document-requests.index', ['tab' => 'active', 'status' => 'submitted']) }}"
                   class="group block
                            bg-white
                            rounded-2xl
                            p-5
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            hover:border-red-200
                            transition">

                    <div class="flex items-start
                                justify-between">

                        <div class="w-11 h-11
                                    rounded-xl
                                    bg-red-50
                                    flex items-center
                                    justify-center">

                            <svg class="w-5 h-5 text-red-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>

                            </svg>

                        </div>

                        <span class="text-[9px]
                                     font-extrabold
                                     uppercase
                                     tracking-wider
                                     text-red-600
                                     bg-red-50
                                     px-2 py-1
                                     rounded-full">

                            Pending

                        </span>

                    </div>


                    <p class="mt-5
                              text-3xl
                              font-black
                              text-gray-900">

                        {{ $pendingCount }}

                    </p>

                    <p class="mt-1
                              text-sm
                              font-medium
                              text-gray-500">

                        Pending Requests

                    </p>

                </a>


                {{-- APPROVED --}}
                <a href="{{ route('registrar.document-requests.index', ['tab' => 'active', 'status' => 'for_signature']) }}"
                   class="group block
                            bg-white
                            rounded-2xl
                            p-5
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            hover:border-green-200
                            transition">

                    <div class="flex items-start
                                justify-between">

                        <div class="w-11 h-11
                                    rounded-xl
                                    bg-green-50
                                    flex items-center
                                    justify-center">

                            <svg class="w-5 h-5 text-green-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.042-.133-2.052-.382-3.016z"/>

                            </svg>

                        </div>

                        <span class="text-[9px]
                                     font-extrabold
                                     uppercase
                                     tracking-wider
                                     text-green-600
                                     bg-green-50
                                     px-2 py-1
                                     rounded-full">

                            Approved

                        </span>

                    </div>


                    <p class="mt-5
                              text-3xl
                              font-black
                              text-gray-900">

                        {{ $approvedCount }}

                    </p>

                    <p class="mt-1
                              text-sm
                              font-medium
                              text-gray-500">

                        Approved Requests

                    </p>

                </a>


                {{-- ISSUED --}}
                <a href="{{ route('registrar.document-requests.index', ['tab' => 'archived', 'status' => 'completed']) }}"
                   class="group block
                            bg-white
                            rounded-2xl
                            p-5
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            hover:border-blue-200
                            transition">

                    <div class="flex items-start
                                justify-between">

                        <div class="w-11 h-11
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
                                      d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>

                            </svg>

                        </div>

                        <span class="text-[9px]
                                     font-extrabold
                                     uppercase
                                     tracking-wider
                                     text-blue-600
                                     bg-blue-50
                                     px-2 py-1
                                     rounded-full">

                            Documents

                        </span>

                    </div>


                    <p class="mt-5
                              text-3xl
                              font-black
                              text-gray-900">

                        {{ $issuedCount }}

                    </p>

                    <p class="mt-1
                              text-sm
                              font-medium
                              text-gray-500">

                        Issued Documents

                    </p>

                </a>


                {{-- STUDENTS --}}
                <a href="{{ route('registrar.students.index') }}"
                   class="group block
                            bg-white
                            rounded-2xl
                            p-5
                            border border-gray-100
                            shadow-sm
                            hover:shadow-md
                            hover:border-indigo-200
                            transition">

                    <div class="flex items-start
                                justify-between">

                        <div class="w-11 h-11
                                    rounded-xl
                                    bg-indigo-50
                                    flex items-center
                                    justify-center">

                            <svg class="w-5 h-5 text-indigo-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>

                            </svg>

                        </div>

                        <span class="text-[9px]
                                     font-extrabold
                                     uppercase
                                     tracking-wider
                                     text-indigo-600
                                     bg-indigo-50
                                     px-2 py-1
                                     rounded-full">

                            Students

                        </span>

                    </div>


                    <p class="mt-5
                              text-3xl
                              font-black
                              text-gray-900">

                        {{ $servedCount }}

                    </p>

                    <p class="mt-1
                              text-sm
                              font-medium
                              text-gray-500">

                        Students Served

                    </p>

                </a>

            </div>


            {{-- =====================================================
                MAIN CONTENT
            ====================================================== --}}
            <div class="mt-7 grid grid-cols-1 xl:grid-cols-3 gap-6">


                {{-- =================================================
                    PENDING REQUESTS
                ================================================== --}}
                <div class="xl:col-span-2
                            bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            overflow-hidden">

                    {{-- HEADER --}}
                    <div class="px-6 py-5
                                border-b border-gray-100
                                flex flex-col
                                sm:flex-row
                                sm:items-center
                                sm:justify-between
                                gap-3">

                        <div>

                            <div class="flex items-center gap-2">

                                <div class="w-8 h-8
                                            rounded-lg
                                            bg-blue-50
                                            flex items-center
                                            justify-center">

                                    <svg class="w-4 h-4 text-blue-700"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>

                                    </svg>

                                </div>

                                <h2 class="font-extrabold
                                           text-blue-950">

                                    Pending Document Requests

                                </h2>

                            </div>

                            <p class="mt-1
                                      text-xs
                                      text-gray-400
                                      ml-10">

                                Requests waiting for registrar action

                            </p>

                        </div>


                        <a
                            href="{{ route('registrar.document-requests.index') }}"
                            class="inline-flex
                                   items-center
                                   gap-1.5
                                   text-xs
                                   font-extrabold
                                   text-blue-700
                                   hover:text-blue-900
                                   transition"
                        >

                            View All

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


                    {{-- TABLE --}}
                    <div class="overflow-x-auto">

                        <table class="min-w-full">

                            <thead class="bg-gray-50/80">

                                <tr>

                                    <th class="px-6 py-3
                                               text-left
                                               text-[10px]
                                               font-extrabold
                                               uppercase
                                               tracking-wider
                                               text-gray-400">

                                        Student

                                    </th>

                                    <th class="px-6 py-3
                                               text-left
                                               text-[10px]
                                               font-extrabold
                                               uppercase
                                               tracking-wider
                                               text-gray-400">

                                        Document

                                    </th>

                                    <th class="px-6 py-3
                                               text-left
                                               text-[10px]
                                               font-extrabold
                                               uppercase
                                               tracking-wider
                                               text-gray-400">

                                        Date Requested

                                    </th>

                                    <th class="px-6 py-3
                                               text-left
                                               text-[10px]
                                               font-extrabold
                                               uppercase
                                               tracking-wider
                                               text-gray-400">

                                        Status

                                    </th>

                                    <th class="px-6 py-3
                                               text-right
                                               text-[10px]
                                               font-extrabold
                                               uppercase
                                               tracking-wider
                                               text-gray-400">

                                        Action

                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-100">

                                @forelse ($pendingRequests as $request)

                                    <tr class="hover:bg-gray-50/70 transition">

                                        {{-- STUDENT --}}
                                        <td class="px-6 py-4">

                                            <p class="text-sm
                                                      font-bold
                                                      text-gray-900">

                                                {{ $request->student_name ?: ($request->user?->name ?? '—') }}

                                            </p>

                                            <p class="mt-0.5
                                                      text-xs
                                                      text-gray-400">

                                                {{ $request->request_number }}

                                            </p>

                                        </td>

                                        {{-- DOCUMENT --}}
                                        <td class="px-6 py-4">

                                            <p class="max-w-[18rem]
                                                      text-sm
                                                      text-gray-600">

                                                {{ $request->documentsSummary() }}

                                            </p>

                                        </td>

                                        {{-- DATE REQUESTED --}}
                                        <td class="px-6 py-4">

                                            <p class="text-sm
                                                      text-gray-700">

                                                {{ ($request->submitted_at ?? $request->created_at)->format('M j, Y') }}

                                            </p>

                                            <p class="mt-0.5
                                                      text-xs
                                                      text-gray-400">

                                                {{ ($request->submitted_at ?? $request->created_at)->format('g:i A') }}

                                            </p>

                                        </td>

                                        {{-- STATUS --}}
                                        <td class="px-6 py-4">

                                            <span class="inline-flex
                                                         items-center
                                                         gap-1.5
                                                         px-2.5 py-1
                                                         rounded-full
                                                         text-[10px]
                                                         font-extrabold
                                                         uppercase
                                                         tracking-wider
                                                         bg-red-50
                                                         text-red-700
                                                         ring-1
                                                         ring-red-200">

                                                <span class="w-1.5 h-1.5
                                                             rounded-full
                                                             bg-red-500"></span>

                                                Pending

                                            </span>

                                        </td>

                                        {{-- ACTION --}}
                                        <td class="px-6 py-4 text-right">

                                            <a href="{{ route('registrar.document-requests.show', $request) }}"
                                               class="inline-flex
                                                      items-center
                                                      gap-1.5
                                                      px-3 py-1.5
                                                      rounded-lg
                                                      bg-blue-600
                                                      text-white
                                                      text-xs
                                                      font-bold
                                                      hover:bg-blue-700
                                                      transition">

                                                Review

                                            </a>

                                        </td>

                                    </tr>

                                @empty

                                <tr>

                                    <td colspan="5"
                                        class="px-6 py-16">

                                        <div class="flex flex-col
                                                    items-center
                                                    justify-center
                                                    text-center">

                                            <div class="w-16 h-16
                                                        rounded-2xl
                                                        bg-blue-50
                                                        flex items-center
                                                        justify-center
                                                        mb-4">

                                                <svg class="w-7 h-7 text-blue-300"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="1.7"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>

                                                </svg>

                                            </div>

                                            <p class="text-sm
                                                      font-extrabold
                                                      text-blue-950">

                                                No Pending Requests

                                            </p>

                                            <p class="mt-1
                                                      max-w-sm
                                                      text-xs
                                                      leading-5
                                                      text-gray-400">

                                                New document requests from
                                                students will appear here
                                                when they are submitted.

                                            </p>

                                        </div>

                                    </td>

                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>


                {{-- =================================================
                    QUICK ACTIONS
                ================================================== --}}
                <div class="bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            overflow-hidden">

                    <div class="px-6 py-5
                                border-b border-gray-100">

                        <div class="flex items-center gap-2">

                            <div class="w-8 h-8
                                        rounded-lg
                                        bg-yellow-50
                                        flex items-center
                                        justify-center">

                                <svg class="w-4 h-4 text-yellow-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M13 10V3L4 14h7v7l9-11h-7z"/>

                                </svg>

                            </div>

                            <h2 class="font-extrabold
                                       text-blue-950">

                                Quick Actions

                            </h2>

                        </div>

                        <p class="mt-1
                                  text-xs
                                  text-gray-400
                                  ml-10">

                            Frequently used Registrar tools

                        </p>

                    </div>


                    <div class="p-5 space-y-3">


                        {{-- DOCUMENT REQUESTS --}}
                        <a
                            href="{{ route('registrar.document-requests.index') }}"
                            class="group flex items-center gap-3
                                   rounded-2xl
                                   border border-gray-100
                                   bg-gray-50
                                   p-4
                                   hover:border-blue-200
                                   hover:bg-blue-50/50
                                   transition"
                        >

                            <div class="w-10 h-10
                                        rounded-xl
                                        bg-blue-100
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
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>

                                </svg>

                            </div>

                            <div class="flex-1 min-w-0">

                                <p class="text-sm
                                          font-bold
                                          text-gray-800">

                                    Document Requests

                                </p>

                                <p class="text-[11px]
                                          text-gray-400">

                                    Review and process requests

                                </p>

                            </div>

                            <svg class="w-4 h-4
                                        text-gray-300
                                        group-hover:text-blue-600
                                        transition"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 5l7 7-7 7"/>

                            </svg>

                        </a>


                        {{-- APPOINTMENTS --}}
                        <a
                            href="{{ route('registrar.appointments.index') }}"
                            class="group flex items-center gap-3
                                   rounded-2xl
                                   border border-gray-100
                                   bg-gray-50
                                   p-4
                                   hover:border-indigo-200
                                   hover:bg-indigo-50/50
                                   transition"
                        >

                            <div class="w-10 h-10
                                        rounded-xl
                                        bg-indigo-100
                                        flex items-center
                                        justify-center
                                        shrink-0">

                                <svg class="w-5 h-5 text-indigo-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                </svg>

                            </div>

                            <div class="flex-1 min-w-0">

                                <p class="text-sm
                                          font-bold
                                          text-gray-800">

                                    Appointments

                                </p>

                                <p class="text-[11px]
                                          text-gray-400">

                                    Manage student appointments

                                </p>

                            </div>

                            <svg class="w-4 h-4
                                        text-gray-300
                                        group-hover:text-indigo-600
                                        transition"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 5l7 7-7 7"/>

                            </svg>

                        </a>


                        {{-- AVAILABILITY --}}
                        <a
                            href="{{ route('registrar.availability.index') }}"
                            class="group flex items-center gap-3
                                   rounded-2xl
                                   border border-gray-100
                                   bg-gray-50
                                   p-4
                                   hover:border-green-200
                                   hover:bg-green-50/50
                                   transition"
                        >

                            <div class="w-10 h-10
                                        rounded-xl
                                        bg-green-100
                                        flex items-center
                                        justify-center
                                        shrink-0">

                                <svg class="w-5 h-5 text-green-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                </svg>

                            </div>

                            <div class="flex-1 min-w-0">

                                <p class="text-sm
                                          font-bold
                                          text-gray-800">

                                    Availability

                                </p>

                                <p class="text-[11px]
                                          text-gray-400">

                                    Set appointment schedules

                                </p>

                            </div>

                            <svg class="w-4 h-4
                                        text-gray-300
                                        group-hover:text-green-600
                                        transition"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 5l7 7-7 7"/>

                            </svg>

                        </a>


                        {{-- STUDENTS --}}
                        <a
                            href="{{ route('registrar.students.index') }}"
                            class="group flex items-center gap-3
                                   rounded-2xl
                                   border border-gray-100
                                   bg-gray-50
                                   p-4
                                   hover:border-purple-200
                                   hover:bg-purple-50/50
                                   transition"
                        >

                            <div class="w-10 h-10
                                        rounded-xl
                                        bg-purple-100
                                        flex items-center
                                        justify-center
                                        shrink-0">

                                <svg class="w-5 h-5 text-purple-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>

                                </svg>

                            </div>

                            <div class="flex-1 min-w-0">

                                <p class="text-sm
                                          font-bold
                                          text-gray-800">

                                    Student Lookup

                                </p>

                                <p class="text-[11px]
                                          text-gray-400">

                                    Search student records

                                </p>

                            </div>

                            <svg class="w-4 h-4
                                        text-gray-300
                                        group-hover:text-purple-600
                                        transition"
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

            </div>


            {{-- =====================================================
                FOOTER
            ====================================================== --}}
            <div class="mt-8
                        flex flex-col
                        sm:flex-row
                        items-center
                        justify-between
                        gap-3
                        px-2">

                <div class="flex items-center gap-2">

                    <div class="w-8 h-8
                                rounded-xl
                                bg-blue-950
                                flex items-center
                                justify-center">

                        <span class="text-xs
                                     font-black
                                     text-yellow-400">

                            P

                        </span>

                    </div>

                    <div>

                        <p class="text-[9px]
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


                <p class="text-[10px]
                          text-gray-400">

                    Registrar Management Portal

                </p>

            </div>

        </div>

    </div>

</x-app-layout>