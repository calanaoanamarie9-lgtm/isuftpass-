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

    <title>Appointment Verified — ISUFSTPASS</title>

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

<body class="min-h-screen bg-[#f5f7fa] font-sans">

    {{--
        What a scanner opens. The page is the student's own Appointment
        Verification Pass — the shared x-appointment-pass-card — so the
        reference, date, time slot, purpose and date booked read here are
        the same pixels the student was handed at booking, with nothing
        added above it. The only extra line is the gate record below:
        who scanned in, where and when.
    --}}
    <main class="min-h-screen py-6 sm:py-10 px-4">

        <div class="w-full max-w-6xl mx-auto">


            <!-- The pass — byte-identical to the student's card -->
            <x-appointment-pass-card :appointment="$appointment" />


            <!-- Scan record -->
            <div class="mt-5 rounded-2xl bg-white border border-gray-200
                        shadow-sm px-5 sm:px-6 py-4
                        flex flex-wrap items-center justify-between gap-3">

                <div class="flex items-center gap-3 min-w-0">

                    <span class="px-3 py-1 rounded-full text-xs font-extrabold
                                 uppercase tracking-wider
                                 bg-green-100 text-green-800">
                        Entry recorded
                    </span>

                    <span class="text-sm font-semibold text-gray-600">
                        {{ $student->name }} &bull; Main Gate
                    </span>

                </div>

                <p class="text-xs text-gray-500">
                    Scanned on {{ $scan->scanned_at->format('F j, Y') }}
                    &bull; {{ $scan->scanned_at->format('g:i A') }}
                </p>

            </div>


            <!-- Footer -->
            <div class="text-center mt-6">

                <p class="text-xs font-bold text-blue-900">
                    ISUFSTPASS
                </p>

                <p class="text-[10px] text-gray-400 mt-1">
                    QR Code-Based Transaction Management System
                </p>

                <p class="text-[10px] text-gray-400 mt-2 font-semibold">
                    Iloilo State University of Fisheries Science and Technology
                </p>

            </div>

        </div>

    </main>

</body>

</html>
