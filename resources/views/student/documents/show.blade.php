<x-app-layout>

    @php
        /*
        |--------------------------------------------------------------------------
        | DOCUMENT REQUEST DATA
        |--------------------------------------------------------------------------
        */

        $steps = \App\Enums\DocumentRequestStatus::pipeline();

        $current = $documentRequest->pipelineIndex();

        $statusEnum = \App\Enums\DocumentRequestStatus::tryFrom(
            $documentRequest->status
        );

        $statusLabel = $statusEnum?->label()
            ?? ucfirst(str_replace('_', ' ', $documentRequest->status));

        $purposeLabel = \App\Enums\RequestPurposeType::tryFrom(
            $documentRequest->purpose_type
        )?->label()
            ?? ($documentRequest->purpose ?? 'N/A');

        $documentNames = $documentRequest->documents
            ->pluck('name')
            ->filter()
            ->join(', ');

        $totalFee = (float) $documentRequest->documents->sum('fee');

        $totalCopies = (int) $documentRequest->documents
            ->sum('pivot.quantity');

        $totalCopies = $totalCopies > 0 ? $totalCopies : 1;


        /*
        |--------------------------------------------------------------------------
        | STATUS STYLE
        |--------------------------------------------------------------------------
        */

        $statusClass = match ($documentRequest->status) {

            'submitted',
            'pending',
            'payment_pending' =>
                'bg-yellow-100 text-yellow-800 border-yellow-200',

            'paid',
            'processing' =>
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


        /*
        |--------------------------------------------------------------------------
        | STATUS DOT
        |--------------------------------------------------------------------------
        */

        $statusDotClass = match ($documentRequest->status) {

            'submitted',
            'pending',
            'payment_pending' =>
                'bg-yellow-500',

            'paid',
            'processing' =>
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


        /*
        |--------------------------------------------------------------------------
        | STATUS DESCRIPTION
        |--------------------------------------------------------------------------
        */

        $statusDescription = match ($documentRequest->status) {

            'submitted',
            'pending' =>
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
    @endphp


    {{-- ============================================================
         PAGE
    ============================================================= --}}
    <div class="min-h-screen bg-[#f5f7fa]">

        {{-- ============================================================
             TOP NAVIGATION
        ============================================================= --}}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4">

            <a
                href="{{ route('student.documents.index') }}"
                class="inline-flex items-center gap-2
                       text-sm font-semibold
                       text-gray-600
                       hover:text-[#123b78]
                       transition"
            >

                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>

                Back to Requests

            </a>


            {{-- Existing instruction component --}}
            @include(
                'student.documents._instructions',
                ['request' => $documentRequest]
            )

        </div>


        {{-- ============================================================
             MAIN CONTENT
        ============================================================= --}}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">


            {{-- ========================================================
                 DIGITAL CLAIM PASS
            ========================================================= --}}
            <div
                class="bg-white
                       rounded-3xl
                       shadow-sm
                       border border-gray-200
                       overflow-hidden"
            >

                {{-- ====================================================
                     BLUE HEADER
                ===================================================== --}}
                <div
                    class="relative
                           bg-gradient-to-r
                           from-[#0b1f46]
                           via-[#123b78]
                           to-[#1857a5]
                           px-6 sm:px-10
                           py-7"
                >

                    <div
                        class="flex flex-col
                               sm:flex-row
                               sm:items-center
                               sm:justify-between
                               gap-5"
                    >

                        {{-- =================================================
                             LOGO + TITLE
                        ================================================== --}}
                        <div>

                            <div
                                class="flex items-center
                                       gap-3 mb-5"
                            >

                                <div
                                    class="w-11 h-11
                                           rounded-full
                                           bg-white
                                           flex items-center
                                           justify-center
                                           overflow-hidden
                                           shadow"
                                >

                                    <img
                                        src="{{ asset('img/isufstpass-logo.png') }}"
                                        alt="ISUFSTPASS"
                                        class="w-9 h-9 object-contain"
                                    >

                                </div>


                                <div>

                                    <p
                                        class="text-[10px]
                                               sm:text-[11px]
                                               font-semibold
                                               tracking-[0.18em]
                                               text-blue-200
                                               uppercase"
                                    >
                                        ILOILO STATE UNIVERSITY
                                    </p>

                                    <h2
                                        class="text-lg
                                               font-black
                                               text-white
                                               tracking-wide"
                                    >
                                        ISUFSTPASS
                                    </h2>

                                </div>

                            </div>


                            <h1
                                class="text-3xl
                                       sm:text-4xl
                                       font-black
                                       text-white
                                       tracking-tight"
                            >
                                Digital Claim Pass
                            </h1>


                            <p
                                class="mt-1
                                       text-sm
                                       text-blue-100"
                            >
                                Present this QR code to authorized staff
                                for verification.
                            </p>

                        </div>


                        {{-- =================================================
                             STATUS
                        ================================================== --}}
                        <div>

                            <span
                                class="inline-flex
                                       items-center
                                       gap-2
                                       px-5 py-2
                                       rounded-full
                                       border
                                       {{ $statusClass }}
                                       text-xs
                                       font-black
                                       uppercase
                                       tracking-wide
                                       shadow-sm"
                            >

                                <span
                                    class="w-2 h-2
                                           rounded-full
                                           {{ $statusDotClass }}"
                                ></span>

                                {{ strtoupper($statusLabel) }}

                            </span>

                        </div>

                    </div>

                </div>


                {{-- ========================================================
                     REQUEST + QR
                ========================================================= --}}
                <div
                    class="grid
                           grid-cols-1
                           lg:grid-cols-2"
                >


                    {{-- ====================================================
                         REQUEST INFORMATION
                    ===================================================== --}}
                    <div class="p-6 sm:p-9">

                        <p
                            class="text-[11px]
                                   font-black
                                   tracking-[0.18em]
                                   text-[#174b91]
                                   uppercase
                                   mb-2"
                        >
                            Verification Information
                        </p>


                        <h2
                            class="text-2xl
                                   sm:text-3xl
                                   font-black
                                   text-gray-900
                                   leading-tight"
                        >
                            {{ $documentNames ?: 'Document Request' }}
                        </h2>


                        <p
                            class="mt-2
                                   text-sm
                                   text-gray-500
                                   leading-6"
                        >
                            {{ $statusDescription }}
                        </p>


                        {{-- =================================================
                             INFORMATION GRID
                        ================================================== --}}
                        <div
                            class="grid
                                   grid-cols-1
                                   sm:grid-cols-2
                                   gap-x-8
                                   gap-y-7
                                   mt-9"
                        >

                            {{-- Reference --}}
                            <div>

                                <p
                                    class="text-[10px]
                                           font-bold
                                           tracking-wider
                                           text-gray-400
                                           uppercase"
                                >
                                    Reference Number
                                </p>

                                <p
                                    class="mt-1
                                           text-sm
                                           font-black
                                           text-gray-900
                                           break-all"
                                >
                                    {{ $documentRequest->request_number }}
                                </p>

                            </div>


                            {{-- Date --}}
                            <div>

                                <p
                                    class="text-[10px]
                                           font-bold
                                           tracking-wider
                                           text-gray-400
                                           uppercase"
                                >
                                    Date Submitted
                                </p>

                                <p
                                    class="mt-1
                                           text-sm
                                           font-black
                                           text-gray-900"
                                >
                                    {{ $documentRequest->created_at?->format('M d, Y') ?? 'N/A' }}
                                </p>

                            </div>


                            {{-- Purpose --}}
                            <div>

                                <p
                                    class="text-[10px]
                                           font-bold
                                           tracking-wider
                                           text-gray-400
                                           uppercase"
                                >
                                    Purpose
                                </p>

                                <p
                                    class="mt-1
                                           text-sm
                                           font-black
                                           text-gray-900"
                                >
                                    {{ $purposeLabel }}
                                </p>

                            </div>


                            {{-- Copies --}}
                            <div>

                                <p
                                    class="text-[10px]
                                           font-bold
                                           tracking-wider
                                           text-gray-400
                                           uppercase"
                                >
                                    Copies
                                </p>

                                <p
                                    class="mt-1
                                           text-sm
                                           font-black
                                           text-gray-900"
                                >
                                    {{ $totalCopies }}
                                    {{ $totalCopies == 1 ? 'copy' : 'copies' }}
                                </p>

                            </div>


                            {{-- Claiming Office --}}
                            <div>

                                <p
                                    class="text-[10px]
                                           font-bold
                                           tracking-wider
                                           text-gray-400
                                           uppercase"
                                >
                                    Claiming Office
                                </p>

                                <p
                                    class="mt-1
                                           text-sm
                                           font-black
                                           text-gray-900"
                                >
                                    {{ $documentRequest->claiming_office ?? "Registrar's Office" }}
                                </p>

                            </div>


                            {{-- Total Fee --}}
                            <div>

                                <p
                                    class="text-[10px]
                                           font-bold
                                           tracking-wider
                                           text-gray-400
                                           uppercase"
                                >
                                    Total Fee
                                </p>

                                <p
                                    class="mt-1
                                           text-sm
                                           font-black
                                           text-[#1558ad]"
                                >
                                    ₱{{ number_format($totalFee, 2) }}
                                </p>

                            </div>

                        </div>


                        {{-- =================================================
                             VERIFICATION INSTRUCTIONS
                        ================================================== --}}
                        <div
                            class="mt-9
                                   p-4
                                   rounded-2xl
                                   bg-blue-50
                                   border border-blue-100"
                        >

                            <div
                                class="flex items-start gap-3"
                            >

                                <div
                                    class="w-9 h-9
                                           rounded-xl
                                           bg-[#2563b8]
                                           text-white
                                           flex items-center
                                           justify-center
                                           flex-shrink-0"
                                >

                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9"
                                            stroke-width="2"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-width="2"
                                            d="M12 10v6"
                                        />

                                        <circle
                                            cx="12"
                                            cy="7"
                                            r="1"
                                            fill="currentColor"
                                            stroke="none"
                                        />
                                    </svg>

                                </div>


                                <div>

                                    <h3
                                        class="text-sm
                                               font-black
                                               text-[#17345f]"
                                    >
                                        Verification Instructions
                                    </h3>


                                    <p
                                        class="mt-1
                                               text-xs
                                               leading-5
                                               text-gray-600"
                                    >
                                        Keep this QR code available when
                                        visiting the university office.
                                        Authorized staff may scan this code
                                        to verify your document request.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ====================================================
                         QR CODE
                    ===================================================== --}}
                    <div
                        class="p-6 sm:p-9
                               flex flex-col
                               items-center
                               justify-center
                               bg-[#fbfcfe]
                               border-t
                               lg:border-t-0
                               lg:border-l
                               border-gray-100"
                    >

                        <p
                            class="text-[11px]
                                   font-black
                                   tracking-[0.2em]
                                   text-gray-500
                                   uppercase
                                   mb-4"
                        >
                            Scan to Verify
                        </p>


                        {{-- =================================================
                             QR CONTAINER
                        ================================================== --}}
                        <div
                            class="relative
                                   inline-block"
                        >

                            {{-- Yellow accent --}}
                            <div
                                class="absolute
                                       -inset-3
                                       bg-[#e1b000]
                                       rounded-[2rem]"
                            ></div>


                            <div
                                class="relative
                                       bg-white
                                       rounded-[1.7rem]
                                       border-2
                                       border-gray-900
                                       p-5
                                       shadow-sm"
                            >

                                @if (!empty($qrCodeDataUri))

                                    <img
                                        src="{{ $qrCodeDataUri }}"
                                        alt="Document Request QR Code"
                                        class="w-60
                                               h-60
                                               sm:w-64
                                               sm:h-64
                                               object-contain"
                                    >

                                @else

                                    <div
                                        class="w-60 h-60
                                               sm:w-64 sm:h-64
                                               flex flex-col
                                               items-center
                                               justify-center
                                               text-center
                                               text-gray-400"
                                    >

                                        <svg
                                            class="w-12 h-12 mb-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M12 9v4m0 4h.01M10.29 3.86l-7.82 14a2 2 0 001.74 3h15.58a2 2 0 001.74-3l-7.82-14a2 2 0 00-3.48 0z"
                                            />
                                        </svg>

                                        <p
                                            class="text-sm
                                                   font-bold"
                                        >
                                            QR Code Unavailable
                                        </p>

                                    </div>

                                @endif


                                <p
                                    class="text-center
                                           mt-3
                                           text-[10px]
                                           font-mono
                                           font-semibold
                                           text-gray-500
                                           tracking-wide"
                                >
                                    {{ $documentRequest->request_number }}
                                </p>

                            </div>

                        </div>


                        {{-- =================================================
                             QR BUTTONS
                        ================================================== --}}
                        <div
                            class="w-full
                                   max-w-md
                                   grid
                                   grid-cols-1
                                   sm:grid-cols-2
                                   gap-3
                                   mt-8"
                        >

                            {{-- Download --}}
                            <a
                                href="{{ route('student.documents.qr.download', $documentRequest) }}"
                                class="inline-flex
                                       items-center
                                       justify-center
                                       gap-2
                                       px-5 py-3
                                       rounded-xl
                                       bg-[#0d244c]
                                       hover:bg-[#16396f]
                                       text-white
                                       text-sm
                                       font-bold
                                       transition
                                       shadow-sm"
                            >

                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 3v12m0 0l4-4m-4 4l-4-4M5 21h14"
                                    />
                                </svg>

                                Download QR Code

                            </a>


                            {{-- Fullscreen --}}
                            <button
                                type="button"
                                onclick="openQRFullscreen()"
                                class="inline-flex
                                       items-center
                                       justify-center
                                       gap-2
                                       px-5 py-3
                                       rounded-xl
                                       bg-white
                                       hover:bg-gray-50
                                       border border-gray-900
                                       text-gray-900
                                       text-sm
                                       font-bold
                                       transition"
                            >

                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 3H5a2 2 0 00-2 2v3m13-5h3a2 2 0 012 2v3M3 16v3a2 2 0 002 2h3m8 0h3a2 2 0 002-2v-3"
                                    />
                                </svg>

                                View QR Fullscreen

                            </button>

                        </div>


                        {{-- SVG --}}
                        <div
                            class="w-full
                                   max-w-md
                                   mt-3"
                        >

                            <a
                                href="{{ route('student.documents.qr.download', [
                                    'documentRequest' => $documentRequest,
                                    'format' => 'svg'
                                ]) }}"
                                class="inline-flex
                                       items-center
                                       gap-1.5
                                       text-[11px]
                                       font-semibold
                                       text-gray-400
                                       hover:text-gray-600
                                       transition"
                            >

                                <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4-4-4-4m6 8h4"
                                    />
                                </svg>

                                Vector version (SVG) for printing

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ============================================================
                 CANCEL / EDIT ACTIONS
            ============================================================= --}}

            <div
                class="mt-4
                       flex flex-wrap
                       items-center
                       gap-3"
            >

                {{-- Cancel --}}
                @can('cancel', $documentRequest)

                    <form
                        method="POST"
                        action="{{ route('student.documents.cancel', $documentRequest) }}"
                    >

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            onclick="return confirm('Are you sure you want to cancel this document request?')"
                            class="inline-flex
                                   items-center
                                   gap-2
                                   px-4 py-2.5
                                   rounded-xl
                                   bg-red-50
                                   border border-red-200
                                   text-red-600
                                   hover:bg-red-100
                                   hover:text-red-700
                                   text-sm
                                   font-semibold
                                   transition"
                        >

                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 6l12 12M18 6L6 18"
                                />
                            </svg>

                            Cancel Request

                        </button>

                    </form>

                @endcan


                {{-- Edit --}}
                @can('update', $documentRequest)

                    <a
                        href="{{ route('student.documents.edit', $documentRequest) }}"
                        class="inline-flex
                               items-center
                               gap-2
                               px-4 py-2.5
                               rounded-xl
                               bg-yellow-50
                               border border-yellow-200
                               text-yellow-700
                               hover:bg-yellow-100
                               text-sm
                               font-semibold
                               transition"
                    >

                        {{-- FIXED EDIT / PENCIL ICON --}}
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                            />
                        </svg>

                        Edit Request

                    </a>

                @endcan

            </div>


            {{-- ============================================================
                 REMINDERS
            ============================================================= --}}
            <div
                class="mt-5
                       bg-white
                       border border-gray-200
                       rounded-2xl
                       p-5 sm:p-6
                       shadow-sm"
            >

                <div
                    class="flex items-center
                           gap-3 mb-5"
                >

                    <div
                        class="w-9 h-9
                               rounded-xl
                               bg-yellow-100
                               text-yellow-700
                               flex items-center
                               justify-center
                               font-bold"
                    >
                        !
                    </div>


                    <div>

                        <p
                            class="text-[10px]
                                   font-black
                                   tracking-[0.18em]
                                   text-gray-400
                                   uppercase"
                        >
                            Reminders
                        </p>

                        <h3
                            class="text-base
                                   font-black
                                   text-gray-900"
                        >
                            Important Information
                        </h3>

                    </div>

                </div>


                <div
                    class="grid
                           grid-cols-1
                           sm:grid-cols-2
                           gap-3"
                >

                    <div
                        class="flex gap-2
                               text-sm
                               text-gray-600"
                    >
                        <span class="text-[#174b91] font-bold">•</span>

                        <span>
                            Present this QR code to authorized personnel.
                        </span>
                    </div>


                    <div
                        class="flex gap-2
                               text-sm
                               text-gray-600"
                    >
                        <span class="text-[#174b91] font-bold">•</span>

                        <span>
                            This QR code is valid only for this document request.
                        </span>
                    </div>


                    <div
                        class="flex gap-2
                               text-sm
                               text-gray-600"
                    >
                        <span class="text-[#174b91] font-bold">•</span>

                        <span>
                            Do not share this QR code with other people.
                        </span>
                    </div>


                    <div
                        class="flex gap-2
                               text-sm
                               text-gray-600"
                    >
                        <span class="text-[#174b91] font-bold">•</span>

                        <span>
                            For concerns, please contact the Registrar's Office.
                        </span>
                    </div>

                </div>

            </div>


            {{-- ============================================================
                 REQUEST PROGRESS
            ============================================================= --}}
            <div
                class="mt-5
                       bg-white
                       rounded-2xl
                       border border-gray-200
                       shadow-sm
                       p-6 sm:p-8"
            >

                <div class="mb-7">

                    <p
                        class="text-[10px]
                               font-black
                               tracking-[0.18em]
                               text-[#174b91]
                               uppercase"
                    >
                        Tracking
                    </p>


                    <h2
                        class="text-xl
                               font-black
                               text-gray-900
                               mt-1"
                    >
                        Request Progress
                    </h2>


                    <div
                        class="flex items-center
                               gap-2 mt-3
                               text-xs
                               text-gray-500"
                    >

                        <span
                            class="w-2 h-2
                                   rounded-full
                                   bg-blue-600"
                        ></span>

                        <span>
                            Tracking live — updates automatically.
                        </span>

                    </div>

                </div>


                {{-- ========================================================
                     TIMELINE
                ========================================================= --}}
                <div class="relative">

                    @foreach ($steps as $index => $step)

                        @php

                            $stepLabel = is_object($step)
                                ? $step->label()
                                : ucfirst(str_replace('_', ' ', $step));

                            $stepLabelNormalized = strtolower(
                                str_replace(['_', '-'], ' ', $stepLabel)
                            );

                            $isCompleted = $index <= $current;

                            $isCurrent = $index === $current;

                        @endphp


                        <div
                            class="relative
                                   flex gap-5
                                   pb-9
                                   last:pb-0"
                        >

                            {{-- =================================================
                                 CONNECTING LINE
                            ================================================== --}}
                            @if (!$loop->last)

                                <div
                                    class="absolute
                                           left-[19px]
                                           top-10
                                           bottom-0
                                           w-[2px]
                                           {{ $index < $current
                                                ? 'bg-[#2459a5]'
                                                : 'bg-gray-200' }}"
                                ></div>

                            @endif


                            {{-- =================================================
                                 STEP CIRCLE
                            ================================================== --}}
                            <div
                                class="relative
                                       z-10
                                       w-10 h-10
                                       rounded-full
                                       flex items-center
                                       justify-center
                                       flex-shrink-0
                                       {{ $isCompleted
                                            ? 'bg-[#174b91] text-white'
                                            : 'bg-white border-2 border-gray-300 text-gray-400' }}"
                            >

                                @if ($isCompleted)

                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2.5"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>

                                @else

                                    {{ $index + 1 }}

                                @endif

                            </div>


                            {{-- =================================================
                                 STEP CONTENT
                            ================================================== --}}
                            <div class="pt-1 min-w-0">

                                <div
                                    class="flex
                                           flex-wrap
                                           items-center
                                           gap-2"
                                >

                                    <h3
                                        class="text-base
                                               font-black
                                               {{ $isCompleted
                                                    ? 'text-gray-900'
                                                    : 'text-gray-500' }}"
                                    >
                                        {{ $stepLabel }}
                                    </h3>


                                    @if ($isCurrent)

                                        <span
                                            class="px-2 py-0.5
                                                   rounded-md
                                                   bg-blue-100
                                                   text-blue-700
                                                   text-[9px]
                                                   font-black
                                                   uppercase
                                                   tracking-wide"
                                        >
                                            Current
                                        </span>

                                    @endif

                                </div>


                                <p
                                    class="mt-1
                                           text-xs
                                           text-gray-500
                                           leading-5"
                                >

                                    @if (
                                        str_contains($stepLabelNormalized, 'submitted')
                                    )

                                        Your request has been received.

                                    @elseif (
                                        str_contains($stepLabelNormalized, 'payment')
                                        || $stepLabelNormalized === 'paid'
                                    )

                                        Payment for your request has been recorded.

                                    @elseif (
                                        str_contains($stepLabelNormalized, 'approved')
                                    )

                                        Your document request has been approved.

                                    @elseif (
                                        str_contains($stepLabelNormalized, 'ready')
                                        || str_contains($stepLabelNormalized, 'release')
                                    )

                                        Your document is ready for pickup.

                                    @elseif (
                                        str_contains($stepLabelNormalized, 'claimed')
                                        || str_contains($stepLabelNormalized, 'completed')
                                    )

                                        Your document has been successfully claimed.

                                    @else

                                        Your request is being processed.

                                    @endif

                                </p>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- ========================================================
                     ACTIVITY HISTORY
                ========================================================= --}}
                <div
                    class="mt-10
                           pt-7
                           border-t
                           border-gray-100"
                >

                    <div
                        class="flex
                               items-center
                               gap-2
                               mb-5"
                    >

                        <svg
                            class="w-5 h-5 text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                                stroke-width="2"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-width="2"
                                d="M12 7v5l3 2"
                            />
                        </svg>


                        <h3
                            class="font-black
                                   text-gray-900"
                        >
                            Activity History
                        </h3>

                    </div>


                    <div class="space-y-4">

                        <div class="flex gap-3">

                            <span
                                class="w-2 h-2
                                       mt-2
                                       rounded-full
                                       bg-blue-600
                                       flex-shrink-0"
                            ></span>


                            <div>

                                <p
                                    class="text-sm
                                           text-gray-700"
                                >
                                    Request status:

                                    <span
                                        class="font-bold
                                               text-[#174b91]"
                                    >
                                        {{ $statusLabel }}
                                    </span>
                                </p>


                                <p
                                    class="text-xs
                                           text-gray-400
                                           mt-1"
                                >
                                    {{ $documentRequest->updated_at?->format('M d, Y • g:i A') ?? 'N/A' }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
         FULLSCREEN QR MODAL
    ================================================================= --}}
    <div
        id="qrFullscreen"
        class="hidden
               fixed inset-0
               z-[9999]
               bg-black/90
               items-center
               justify-center
               p-4 sm:p-5"
        role="dialog"
        aria-modal="true"
        aria-label="QR Code Fullscreen"
    >

        {{-- Close --}}
        <button
            type="button"
            onclick="closeQRFullscreen()"
            aria-label="Close QR fullscreen"
            class="absolute
                   top-4 right-4
                   sm:top-5 sm:right-5
                   w-11 h-11
                   rounded-full
                   bg-white
                   text-gray-900
                   font-bold
                   text-xl
                   shadow-lg
                   hover:bg-gray-100
                   transition"
        >
            ×
        </button>


        {{-- Modal Card --}}
        <div
            class="bg-white
                   rounded-3xl
                   p-5 sm:p-7
                   text-center
                   max-w-md
                   w-full
                   max-h-[90vh]
                   overflow-y-auto"
        >

            <p
                class="text-xs
                       font-black
                       tracking-[0.2em]
                       text-gray-500
                       uppercase"
            >
                Scan to Verify
            </p>


            @if (!empty($qrCodeDataUri))

                <div
                    class="mt-5
                           p-4
                           border
                           border-gray-200
                           rounded-2xl
                           bg-white"
                >

                    <img
                        src="{{ $qrCodeDataUri }}"
                        alt="Document Request QR Code"
                        class="w-full
                               max-w-sm
                               mx-auto
                               object-contain"
                    >

                </div>

            @else

                <div
                    class="mt-5
                           aspect-square
                           flex items-center
                           justify-center
                           rounded-2xl
                           bg-gray-50
                           border
                           border-gray-200"
                >

                    <p
                        class="text-sm
                               font-semibold
                               text-gray-400"
                    >
                        QR Code unavailable.
                    </p>

                </div>

            @endif


            <p
                class="mt-4
                       text-xs
                       font-mono
                       text-gray-500
                       break-all"
            >
                {{ $documentRequest->request_number }}
            </p>


            <button
                type="button"
                onclick="closeQRFullscreen()"
                class="mt-5
                       w-full
                       px-5 py-3
                       rounded-xl
                       bg-[#0d244c]
                       hover:bg-[#16396f]
                       text-white
                       text-sm
                       font-bold
                       transition"
            >
                Close
            </button>

        </div>

    </div>


    {{-- ================================================================
         JAVASCRIPT
    ================================================================= --}}
    <script>

        function openQRFullscreen() {

            const modal = document.getElementById('qrFullscreen');

            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.body.classList.add('overflow-hidden');

        }


        function closeQRFullscreen() {

            const modal = document.getElementById('qrFullscreen');

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');

            document.body.classList.remove('overflow-hidden');

        }


        /*
        |--------------------------------------------------------------------------
        | ESCAPE KEY
        |--------------------------------------------------------------------------
        */

        document.addEventListener('keydown', function (event) {

            if (event.key === 'Escape') {

                closeQRFullscreen();

            }

        });


        /*
        |--------------------------------------------------------------------------
        | CLICK OUTSIDE MODAL
        |--------------------------------------------------------------------------
        */

        document.addEventListener('click', function (event) {

            const modal = document.getElementById('qrFullscreen');

            if (!modal) {
                return;
            }

            if (
                !modal.classList.contains('hidden') &&
                event.target === modal
            ) {

                closeQRFullscreen();

            }

        });

    </script>

</x-app-layout>