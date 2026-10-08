<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f2a75">
    <title>Digital Claim Pass — ISUFSTPASS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f7fa] font-sans">

    {{-- Same masthead as verify.pass and verify.appointment: every page a QR
         can open must look like it came out of one system, not three. --}}
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
                    <h1 class="font-extrabold text-lg tracking-wide">ISUFSTPASS</h1>
                    <p class="text-xs text-blue-200">Digital Campus Transaction System</p>
                </div>
            </div>
        </div>
    </header>

    @php
        /*
        |--------------------------------------------------------------------------
        | SAME BLOCK AS THE DIGITAL CLAIM PASS IN VIEW DETAILS
        |--------------------------------------------------------------------------
        | The scanned page is the claim pass itself, so it is built from the
        | same values and the same status styles as the student's view of it.
        */

        $statusEnum = \App\Enums\DocumentRequestStatus::tryFrom($documentRequest->status);

        $statusLabel = $statusEnum?->label()
            ?? ucfirst(str_replace('_', ' ', $documentRequest->status));

        $purposeLabel = \App\Enums\RequestPurposeType::tryFrom($documentRequest->purpose_type)
            ?->label() ?? ($documentRequest->purpose ?? 'N/A');

        $documentNames = $documentRequest->documents
            ->pluck('name')
            ->filter()
            ->join(', ');

        $totalFee = (float) $documentRequest->documents->sum('fee');

        $totalCopies = (int) $documentRequest->documents->sum('pivot.quantity');
        $totalCopies = $totalCopies > 0 ? $totalCopies : 1;

        $statusClass = match ($documentRequest->status) {
            'submitted', 'pending', 'payment_pending' =>
                'bg-red-100 text-red-800 border-red-200',
            'paid', 'processing' =>
                'bg-blue-100 text-blue-800 border-blue-200',
            'approved' =>
                'bg-green-100 text-green-800 border-green-200',
            'ready_for_pickup' =>
                'bg-indigo-100 text-indigo-800 border-indigo-200',
            'completed' =>
                'bg-emerald-100 text-emerald-800 border-emerald-200',
            'cancelled' =>
                'bg-red-100 text-red-800 border-red-200',
            default =>
                'bg-gray-100 text-gray-700 border-gray-200',
        };

        $statusDotClass = match ($documentRequest->status) {
            'submitted', 'pending', 'payment_pending' =>
                'bg-red-600',
            'paid', 'processing' =>
                'bg-blue-600',
            'approved' =>
                'bg-green-600',
            'ready_for_pickup' =>
                'bg-indigo-600',
            'completed' =>
                'bg-emerald-600',
            'cancelled' =>
                'bg-red-600',
            default =>
                'bg-gray-500',
        };

        $statusDescription = match ($documentRequest->status) {
            'submitted', 'pending' =>
                'Your document request has been submitted and is waiting for processing.',
            'payment_pending' =>
                'Please complete the required payment for your document request.',
            'paid' =>
                'Your payment has been recorded and your request is being processed.',
            'processing' =>
                'Your documents are currently being processed by the office.',
            'approved' =>
                'Your document request has been approved.',
            'ready_for_pickup' =>
                'Your document is ready for pickup at the claiming office.',
            'completed' =>
                'Your document request has been successfully completed.',
            'cancelled' =>
                'This document request has been cancelled.',
            default =>
                'Your document request is currently being processed.',
        };

        $ready = $documentRequest->status === 'ready_for_pickup';
        $cancelled = $documentRequest->status === 'cancelled';
        $done = $documentRequest->status === 'completed';
    @endphp

    <main class="max-w-4xl mx-auto px-4 py-6 sm:py-10">

        {{-- ============================================================
             DIGITAL CLAIM PASS — same card as the view details page
        ============================================================= --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">

            {{-- ====================================================
                 BLUE HEADER
            ===================================================== --}}
            <div class="relative bg-gradient-to-r from-[#0b1f46] via-[#123b78] to-[#1857a5] px-6 sm:px-10 py-7">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">

                    <div>

                        <div class="flex items-center gap-3 mb-5">

                            <div class="w-11 h-11 rounded-full bg-white flex items-center justify-center overflow-hidden shadow">
                                <img
                                    src="{{ asset('img/isufstpass-logo.png') }}"
                                    alt="ISUFSTPASS"
                                    class="w-9 h-9 object-contain"
                                >
                            </div>

                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold tracking-[0.18em] text-blue-200 uppercase">
                                    ILOILO STATE UNIVERSITY
                                </p>

                                <h2 class="text-lg font-black text-white tracking-wide">
                                    ISUFSTPASS
                                </h2>
                            </div>

                        </div>

                        <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                            Digital Claim Pass
                        </h1>

                        <p class="mt-1 text-sm text-blue-100">
                            Present this QR code to authorized staff
                            for verification.
                        </p>

                    </div>

                    {{-- STATUS --}}
                    <div>
                        <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full border {{ $statusClass }} text-xs font-black uppercase tracking-wide shadow-sm">
                            <span class="w-2 h-2 rounded-full {{ $statusDotClass }}"></span>
                            {{ strtoupper($statusLabel) }}
                        </span>
                    </div>

                </div>

            </div>

            {{-- Verification band: the fact a staff member reads first --}}
            <div class="bg-blue-950 px-5 sm:px-8 py-4 text-white">
                <div class="flex items-center justify-between gap-x-3 gap-y-2 flex-wrap">

                    <div class="flex items-center gap-3 min-w-0">
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-green-400 text-green-950">
                            QR verified
                        </span>

                        <span class="text-sm text-blue-200 break-all">
                            {{ $documentRequest->request_number }}
                        </span>
                    </div>

                    <p class="text-xs text-blue-300">
                        Scanned {{ now()->format('M j, Y') }} at {{ now()->format('g:i A') }}
                    </p>

                </div>
            </div>

            {{-- ========================================================
                 REQUEST + QR
            ========================================================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-2">

                {{-- ====================================================
                     VERIFICATION INFORMATION
                ===================================================== --}}
                <div class="p-6 sm:p-9">

                    <p class="text-[11px] font-black tracking-[0.18em] text-[#174b91] uppercase mb-2">
                        Verification Information
                    </p>

                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 leading-tight">
                        {{ $documentNames ?: 'Document Request' }}
                    </h2>

                    <p class="mt-2 text-sm text-gray-500 leading-6">
                        {{ $statusDescription }}
                    </p>

                    {{-- INFORMATION GRID --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-7 mt-9">

                        <div>
                            <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                Reference Number
                            </p>
                            <p class="mt-1 text-sm font-black text-gray-900 break-all">
                                {{ $documentRequest->request_number }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                Date Submitted
                            </p>
                            <p class="mt-1 text-sm font-black text-gray-900">
                                {{ $documentRequest->created_at?->format('M d, Y') ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                Purpose
                            </p>
                            <p class="mt-1 text-sm font-black text-gray-900">
                                {{ $purposeLabel }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                Copies
                            </p>
                            <p class="mt-1 text-sm font-black text-gray-900">
                                {{ $totalCopies }} {{ $totalCopies == 1 ? 'copy' : 'copies' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                Claiming Office
                            </p>
                            <p class="mt-1 text-sm font-black text-gray-900">
                                {{ $documentRequest->claiming_office ?? "Registrar's Office" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                Total Fee
                            </p>
                            <p class="mt-1 text-sm font-black text-[#1558ad]">
                                ₱{{ number_format($totalFee, 2) }}
                            </p>
                        </div>

                    </div>

                    {{-- VERIFICATION INSTRUCTIONS --}}
                    <div class="mt-9 p-4 rounded-2xl bg-blue-50 border border-blue-100">
                        <div class="flex items-start gap-3">

                            <div class="w-9 h-9 rounded-xl bg-[#2563b8] text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                    <path stroke-linecap="round" stroke-width="2" d="M12 10v6"/>
                                    <circle cx="12" cy="7" r="1" fill="currentColor" stroke="none"/>
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-sm font-black text-[#17345f]">
                                    Verification Instructions
                                </h3>

                                <p class="mt-1 text-xs leading-5 text-gray-600">
                                    Keep this QR code available when
                                    visiting the university office.
                                    Authorized staff may scan this code
                                    to verify your document request.
                                </p>
                            </div>

                        </div>
                    </div>

                    {{-- WHAT HAPPENS NEXT --}}
                    @if ($ready)
                        <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 p-5 text-center">
                            <p class="text-sm font-bold text-green-700">Ready for release</p>
                            <p class="text-xs text-green-600 mt-1">
                                Present this page together with a valid ID at the Registrar's counter.
                            </p>
                        </div>
                    @elseif ($cancelled)
                        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5 text-center">
                            <p class="text-sm font-bold text-rose-700">This claim slip has been cancelled.</p>
                            <p class="text-xs text-rose-600 mt-1">
                                Contact the office that processed this request.
                            </p>
                        </div>
                    @else
                        <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-5 text-center">
                            <p class="text-xs text-gray-500">
                                This claim slip is not yet ready for release. Check back later.
                            </p>
                        </div>
                    @endif

                </div>

                {{-- ====================================================
                     QR CODE
                ===================================================== --}}
                <div class="p-6 sm:p-9 flex flex-col items-center justify-center bg-[#fbfcfe] border-t lg:border-t-0 lg:border-l border-gray-100">

                    <p class="text-[11px] font-black tracking-[0.2em] text-gray-500 uppercase mb-4">
                        Scan to Verify
                    </p>

                    {{-- QR CONTAINER --}}
                    <div class="relative inline-block">

                        {{-- Yellow accent --}}
                        <div class="absolute -inset-3 bg-[#e1b000] rounded-[2rem]"></div>

                        <div class="relative bg-white rounded-[1.7rem] border-2 border-gray-900 p-5 shadow-sm">

                            @if (!empty($qrCodeDataUri))
                                <img
                                    src="{{ $qrCodeDataUri }}"
                                    alt="Document Request QR Code"
                                    class="w-60 h-60 sm:w-64 sm:h-64 object-contain"
                                >
                            @else
                                <div class="w-60 h-60 sm:w-64 sm:h-64 flex flex-col items-center justify-center text-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 9v4m0 4h.01M10.29 3.86l-7.82 14a2 2 0 001.74 3h15.58a2 2 0 001.74-3l-7.82-14a2 2 0 00-3.48 0z"/>
                                    </svg>
                                    <p class="text-sm font-bold">QR Code Unavailable</p>
                                </div>
                            @endif

                            <p class="text-center mt-3 text-[10px] font-mono font-semibold text-gray-500 tracking-wide">
                                {{ $documentRequest->request_number }}
                            </p>

                        </div>

                    </div>

                    {{-- IDENTITY OF THE REQUESTER --}}
                    <div class="w-full max-w-xs mt-8 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600">
                            Requested by
                        </p>

                        <p class="mt-1 text-lg font-black text-gray-900 break-words">
                            {{ $documentRequest->student_name }}
                        </p>

                        @if ($documentRequest->user?->studentProfile?->student_id)
                            <p class="text-sm text-gray-500">
                                {{ $documentRequest->user->studentProfile->student_id }}
                            </p>
                        @endif
                    </div>

                </div>

            </div>

            {{-- Verification footer --}}
            <div class="px-6 sm:px-9 py-6 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">Verified on</p>
                <p class="mt-1 text-sm font-bold text-gray-700">{{ now()->format('F j, Y • g:i A') }}</p>

                <p class="mt-4 text-[11px] text-gray-400 leading-5">
                    This verification page is generated by the
                    ISUFSTPASS Digital Campus Transaction System.
                </p>

                <p class="mt-2 text-[11px] font-semibold text-gray-400">
                    Iloilo State University of Fisheries Science and Technology
                </p>
            </div>

        </div>

        <div class="text-center mt-6">
            <p class="text-xs font-bold text-blue-900">ISUFSTPASS</p>
            <p class="text-[10px] text-gray-400 mt-1">QR Code-Based Transaction Management System</p>
        </div>

    </main>

</body>
</html>
