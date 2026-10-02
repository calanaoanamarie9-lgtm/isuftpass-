<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1"
    >

    <meta name="theme-color" content="#0f2a75">

    <title>QR Pass Verified — ISUFSTPASS</title>

    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    >

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-slate-100 font-sans">

    <!-- Header -->
    <header class="bg-blue-950 text-white shadow-lg">

        <div class="max-w-4xl mx-auto px-5 py-4">

            <div class="flex items-center gap-3">

                <div class="w-12 h-12 bg-white rounded-xl p-1 flex items-center justify-center">
                    <img
                        src="{{ asset('img/isufstpass-logo.png') }}"
                        alt="ISUFSTPASS"
                        class="w-full h-full object-contain"
                    >
                </div>

                <div>
                    <h1 class="font-extrabold text-lg tracking-wide">
                        ISUFSTPASS
                    </h1>

                    <p class="text-xs text-blue-200">
                        Digital Campus Transaction System
                    </p>
                </div>

            </div>

        </div>

    </header>


    <!-- Main -->
    <main class="max-w-4xl mx-auto px-4 py-6 sm:py-10">

        <!-- Verification Status -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-200">

            <!-- Success Header -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-8 text-center text-white">

                <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">

                    <svg
                        class="w-12 h-12 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>

                </div>

                <h2 class="mt-5 text-2xl sm:text-3xl font-extrabold">
                    Valid Student Pass
                </h2>

                <p class="mt-2 text-sm text-green-100">
                    QR Code successfully verified
                </p>

            </div>


            <!-- Scan Direction -->
            <div class="bg-blue-950 px-5 sm:px-8 py-4 text-white">

                <div class="flex items-center justify-between gap-x-3 gap-y-2 flex-wrap">

                    <div class="flex items-center gap-3 min-w-0">

                        <span
                            class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-green-400 text-green-950"
                        >

                            Entry recorded

                        </span>

                        <span class="text-sm text-blue-200">
                            Main Gate
                        </span>

                    </div>

                    <p class="text-xs text-blue-300">
                        {{ $scan->scanned_at->format('M j, Y') }} at {{ $scan->scanned_at->format('g:i A') }}
                    </p>

                </div>

            </div>


            <!-- Student Information -->
            <div class="p-6 sm:p-8">

                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">

                    <!-- Profile Picture -->
                    <div class="w-24 h-24 rounded-2xl overflow-hidden bg-blue-950 flex items-center justify-center shrink-0">

                        @if ($student->studentProfile?->avatar)

                            <img
                                src="{{ Storage::url($student->studentProfile->avatar) }}"
                                alt="Student Profile"
                                class="w-full h-full object-cover"
                            >

                        @else

                            <span class="text-3xl font-extrabold text-yellow-400">
                                {{ strtoupper(substr($student->name, 0, 1)) }}
                            </span>

                        @endif

                    </div>


                    <!-- Student Details -->
                    <div class="text-center sm:text-left min-w-0">

                        <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                            Student
                        </p>

                        <h3 class="mt-1 text-2xl font-extrabold text-gray-900">
                            {{ $student->name }}
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $student->email }}
                        </p>

                    </div>

                </div>


                <!-- Student Information Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-7">

                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">
                            Student ID
                        </p>

                        <p class="mt-1 font-bold text-gray-900 break-words">
                            {{ $profile->student_id ?? '—' }}
                        </p>
                    </div>


                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">
                            Course
                        </p>

                        <p class="mt-1 font-bold text-gray-900 break-words">
                            {{ $profile->course ?? '—' }}
                        </p>
                    </div>


                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">
                            Year Level
                        </p>

                        <p class="mt-1 font-bold text-gray-900">
                            {{ $profile->year_level ?? '—' }}
                        </p>
                    </div>


                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500">
                            Status
                        </p>

                        <p class="mt-1 font-bold text-green-600">
                            Active
                        </p>
                    </div>

                </div>


                <!-- Gate Verification -->
                <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 p-5">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-green-600 flex items-center justify-center shrink-0">

                            <svg
                                class="w-6 h-6 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>

                        </div>

                        <div>

                            <p class="font-extrabold text-green-800">
                                Identity Verified
                            </p>

                            <p class="text-sm text-green-700 mt-1">
                                This QR pass has been successfully validated by ISUFSTPASS.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- Transactions -->
                <div class="mt-8">

                    <div class="flex items-center justify-between mb-4">

                        <div>

                            <h3 class="text-lg font-extrabold text-gray-900">
                                Active Transactions
                            </h3>

                            <p class="text-xs text-gray-500 mt-1">
                                Transactions associated with this student.
                            </p>

                        </div>

                    </div>


                    <div class="space-y-3">

                        {{-- Appointments --}}
                        @foreach ($appointments as $appointment)

                            <div class="border border-blue-100 bg-blue-50 rounded-2xl p-4">

                                <div class="flex items-start justify-between gap-3">

                                    <div class="min-w-0">

                                        <p class="font-bold text-gray-900 break-words">
                                            {{ $appointment->office }}
                                        </p>

                                        <p class="text-sm text-gray-500 mt-1 break-words">

                                            {{ $appointment->date->format('M d, Y') }}

                                            <span class="mx-1">•</span>

                                            {{ $appointment->time_slot }}

                                        </p>

                                    </div>

                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                        Appointment
                                    </span>

                                </div>

                            </div>

                        @endforeach


                        {{-- Document Requests --}}
                        @foreach ($requests as $request)

                            <div class="border border-indigo-100 bg-indigo-50 rounded-2xl p-4">

                                <div class="flex items-start justify-between gap-3">

                                    <div class="min-w-0">

                                        <p class="font-bold text-gray-900 break-words">
                                            {{ $request->documentsSummary() }}
                                        </p>

                                        <p class="text-sm text-gray-500 mt-1">
                                            Reference:
                                            {{ $request->request_number }}
                                        </p>

                                    </div>

                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 shrink-0">
                                        Document
                                    </span>

                                </div>

                            </div>

                        @endforeach


                        @if ($appointments->isEmpty() && $requests->isEmpty())

                            <div class="text-center py-8 bg-gray-50 rounded-2xl">

                                <p class="font-semibold text-gray-600">
                                    No active transactions
                                </p>

                                <p class="text-sm text-gray-400 mt-1">
                                    There are currently no active transactions on file.
                                </p>

                            </div>

                        @endif

                    </div>

                </div>


                <!-- Scan Information -->
                <div class="mt-8 pt-6 border-t border-gray-100 text-center">

                    <p class="text-xs text-gray-400">
                        Verified on
                    </p>

                    <p class="mt-1 text-sm font-bold text-gray-700">
                        {{ $scan->scanned_at->format('F j, Y • g:i A') }}
                    </p>

                    <p class="mt-4 text-[11px] text-gray-400 leading-5">
                        This verification page is generated by the
                        ISUFSTPASS Digital Campus Transaction System.
                    </p>

                    <p class="mt-2 text-[11px] font-semibold text-gray-400">
                        Iloilo State University of Fisheries Science and Technology
                    </p>

                </div>

            </div>

        </div>


        <!-- Footer -->
        <div class="text-center mt-6">

            <p class="text-xs font-bold text-blue-900">
                ISUFSTPASS
            </p>

            <p class="text-[10px] text-gray-400 mt-1">
                QR Code-Based Transaction Management System
            </p>

        </div>

    </main>

</body>
</html>