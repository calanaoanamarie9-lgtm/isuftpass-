<x-app-layout>

    @php
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

        $statusClass = match ($documentRequest->status) {
            'approved' => 'bg-green-100 text-green-700 border-green-200',
            'ready_for_pickup' => 'bg-blue-100 text-blue-700 border-blue-200',
            'completed' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
            'cancelled' => 'bg-red-100 text-red-700 border-red-200',
            default => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        };
    @endphp


    <div class="min-h-screen bg-[#f5f7fa]">

        {{-- =========================================================
             TOP
        ========================================================== --}}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4">

            <a href="{{ route('student.documents.index') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-[#123b78] transition">
                <span class="text-lg">‹</span>
                Back to Requests
            </a>

            @include('student.documents._instructions', ['request' => $documentRequest])

        </div>


        {{-- =========================================================
             DIGITAL CLAIM PASS
        ========================================================== --}}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">

            <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">


                {{-- =====================================================
                     BLUE HEADER
                ====================================================== --}}
                <div class="relative bg-gradient-to-r from-[#0b1f46] via-[#123b78] to-[#1857a5] px-6 sm:px-10 py-7">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">

                        {{-- Logo + Title --}}
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
                                    <p class="text-[11px] font-semibold tracking-[0.18em] text-blue-200 uppercase">
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
                                Present this QR code to authorized staff for verification.
                            </p>

                        </div>


                        {{-- Status --}}
                        <div>
                            <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full
                                bg-green-400 text-green-950
                                text-xs font-black uppercase tracking-wide shadow-sm">

                                <span class="w-2 h-2 bg-green-900 rounded-full"></span>

                                {{ strtoupper($statusLabel) }}

                            </span>
                        </div>

                    </div>

                </div>



                {{-- =====================================================
                     REQUEST + QR
                ====================================================== --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">


                    {{-- =================================================
                         REQUEST INFORMATION
                    ================================================== --}}
                    <div class="p-6 sm:p-9">

                        <p class="text-[11px] font-black tracking-[0.18em]
                            text-[#174b91] uppercase mb-2">
                            Verification Information
                        </p>

                        <h2 class="text-2xl sm:text-3xl font-black text-gray-900">
                            {{ $documentNames ?: 'Document Request' }}
                        </h2>

                        <p class="mt-2 text-sm text-gray-500">
                            Your document request is being processed through ISUFSTPASS.
                        </p>


                        {{-- Information Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-7 mt-9">

                            <div>
                                <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                                    Reference Number
                                </p>

                                <p class="mt-1 text-sm font-black text-gray-900">
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
                                    {{ $documentRequest->documents->sum('pivot.quantity') ?: 1 }}
                                    {{ $documentRequest->documents->sum('pivot.quantity') == 1 ? 'copy' : 'copies' }}
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


                        {{-- Verification Instructions --}}
                        <div class="mt-9 p-4 rounded-2xl bg-blue-50 border border-blue-100">

                            <div class="flex items-start gap-3">

                                <div class="w-9 h-9 rounded-xl bg-[#2563b8] text-white
                                    flex items-center justify-center flex-shrink-0">
                                    ⓘ
                                </div>

                                <div>

                                    <h3 class="text-sm font-black text-[#17345f]">
                                        Verification Instructions
                                    </h3>

                                    <p class="mt-1 text-xs leading-5 text-gray-600">
                                        Keep this QR code available when visiting the university office.
                                        Staff may scan this code to verify your document request.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>



                    {{-- =================================================
                         QR CODE
                    ================================================== --}}
                    <div class="p-6 sm:p-9 flex flex-col items-center justify-center
                        bg-[#fbfcfe] border-t lg:border-t-0 lg:border-l border-gray-100">

                        <p class="text-[11px] font-black tracking-[0.2em]
                            text-gray-500 uppercase mb-4">
                            Scan to Verify
                        </p>


                        {{-- QR --}}
                        <div class="relative">

                            {{-- Yellow accent --}}
                            <div class="absolute -inset-3 bg-[#e1b000] rounded-[2rem] -z-0"></div>

                            <div class="relative bg-white rounded-[1.7rem]
                                border-2 border-gray-900 p-5 shadow-sm">

                                <img
                                    src="{{ $qrCodeDataUri }}"
                                    alt="Document Request QR Code"
                                    class="w-60 h-60 sm:w-64 sm:h-64 object-contain"
                                >

                                <p class="text-center mt-3 text-[10px]
                                    font-mono font-semibold text-gray-500 tracking-wide">
                                    {{ $documentRequest->request_number }}
                                </p>

                            </div>

                        </div>


                        {{-- Buttons --}}
                        <div class="w-full max-w-md grid grid-cols-1 sm:grid-cols-2 gap-3 mt-8">

                            <a
                                href="{{ route('student.documents.qr.download', $documentRequest) }}"
                                class="inline-flex items-center justify-center gap-2
                                px-5 py-3 rounded-xl
                                bg-[#0d244c] hover:bg-[#16396f]
                                text-white text-sm font-bold
                                transition shadow-sm">

                                <span>↓</span>
                                Download QR Code

                            </a>

                            <button
                                type="button"
                                onclick="openQRFullscreen()"
                                class="inline-flex items-center justify-center gap-2
                                px-5 py-3 rounded-xl
                                bg-white hover:bg-gray-50
                                border border-gray-900
                                text-gray-900 text-sm font-bold
                                transition">

                                <span>⛶</span>
                                View QR Fullscreen

                            </button>

                        </div>

                        {{-- The button above serves PNG, which a phone can
                             actually open. Keep vector reachable for print
                             and for scaling without loss. --}}
                        <div class="w-full max-w-md mt-2">
                            <a href="{{ route('student.documents.qr.download', ['documentRequest' => $documentRequest, 'format' => 'svg']) }}"
                               class="inline-flex items-center gap-1.5
                                      text-[11px] font-semibold text-gray-400
                                      hover:text-gray-600 transition">

                                Vector version (SVG) for printing

                            </a>
                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                 REMINDERS
            ========================================================== --}}
            <div class="mt-5 bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-sm">

                <div class="flex items-center gap-3 mb-4">

                    <div class="w-9 h-9 rounded-xl bg-yellow-100 text-yellow-700
                        flex items-center justify-center font-bold">
                        !
                    </div>

                    <div>
                        <p class="text-[10px] font-black tracking-[0.18em]
                            text-gray-400 uppercase">
                            Reminders
                        </p>

                        <h3 class="text-base font-black text-gray-900">
                            Important Information
                        </h3>
                    </div>

                </div>


                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    <div class="flex gap-2 text-sm text-gray-600">
                        <span class="text-[#174b91] font-bold">•</span>
                        <span>Present this QR code to the authorized personnel.</span>
                    </div>

                    <div class="flex gap-2 text-sm text-gray-600">
                        <span class="text-[#174b91] font-bold">•</span>
                        <span>This QR code is valid only for this document request.</span>
                    </div>

                    <div class="flex gap-2 text-sm text-gray-600">
                        <span class="text-[#174b91] font-bold">•</span>
                        <span>Do not share this QR code with other people.</span>
                    </div>

                    <div class="flex gap-2 text-sm text-gray-600">
                        <span class="text-[#174b91] font-bold">•</span>
                        <span>For concerns, please contact the Registrar's Office.</span>
                    </div>

                </div>

            </div>



            {{-- =========================================================
                 REQUEST PROGRESS
            ========================================================== --}}
            <div class="mt-5 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">

                <div class="mb-7">

                    <p class="text-[10px] font-black tracking-[0.18em]
                        text-[#174b91] uppercase">
                        Tracking
                    </p>

                    <h2 class="text-xl font-black text-gray-900 mt-1">
                        Request Progress
                    </h2>

                    <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">

                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>

                        <span>
                            Tracking live — updates automatically.
                        </span>

                    </div>

                </div>


                {{-- Timeline --}}
                <div class="relative">

                    @foreach($steps as $index => $step)

                        @php
                            $stepLabel = is_object($step)
                                ? $step->label()
                                : ucfirst(str_replace('_', ' ', $step));

                            $isCompleted = $index <= $current;
                            $isCurrent = $index === $current;
                        @endphp


                        <div class="relative flex gap-5 pb-9 last:pb-0">

                            {{-- Line --}}
                            @if(!$loop->last)
                                <div class="absolute left-[19px] top-10 bottom-0 w-[2px]
                                    {{ $index < $current ? 'bg-[#2459a5]' : 'bg-gray-200' }}">
                                </div>
                            @endif


                            {{-- Circle --}}
                            <div class="relative z-10 w-10 h-10 rounded-full
                                flex items-center justify-center flex-shrink-0
                                {{ $isCompleted
                                    ? 'bg-[#174b91] text-white'
                                    : 'bg-white border-2 border-gray-300 text-gray-400' }}">

                                @if($isCompleted)
                                    ✓
                                @else
                                    {{ $index + 1 }}
                                @endif

                            </div>


                            {{-- Content --}}
                            <div class="pt-1">

                                <div class="flex flex-wrap items-center gap-2">

                                    <h3 class="text-base font-black
                                        {{ $isCompleted ? 'text-gray-900' : 'text-gray-500' }}">
                                        {{ $stepLabel }}
                                    </h3>


                                    @if($isCurrent)
                                        <span class="px-2 py-0.5 rounded-md
                                            bg-blue-100 text-blue-700
                                            text-[9px] font-black uppercase tracking-wide">
                                            Current
                                        </span>
                                    @endif

                                </div>


                                <p class="mt-1 text-xs text-gray-500">

                                    @switch($stepLabel)

                                        @case('Submitted')
                                            Your request has been received.
                                            @break

                                        @case('Payment Completed')
                                        @case('Paid')
                                            Payment for your request has been recorded.
                                            @break

                                        @case('Approved')
                                            Your document request has been approved.
                                            @break

                                        @case('Ready for Release')
                                            Your document is ready for pickup.
                                            @break

                                        @case('Claimed')
                                            Your document has been successfully claimed.
                                            @break

                                        @default
                                            Your request is being processed.
                                    @endswitch

                                </p>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- =====================================================
                     ACTIVITY HISTORY
                ====================================================== --}}
                <div class="mt-10 pt-7 border-t border-gray-100">

                    <div class="flex items-center gap-2 mb-5">

                        <span class="text-gray-500">◷</span>

                        <h3 class="font-black text-gray-900">
                            Activity History
                        </h3>

                    </div>


                    <div class="space-y-4">

                        <div class="flex gap-3">

                            <span class="w-2 h-2 mt-2 rounded-full bg-blue-600 flex-shrink-0"></span>

                            <div>

                                <p class="text-sm text-gray-700">
                                    Request status:
                                    <span class="font-bold text-[#174b91]">
                                        {{ $statusLabel }}
                                    </span>
                                </p>

                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $documentRequest->updated_at?->format('M d, Y • g:i A') ?? 'N/A' }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- =========================================================
         FULLSCREEN QR MODAL
    ========================================================== --}}
    <div
        id="qrFullscreen"
        class="hidden fixed inset-0 z-[9999] bg-black/90
        items-center justify-center p-5"
    >

        <button
            type="button"
            onclick="closeQRFullscreen()"
            class="absolute top-5 right-5 w-11 h-11 rounded-full
            bg-white text-gray-900 font-bold text-xl shadow-lg"
        >
            ×
        </button>


        <div class="bg-white rounded-3xl p-7 text-center max-w-md w-full">

            <p class="text-xs font-black tracking-[0.2em] text-gray-500 uppercase">
                Scan to Verify
            </p>

            <div class="mt-5 p-4 border border-gray-200 rounded-2xl">
                <img
                    src="{{ $qrCodeDataUri }}"
                    alt="QR Code"
                    class="w-full max-w-sm mx-auto object-contain"
                >
            </div>

            <p class="mt-4 text-xs font-mono text-gray-500">
                {{ $documentRequest->request_number }}
            </p>

        </div>

    </div>


    <script>
        function openQRFullscreen() {
            const modal = document.getElementById('qrFullscreen');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.body.classList.add('overflow-hidden');
        }

        function closeQRFullscreen() {
            const modal = document.getElementById('qrFullscreen');

            modal.classList.add('hidden');
            modal.classList.remove('flex');

            document.body.classList.remove('overflow-hidden');
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeQRFullscreen();
            }
        });
    </script>

</x-app-layout>