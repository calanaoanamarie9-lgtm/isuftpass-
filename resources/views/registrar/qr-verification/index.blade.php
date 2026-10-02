<x-app-layout>

    <div class="min-h-screen bg-[#f4f7fb] py-8">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="mb-8">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                    <div>
                        <div class="flex items-center gap-3">

                            <div class="w-12 h-12 rounded-2xl bg-blue-900
                                        flex items-center justify-center
                                        shadow-lg shadow-blue-900/20">

                                <svg class="w-6 h-6 text-yellow-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 4v1m6.364 1.636l-.707.707M20 12h-1M18.364 18.364l-.707-.707M12 19v1M6.343 17.657l-.707.707M5 12H4m2.343-5.657l-.707-.707M9 12a3 3 0 106 0 3 3 0 00-6 0z"/>
                                </svg>

                            </div>

                            <div>
                                <h1 class="text-2xl sm:text-3xl font-extrabold text-blue-950">
                                    QR Pass Verification
                                </h1>

                                <p class="text-sm text-gray-500 mt-1">
                                    Verify student identity, document requests, and appointments.
                                </p>
                            </div>

                        </div>
                    </div>

                    {{-- VERIFIED BADGE --}}
                    <div class="inline-flex items-center gap-2
                                px-4 py-2 rounded-full
                                bg-white border border-blue-100
                                shadow-sm">

                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>

                        <span class="text-xs font-bold text-gray-600">
                            ISUFSTPASS SECURE VERIFICATION
                        </span>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                QR VERIFICATION CARD
            ========================================================== --}}
            <div class="relative overflow-hidden
                        bg-gradient-to-br from-blue-950 via-blue-900 to-indigo-950
                        rounded-3xl shadow-xl shadow-blue-950/20
                        mb-7">

                {{-- Decorative circles --}}
                <div class="absolute -top-20 -right-20 w-64 h-64
                            rounded-full bg-blue-800/30"></div>

                <div class="absolute -bottom-24 -left-20 w-72 h-72
                            rounded-full bg-indigo-800/20"></div>

                <div class="relative p-6 sm:p-8">

                    <div class="flex items-start gap-4 mb-6">

                        <div class="w-12 h-12 rounded-2xl
                                    bg-yellow-400
                                    flex items-center justify-center
                                    shrink-0 shadow-lg">

                            <svg class="w-6 h-6 text-blue-950"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 4a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM15 4a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V4zM3 16a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1v-4zM16 15h1m-1 4h1m3-4h1m-1 4h1M12 12h.01"/>
                            </svg>

                        </div>

                        <div>

                            <h2 class="text-lg font-extrabold text-white">
                                Verify QR Pass
                            </h2>

                            <p class="text-sm text-blue-200 mt-1">
                                Scan a student's QR code or enter their identifier below.
                            </p>

                        </div>

                    </div>


                    <form method="GET"
                          action="{{ route('registrar.qr.index') }}">

                        <div class="flex flex-col sm:flex-row gap-3">

                            <div class="relative flex-1">

                                <svg class="absolute left-4 top-1/2
                                            -translate-y-1/2
                                            w-5 h-5 text-gray-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>

                                </svg>

                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $query }}"
                                    id="qr-input"
                                    placeholder="Scan QR code, paste QR link, name, or email"
                                    autofocus
                                    class="w-full pl-12 pr-4 py-3.5
                                           rounded-2xl
                                           border-0
                                           bg-white
                                           text-sm text-gray-800
                                           shadow-lg
                                           focus:ring-4
                                           focus:ring-yellow-400/30
                                           focus:outline-none"
                                >

                            </div>

                            <button type="submit"
                                    class="inline-flex items-center justify-center
                                           gap-2 px-7 py-3.5
                                           rounded-2xl
                                           bg-yellow-400
                                           text-blue-950
                                           text-sm font-extrabold
                                           shadow-lg
                                           hover:bg-yellow-300
                                           transition">

                                <svg class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 5l7 7-7 7"/>

                                </svg>

                                Verify

                            </button>

                        </div>


                        {{-- CAMERA BUTTON --}}
                        <button type="button"
                                onclick="startQrCamera()"
                                class="mt-4 w-full
                                       inline-flex items-center
                                       justify-center gap-2
                                       px-5 py-3
                                       rounded-2xl
                                       border border-white/20
                                       bg-white/10
                                       backdrop-blur-sm
                                       text-white
                                       text-sm font-bold
                                       hover:bg-white/20
                                       transition">

                            <svg class="w-5 h-5 text-yellow-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 7h4l2-3h6l2 3h4v12H3V7z"/>

                                <circle cx="12"
                                        cy="13"
                                        r="3"
                                        stroke-width="2"/>

                            </svg>

                            Scan QR Code with Camera

                        </button>


                        {{-- QR CAMERA --}}
                        <div id="qr-reader"
                             class="hidden mt-4
                                    overflow-hidden
                                    rounded-2xl
                                    border border-white/20
                                    bg-black">
                        </div>


                        <div class="flex items-start gap-2 mt-4">

                            <svg class="w-4 h-4 text-blue-300 mt-0.5 shrink-0"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-8h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>

                            </svg>

                            <p class="text-xs text-blue-200 leading-relaxed">
                                USB QR scanners are also supported. Keep the input field focused
                                while scanning a student pass, claim slip, or appointment QR.
                            </p>

                        </div>

                    </form>

                </div>

            </div>


            {{-- =========================================================
                QR SCANNER SCRIPT
            ========================================================== --}}
            <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

            <script>

                let qrScanner = null;

                function startQrCamera() {

                    const box = document.getElementById('qr-reader');

                    if (typeof Html5Qrcode === 'undefined') {

                        alert(
                            'Camera scanner could not load. Check your internet connection or use a USB scanner.'
                        );

                        return;
                    }

                    box.classList.remove('hidden');

                    if (qrScanner) return;

                    qrScanner = new Html5Qrcode('qr-reader');

                    qrScanner.start(
                        {
                            facingMode: 'environment'
                        },
                        {
                            fps: 10,
                            qrbox: {
                                width: 230,
                                height: 230
                            }
                        },
                        (decoded) => {

                            stopQrCamera();

                            const input =
                                document.getElementById('qr-input');

                            input.value = decoded.trim();

                            input.closest('form').submit();

                        },
                        () => {}
                    ).catch((err) => {

                        box.classList.add('hidden');

                        alert(
                            'Unable to access the camera: ' + err
                        );

                    });
                }


                function stopQrCamera() {

                    if (!qrScanner) return;

                    qrScanner.stop()
                        .then(() => {

                            document
                                .getElementById('qr-reader')
                                .classList.add('hidden');

                            qrScanner.clear();

                            qrScanner = null;

                        })
                        .catch(() => {});

                }

            </script>


            {{-- =========================================================
                DOCUMENT REQUEST
            ========================================================== --}}
            @if ($documentRequest)

                <div class="bg-white rounded-3xl
                            border border-blue-100
                            shadow-sm overflow-hidden mb-6">

                    {{-- Header --}}
                    <div class="px-6 py-5
                                bg-gradient-to-r
                                from-blue-50 to-indigo-50
                                border-b border-blue-100">

                        <div class="flex flex-wrap
                                    items-center justify-between gap-4">

                            <div class="flex items-center gap-3">

                                <div class="w-11 h-11 rounded-xl
                                            bg-blue-900
                                            flex items-center justify-center">

                                    <svg class="w-5 h-5 text-yellow-400"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 13h6m-6 4h6M9 9h2"/>

                                    </svg>

                                </div>

                                <div>

                                    <p class="text-xs font-bold
                                              uppercase tracking-widest
                                              text-blue-500">

                                        Transaction Verification

                                    </p>

                                    <h2 class="text-lg font-extrabold
                                               text-blue-950">

                                        Document Request Found

                                    </h2>

                                </div>

                            </div>


                            <span class="inline-flex items-center
                                         px-3.5 py-1.5
                                         rounded-full
                                         text-xs font-extrabold

                                @if ($documentRequest->status === 'cancelled')
                                    bg-red-50 text-red-600 ring-1 ring-red-200

                                @elseif ($documentRequest->status === 'completed')
                                    bg-blue-50 text-blue-700 ring-1 ring-blue-200

                                @elseif ($documentRequest->status === 'ready_for_pickup')
                                    bg-green-50 text-green-700 ring-1 ring-green-200

                                @else
                                    bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200
                                @endif">

                                <span class="w-1.5 h-1.5 rounded-full
                                    mr-2

                                    @if ($documentRequest->status === 'cancelled')
                                        bg-red-500

                                    @elseif ($documentRequest->status === 'completed')
                                        bg-blue-500

                                    @elseif ($documentRequest->status === 'ready_for_pickup')
                                        bg-green-500

                                    @else
                                        bg-yellow-500
                                    @endif">
                                </span>

                                {{ \App\Enums\DocumentRequestStatus::tryFrom($documentRequest->status)?->label() }}

                            </span>

                        </div>

                    </div>


                    {{-- Content --}}
                    <div class="p-6">

                        <div class="grid sm:grid-cols-2 gap-4">

                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[11px]
                                          font-bold uppercase
                                          tracking-widest
                                          text-gray-400">

                                    Reference Number

                                </p>

                                <p class="mt-1 text-sm font-extrabold
                                          text-blue-950">

                                    {{ $documentRequest->request_number }}

                                </p>

                            </div>


                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[11px]
                                          font-bold uppercase
                                          tracking-widest
                                          text-gray-400">

                                    Student

                                </p>

                                <p class="mt-1 text-sm font-extrabold
                                          text-blue-950">

                                    {{ $documentRequest->student_name }}

                                </p>

                            </div>

                        </div>


                        {{-- DOCUMENTS --}}
                        <div class="mt-6">

                            <p class="text-[11px]
                                      font-bold uppercase
                                      tracking-widest
                                      text-gray-400 mb-3">

                                Requested Document(s)

                            </p>

                            <div class="flex flex-wrap gap-2">

                                @foreach ($documentRequest->documents as $doc)

                                    <span class="inline-flex items-center
                                                 gap-2 px-3.5 py-2
                                                 rounded-xl
                                                 bg-blue-50
                                                 border border-blue-100
                                                 text-xs font-bold
                                                 text-blue-800">

                                        <span class="w-2 h-2 rounded-full
                                                     bg-blue-600">
                                        </span>

                                        {{ $doc->name }}

                                        <span class="text-blue-400">
                                            ₱{{ number_format($doc->fee, 2) }}
                                        </span>

                                    </span>

                                @endforeach


                                @if ($documentRequest->others_specification)

                                    <span class="inline-flex items-center
                                                 px-3.5 py-2
                                                 rounded-xl
                                                 bg-indigo-50
                                                 border border-indigo-100
                                                 text-xs font-bold
                                                 text-indigo-700">

                                        Others:
                                        {{ $documentRequest->others_specification }}

                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- CLAIM BUTTON --}}
                        @if ($documentRequest->status === 'ready_for_pickup')

                            @unless ($documentRequest->isPaid())

                                <div class="mt-6 rounded-2xl
                                            border border-amber-200
                                            bg-amber-50
                                            px-4 py-3.5">

                                    <p class="text-xs font-bold text-amber-800">
                                        Payment not yet recorded
                                    </p>

                                    <p class="mt-1
                                              text-[11px]
                                              leading-relaxed
                                              text-amber-700">

                                        This request cannot be released until the
                                        cashier records the payment.

                                    </p>

                                </div>

                            @endunless

                            <form method="POST"
                                  action="{{ route('registrar.document-requests.next', $documentRequest) }}"
                                  data-confirm="Verify that the student is present and release the documents."
                                  data-confirm-title="Mark this request as claimed?"
                                  data-confirm-ok="Yes, release documents"
                                  data-confirm-icon="success">

                                @csrf

                                <button type="submit"
                                        @disabled(! $documentRequest->isPaid())
                                        class="mt-6 w-full
                                               inline-flex items-center
                                               justify-center gap-2
                                               px-5 py-3.5
                                               rounded-2xl
                                               text-white
                                               text-sm font-extrabold
                                               shadow-lg transition
                                               {{ $documentRequest->isPaid()
                                                   ? 'bg-green-600 shadow-green-600/10 hover:bg-green-700'
                                                   : 'bg-gray-300 cursor-not-allowed' }}">

                                    <svg class="w-5 h-5"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M5 13l4 4L19 7"/>

                                    </svg>

                                    @if ($documentRequest->isPaid())
                                        Verify &amp; Claim / Release Documents
                                    @else
                                        Payment required first
                                    @endif

                                </button>

                            </form>

                        @endif

                    </div>

                </div>

            @endif


            {{-- =========================================================
                APPOINTMENT
            ========================================================== --}}
            @if ($appointment)

                <div class="bg-white rounded-3xl
                            border border-cyan-100
                            shadow-sm overflow-hidden mb-6">

                    {{-- Header --}}
                    <div class="px-6 py-5
                                bg-gradient-to-r
                                from-cyan-50 to-blue-50
                                border-b border-cyan-100">

                        <div class="flex flex-wrap
                                    items-center justify-between gap-4">

                            <div class="flex items-center gap-3">

                                <div class="w-11 h-11 rounded-xl
                                            bg-cyan-700
                                            flex items-center justify-center">

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

                                    <p class="text-xs font-bold
                                              uppercase tracking-widest
                                              text-cyan-600">

                                        Schedule Verification

                                    </p>

                                    <h2 class="text-lg font-extrabold
                                               text-blue-950">

                                        Appointment Found

                                    </h2>

                                </div>

                            </div>


                            <span class="inline-flex items-center
                                         px-3.5 py-1.5
                                         rounded-full
                                         text-xs font-extrabold

                                @if ($appointment->status === 'confirmed')
                                    bg-green-50 text-green-700 ring-1 ring-green-200

                                @elseif ($appointment->status === 'checked_in')
                                    bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200

                                @elseif ($appointment->status === 'rescheduled')
                                    bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200

                                @else
                                    bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200
                                @endif">

                                {{ \App\Enums\AppointmentStatus::tryFrom($appointment->status)?->label() }}

                            </span>

                        </div>

                    </div>


                    <div class="p-6">

                        <div class="grid sm:grid-cols-2
                                    lg:grid-cols-4 gap-4">

                            {{-- Reference --}}
                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[11px]
                                          font-bold uppercase
                                          tracking-widest text-gray-400">

                                    Reference

                                </p>

                                <p class="mt-1 text-sm font-extrabold
                                          text-blue-950">

                                    {{ $appointment->reference_code }}

                                </p>

                            </div>


                            {{-- Office --}}
                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[11px]
                                          font-bold uppercase
                                          tracking-widest text-gray-400">

                                    Office

                                </p>

                                <p class="mt-1 text-sm font-extrabold
                                          text-blue-950">

                                    {{ $appointment->office }}

                                </p>

                            </div>


                            {{-- Date --}}
                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[11px]
                                          font-bold uppercase
                                          tracking-widest text-gray-400">

                                    Date

                                </p>

                                <p class="mt-1 text-sm font-extrabold
                                          text-blue-950">

                                    {{ $appointment->date->format('M j, Y') }}

                                </p>

                            </div>


                            {{-- Time --}}
                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[11px]
                                          font-bold uppercase
                                          tracking-widest text-gray-400">

                                    Time Slot

                                </p>

                                <p class="mt-1 text-sm font-extrabold
                                          text-blue-950">

                                    {{ $appointment->time_slot }}

                                </p>

                            </div>

                        </div>


                        {{-- PURPOSE --}}
                        <div class="mt-5 rounded-2xl
                                    bg-blue-50
                                    border border-blue-100
                                    p-5">

                            <p class="text-[11px]
                                      font-bold uppercase
                                      tracking-widest
                                      text-blue-400">

                                Purpose

                            </p>

                            <p class="mt-1 text-sm font-bold
                                      text-blue-950">

                                {{ $appointment->purpose }}

                            </p>

                        </div>


                        @if ($appointment->user && $student)

                            <a href="{{ route('registrar.appointments.show', $appointment) }}"
                               class="mt-5 w-full
                                      inline-flex items-center
                                      justify-center gap-2
                                      px-5 py-3.5
                                      rounded-2xl
                                      bg-blue-900
                                      text-white
                                      text-sm font-extrabold
                                      hover:bg-blue-950
                                      transition">

                                Open Appointment Details

                                <svg class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M13 7l5 5m0 0l-5 5m5-5H6"/>

                                </svg>

                            </a>

                        @endif

                    </div>

                </div>

            @endif


            {{-- =========================================================
                NO STUDENT FOUND
            ========================================================== --}}
            @if ($query && ! $student)

                <div class="bg-white rounded-3xl
                            border border-red-100
                            shadow-sm p-10 text-center">

                    <div class="mx-auto w-16 h-16
                                rounded-2xl
                                bg-red-50
                                flex items-center justify-center">

                        <svg class="w-8 h-8 text-red-500"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>

                        </svg>

                    </div>

                    <h2 class="mt-5 text-lg font-extrabold
                               text-gray-900">

                        No Student Found

                    </h2>

                    <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">

                        The QR code or identifier does not match
                        any registered student.

                    </p>

                    <p class="mt-4 text-xs text-gray-400">

                        Please check the QR code or try searching
                        using the student's name or email.

                    </p>

                </div>

            @endif


            {{-- =========================================================
                VERIFIED STUDENT
            ========================================================== --}}
            @if ($student)

                <div class="bg-white rounded-3xl
                            border border-green-100
                            shadow-sm overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-5
                                bg-gradient-to-r
                                from-green-50 to-emerald-50
                                border-b border-green-100">

                        <div class="flex items-center gap-3">

                            <div class="w-11 h-11 rounded-xl
                                        bg-green-600
                                        flex items-center justify-center">

                                <svg class="w-6 h-6 text-white"
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

                                <p class="text-xs font-bold
                                          uppercase tracking-widest
                                          text-green-600">

                                    Identity Verification

                                </p>

                                <h2 class="text-lg font-extrabold
                                           text-green-900">

                                    Verified Student

                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="p-6">

                        {{-- Student Profile --}}
                        <div class="flex flex-col sm:flex-row
                                    sm:items-center gap-5">

                            <div class="relative shrink-0">

                                <div class="w-20 h-20 rounded-2xl
                                            bg-blue-950
                                            overflow-hidden
                                            flex items-center justify-center
                                            text-yellow-400
                                            font-extrabold text-2xl
                                            shadow-lg">

                                    @if ($student->studentProfile?->avatar)

                                        <img
                                            src="{{ Storage::url($student->studentProfile->avatar) }}"
                                            alt="Avatar"
                                            class="w-full h-full object-cover">

                                    @else

                                        {{ substr($student->name, 0, 1) }}

                                    @endif

                                </div>

                                <div class="absolute -right-2 -bottom-2
                                            w-7 h-7 rounded-full
                                            bg-green-500
                                            border-4 border-white
                                            flex items-center justify-center">

                                    <svg class="w-3.5 h-3.5 text-white"
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


                            <div class="flex-1 min-w-0">

                                <div class="flex flex-wrap
                                            items-center gap-2">

                                    <h2 class="text-xl font-extrabold
                                               text-blue-950">

                                        {{ $student->name }}

                                    </h2>

                                    <span class="px-2.5 py-1 rounded-full
                                                 bg-green-50
                                                 text-green-700
                                                 text-[10px]
                                                 font-extrabold
                                                 uppercase">

                                        Verified

                                    </span>

                                </div>

                                <p class="text-sm text-gray-500 mt-1">

                                    {{ $student->email }}

                                </p>

                                <p class="text-xs text-gray-400 mt-1">

                                    {{ $student->studentProfile?->course ?: 'No course set' }}

                                    @if ($student->studentProfile?->year_level)

                                        &middot;
                                        Year {{ $student->studentProfile->year_level }}

                                    @endif

                                </p>

                            </div>

                        </div>


                        {{-- Student Info --}}
                        <div class="mt-6 grid sm:grid-cols-2 gap-4">

                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[11px]
                                          font-bold uppercase
                                          tracking-widest
                                          text-gray-400">

                                    Account Status

                                </p>

                                <div class="flex items-center gap-2 mt-2">

                                    <span class="w-2 h-2
                                                 rounded-full
                                                 bg-green-500">
                                    </span>

                                    <p class="text-sm font-extrabold
                                              text-green-700">

                                        Active Student Account

                                    </p>

                                </div>

                            </div>


                            <div class="rounded-2xl
                                        bg-[#f4f7fb]
                                        border border-gray-100
                                        p-4">

                                <p class="text-[11px]
                                          font-bold uppercase
                                          tracking-widest
                                          text-gray-400">

                                    Member Since

                                </p>

                                <p class="mt-2 text-sm font-extrabold
                                          text-blue-950">

                                    {{ $student->created_at->format('M j, Y') }}

                                </p>

                            </div>

                        </div>


                        {{-- =================================================
                            RECENT TRANSACTIONS
                        ================================================== --}}
                        <div class="mt-8">

                            <div class="flex items-center
                                        justify-between gap-3 mb-3">

                                <h3 class="text-xs font-extrabold
                                           uppercase tracking-widest
                                           text-gray-400">

                                    Recent Transactions

                                </h3>

                                <span class="text-[10px]
                                             font-bold
                                             text-blue-600">

                                    ISUFSTPASS RECORD

                                </span>

                            </div>


                            <div class="space-y-2">

                                @forelse ($student->documentRequests as $request)

                                    <div class="group
                                                flex items-center
                                                justify-between
                                                gap-3
                                                rounded-2xl
                                                border border-gray-100
                                                bg-white
                                                px-4 py-3.5
                                                hover:border-blue-100
                                                hover:bg-blue-50/30
                                                transition">

                                        <div class="flex items-center
                                                    gap-3 min-w-0">

                                            <div class="w-9 h-9
                                                        rounded-xl
                                                        bg-blue-50
                                                        flex items-center
                                                        justify-center
                                                        shrink-0">

                                                <svg class="w-4 h-4 text-blue-700"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>

                                                </svg>

                                            </div>

                                            <p class="text-sm font-semibold
                                                      text-gray-800 truncate">

                                                {{ $request->documentsSummary() }}

                                            </p>

                                        </div>

                                        <span class="shrink-0
                                                     px-2.5 py-1
                                                     rounded-full
                                                     bg-blue-50
                                                     text-blue-700
                                                     text-[10px]
                                                     font-extrabold">

                                            {{ \App\Enums\DocumentRequestStatus::tryFrom($request->status)?->label() }}

                                        </span>

                                    </div>

                                @empty

                                    <p class="text-sm text-gray-400
                                              py-3">

                                        No recent document requests.

                                    </p>

                                @endforelse


                                @forelse ($student->appointments as $appointment)

                                    <div class="group
                                                flex items-center
                                                justify-between
                                                gap-3
                                                rounded-2xl
                                                border border-gray-100
                                                bg-white
                                                px-4 py-3.5
                                                hover:border-cyan-100
                                                hover:bg-cyan-50/30
                                                transition">

                                        <div class="flex items-center
                                                    gap-3 min-w-0">

                                            <div class="w-9 h-9
                                                        rounded-xl
                                                        bg-cyan-50
                                                        flex items-center
                                                        justify-center
                                                        shrink-0">

                                                <svg class="w-4 h-4 text-cyan-700"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                                </svg>

                                            </div>

                                            <p class="text-sm font-semibold
                                                      text-gray-800 truncate">

                                                {{ $appointment->office }}
                                                —
                                                {{ $appointment->date->format('M j, Y') }}
                                                {{ $appointment->time_slot }}

                                            </p>

                                        </div>

                                        <span class="shrink-0
                                                     px-2.5 py-1
                                                     rounded-full
                                                     bg-cyan-50
                                                     text-cyan-700
                                                     text-[10px]
                                                     font-extrabold">

                                            {{ \App\Enums\AppointmentStatus::tryFrom($appointment->status)?->label() }}

                                        </span>

                                    </div>

                                @empty

                                    <p class="text-sm text-gray-400
                                              py-3">

                                        No recent appointments.

                                    </p>

                                @endforelse

                            </div>


                            {{-- STUDENT RECORD --}}
                            <a href="{{ route('registrar.students.show', $student) }}"
                               class="mt-4 w-full
                                      inline-flex items-center
                                      justify-center gap-2
                                      px-5 py-3
                                      rounded-2xl
                                      border border-blue-200
                                      bg-blue-50
                                      text-blue-800
                                      text-sm font-extrabold
                                      hover:bg-blue-100
                                      transition">

                                View Full Student Record

                                <svg class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M13 7l5 5m0 0l-5 5m5-5H6"/>

                                </svg>

                            </a>

                        </div>


                        {{-- VERIFIED FOOTER --}}
                        <div class="mt-6 pt-5
                                    border-t border-gray-100
                                    flex flex-col sm:flex-row
                                    items-center justify-between
                                    gap-3">

                            <div class="flex items-center gap-2">

                                <div class="w-7 h-7 rounded-lg
                                            bg-blue-900
                                            flex items-center
                                            justify-center">

                                    <span class="text-yellow-400
                                                 text-xs font-black">
                                        P
                                    </span>

                                </div>

                                <div>

                                    <p class="text-[10px]
                                              font-extrabold
                                              text-blue-950
                                              uppercase
                                              tracking-widest">

                                        ISUFSTPASS VERIFIED

                                    </p>

                                    <p class="text-[9px] text-gray-400">

                                        Secure • Reliable • Official

                                    </p>

                                </div>

                            </div>

                            <p class="text-[10px] text-gray-400">
                                Student Identity Verification
                            </p>

                        </div>

                    </div>

                </div>

            @endif

        </div>

    </div>

</x-app-layout>