<x-app-layout>

    @php
        $steps = [
            ['value' => 'submitted',        'label' => 'Submitted'],
            ['value' => 'processing',       'label' => 'Paid'],
            ['value' => 'for_signature',    'label' => 'Approved'],
            ['value' => 'ready_for_pickup', 'label' => 'For Release'],
            ['value' => 'completed',        'label' => 'Claimed'],
        ];

        $activeStep = collect($steps)->search(fn ($s) => $s['value'] === $request->status);
        $cancelled = $request->status === 'cancelled';

        $currentLabel = $cancelled
            ? 'Cancelled'
            : ($activeStep !== false
                ? $steps[$activeStep]['label']
                : ucfirst(str_replace('_', ' ', $request->status)));

        // Label of the status this request will be set to when the
        // registrar clicks the primary action button.
        $nextLabel = ($activeStep !== false && isset($steps[$activeStep + 1]))
            ? $steps[$activeStep + 1]['label']
            : null;
    @endphp


    <div class="min-h-screen bg-[#f5f8fc] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="mb-7">

                <div class="flex flex-col lg:flex-row
                            lg:items-center
                            lg:justify-between
                            gap-5">

                    {{-- LEFT --}}
                    <div class="flex items-center gap-4">

                        <div class="w-14 h-14
                                    rounded-2xl
                                    bg-blue-700
                                    text-white
                                    flex items-center
                                    justify-center
                                    shadow-sm">

                            <svg class="w-7 h-7"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                            </svg>

                        </div>

                        <div>

                            <h1 class="text-2xl sm:text-3xl
                                       font-extrabold
                                       text-[#102d5b]">

                                Document Request Details

                            </h1>

                            <p class="mt-1 text-sm text-gray-500">
                                Review, process, and manage this student request.
                            </p>

                        </div>

                    </div>


                    {{-- RIGHT --}}
                    <div class="flex flex-wrap items-center gap-3">

                        <span class="inline-flex items-center
                                     px-4 py-2
                                     rounded-full
                                     bg-white
                                     border border-gray-200
                                     text-xs
                                     font-semibold
                                     text-gray-600
                                     shadow-sm">

                            Request #{{ $request->request_number }}

                        </span>


                        <span class="inline-flex items-center gap-2
                                     px-4 py-2
                                     rounded-full
                                     text-xs
                                     font-bold
                                     border
                                     {{ $cancelled
                                        ? 'bg-red-50 text-red-600 border-red-200'
                                        : ($request->status === 'completed'
                                            ? 'bg-green-50 text-green-700 border-green-200'
                                            : 'bg-blue-50 text-blue-700 border-blue-200') }}">

                            <span class="w-2 h-2 rounded-full
                                         {{ $cancelled
                                            ? 'bg-red-500'
                                            : ($request->status === 'completed'
                                                ? 'bg-green-500'
                                                : 'bg-blue-600') }}">
                            </span>

                            {{ $currentLabel }}

                        </span>


                        <a href="{{ route('registrar.document-requests.index') }}"
                           class="w-10 h-10
                                  rounded-xl
                                  bg-white
                                  border border-gray-200
                                  text-gray-500
                                  hover:text-gray-700
                                  hover:bg-gray-50
                                  flex items-center
                                  justify-center
                                  transition"
                           title="Close">

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>

                            </svg>

                        </a>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                ALERTS
            ========================================================== --}}
            @if (session('status'))

                <div class="mb-6
                            rounded-2xl
                            bg-green-50
                            border border-green-200
                            px-5 py-4
                            text-sm text-green-700">

                    {{ session('status') }}

                </div>

            @endif


            @if (session('error'))

                <div class="mb-6
                            rounded-2xl
                            bg-red-50
                            border border-red-200
                            px-5 py-4
                            text-sm text-red-700">

                    {{ session('error') }}

                </div>

            @endif


            {{-- =========================================================
                STATUS STEPPER
            ========================================================== --}}
            <div class="bg-white
                        rounded-2xl
                        border border-gray-200
                        shadow-sm
                        p-6
                        mb-7">

                <ol class="flex items-start
                           overflow-x-auto
                           pb-2">

                    @foreach ($steps as $i => $step)

                        <li class="flex items-start
                                   {{ $i < count($steps) - 1 ? 'flex-1' : '' }}
                                   min-w-[130px]">

                            {{-- STEP --}}
                            <div class="flex flex-col items-center
                                        text-center
                                        shrink-0">

                                <span class="w-11 h-11
                                             rounded-full
                                             flex items-center
                                             justify-center
                                             text-sm
                                             font-bold
                                             transition

                                             {{ $cancelled
                                                ? 'bg-red-50 text-red-500 ring-1 ring-red-200'

                                                : ($activeStep !== false && $i < $activeStep
                                                    ? 'bg-green-500 text-white'

                                                    : ($activeStep === $i
                                                        ? 'bg-blue-700 text-white ring-4 ring-blue-100'

                                                        : 'bg-gray-100 text-gray-400 ring-1 ring-gray-200')) }}">

                                    @if (!$cancelled && $activeStep !== false && $i < $activeStep)

                                        <svg class="w-5 h-5"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2.5"
                                                  d="M5 13l4 4L19 7"/>

                                        </svg>

                                    @else

                                        {{ $i + 1 }}

                                    @endif

                                </span>


                                <p class="mt-3
                                          text-xs
                                          font-bold
                                          {{ $activeStep === $i && !$cancelled
                                              ? 'text-blue-700'
                                              : ($activeStep !== false && $i < $activeStep
                                                  ? 'text-green-700'
                                                  : 'text-gray-500') }}">

                                    {{ $step['label'] }}

                                </p>

                            </div>


                            {{-- CONNECTOR --}}
                            @if ($i < count($steps) - 1)

                                <div class="flex-1
                                            h-0.5
                                            mt-5
                                            mx-3
                                            min-w-[40px]
                                            {{ !$cancelled && $activeStep !== false && $i < $activeStep
                                                ? 'bg-green-500'
                                                : 'bg-gray-200' }}">
                                </div>

                            @endif

                        </li>

                    @endforeach

                </ol>


                @if ($cancelled)

                    <div class="mt-5
                                rounded-xl
                                bg-red-50
                                border border-red-100
                                px-4 py-3
                                text-center
                                text-sm
                                font-semibold
                                text-red-600">

                        This document request has been rejected or cancelled.

                    </div>

                @endif

            </div>


            {{-- =========================================================
                MAIN GRID
            ========================================================== --}}
            <div class="grid grid-cols-1
                        xl:grid-cols-3
                        gap-6">


                {{-- =====================================================
                    LEFT SIDE
                ====================================================== --}}
                <div class="xl:col-span-2 space-y-6">


                    {{-- =================================================
                        REQUEST SUMMARY
                    ================================================== --}}
                    <div class="bg-white
                                rounded-2xl
                                border border-gray-200
                                shadow-sm
                                overflow-hidden">

                        {{-- HEADER --}}
                        <div class="px-6 py-4
                                    border-b border-gray-100
                                    bg-[#f8fbff]">

                            <div class="flex items-center gap-3">

                                <div class="w-9 h-9
                                            rounded-xl
                                            bg-blue-700
                                            text-white
                                            flex items-center
                                            justify-center">

                                    <svg class="w-5 h-5"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                                    </svg>

                                </div>

                                <h2 class="font-bold text-[#102d5b]">
                                    Request Summary
                                </h2>

                            </div>

                        </div>


                        {{-- CONTENT --}}
                        <div class="p-6">

                            <div class="grid grid-cols-1
                                        sm:grid-cols-2
                                        lg:grid-cols-3
                                        gap-6">


                                {{-- DOCUMENT --}}
                                <div>

                                    <p class="text-[11px]
                                              uppercase
                                              tracking-wider
                                              font-bold
                                              text-gray-400">

                                        Document

                                    </p>

                                    <p class="mt-2
                                              text-sm
                                              font-bold
                                              text-gray-900">

                                        {{ $request->documentsSummary() }}

                                    </p>

                                </div>


                                {{-- REQUEST NUMBER --}}
                                <div>

                                    <p class="text-[11px]
                                              uppercase
                                              tracking-wider
                                              font-bold
                                              text-gray-400">

                                        Request Number

                                    </p>

                                    <p class="mt-2
                                              text-sm
                                              font-semibold
                                              text-gray-800">

                                        {{ $request->request_number }}

                                    </p>

                                </div>


                                {{-- STATUS --}}
                                <div>

                                    <p class="text-[11px]
                                              uppercase
                                              tracking-wider
                                              font-bold
                                              text-gray-400">

                                        Current Status

                                    </p>

                                    <span class="mt-2
                                                 inline-flex items-center
                                                 gap-2
                                                 px-3 py-1.5
                                                 rounded-full
                                                 text-xs
                                                 font-bold

                                                 {{ $cancelled
                                                    ? 'bg-red-50 text-red-600'
                                                    : ($request->status === 'completed'
                                                        ? 'bg-green-50 text-green-700'
                                                        : 'bg-blue-50 text-blue-700') }}">

                                        <span class="w-2 h-2
                                                     rounded-full
                                                     {{ $cancelled
                                                        ? 'bg-red-500'
                                                        : ($request->status === 'completed'
                                                            ? 'bg-green-500'
                                                            : 'bg-blue-600') }}">
                                        </span>

                                        {{ $currentLabel }}

                                    </span>

                                </div>


                                {{-- STUDENT --}}
                                <div>

                                    <p class="text-[11px]
                                              uppercase
                                              tracking-wider
                                              font-bold
                                              text-gray-400">

                                        Student Name

                                    </p>

                                    <p class="mt-2
                                              text-sm
                                              font-semibold
                                              text-gray-800">

                                        {{ $request->student_name }}

                                    </p>

                                </div>


                                {{-- STUDENT ID --}}
                                <div>

                                    <p class="text-[11px]
                                              uppercase
                                              tracking-wider
                                              font-bold
                                              text-gray-400">

                                        Student ID

                                    </p>

                                    <p class="mt-2
                                              text-sm
                                              font-semibold
                                              text-gray-800">

                                        {{ $request->user->studentProfile?->student_id ?? '—' }}

                                    </p>

                                </div>


                                {{-- EMAIL --}}
                                <div>

                                    <p class="text-[11px]
                                              uppercase
                                              tracking-wider
                                              font-bold
                                              text-gray-400">

                                        ISUFST Email

                                    </p>

                                    <p class="mt-2
                                              text-sm
                                              font-semibold
                                              text-gray-800
                                              break-all">

                                        {{ $request->user->email }}

                                    </p>

                                </div>


                                {{-- COURSE --}}
                                <div>

                                    <p class="text-[11px]
                                              uppercase
                                              tracking-wider
                                              font-bold
                                              text-gray-400">

                                        Course / Department

                                    </p>

                                    <p class="mt-2
                                              text-sm
                                              font-semibold
                                              text-gray-800">

                                        {{ $request->user->studentProfile?->course ?? '—' }}

                                        @if ($request->user->studentProfile?->year_level)

                                            <span class="block
                                                         mt-0.5
                                                         text-xs
                                                         font-normal
                                                         text-gray-500">

                                                {{ $request->user->studentProfile->year_level }}

                                            </span>

                                        @endif

                                    </p>

                                </div>


                                {{-- PAYMENT --}}
                                <div>

                                    <p class="text-[11px]
                                              uppercase
                                              tracking-wider
                                              font-bold
                                              text-gray-400">

                                        Payment Status

                                    </p>

                                    @if ($request->isPaid())

                                        <span class="mt-2
                                                     inline-flex items-center
                                                     gap-2
                                                     px-3 py-1.5
                                                     rounded-full
                                                     bg-green-50
                                                     text-green-700
                                                     text-xs
                                                     font-bold">

                                            <span class="w-2 h-2 rounded-full bg-green-500"></span>

                                            Paid

                                        </span>

                                    @else

                                        <span class="mt-2
                                                     inline-flex items-center
                                                     gap-2
                                                     px-3 py-1.5
                                                     rounded-full
                                                     bg-yellow-50
                                                     text-yellow-700
                                                     text-xs
                                                     font-bold">

                                            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>

                                            Pending

                                        </span>

                                    @endif

                                </div>


                                {{-- AMOUNT --}}
                                <div>

                                    <p class="text-[11px]
                                              uppercase
                                              tracking-wider
                                              font-bold
                                              text-gray-400">

                                        Total Fee

                                    </p>

                                    <p class="mt-2
                                              text-xl
                                              font-extrabold
                                              text-[#102d5b]">

                                        ₱{{ number_format($request->totalFee(), 2) }}

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        PAYMENT INFORMATION
                    ================================================== --}}
                    <div class="bg-white
                                rounded-2xl
                                border border-gray-200
                                shadow-sm
                                overflow-hidden">

                        <div class="px-6 py-4
                                    bg-[#f8fbff]
                                    border-b border-gray-100">

                            <div class="flex items-center gap-3">

                                <div class="w-9 h-9
                                            rounded-xl
                                            bg-blue-700
                                            text-white
                                            flex items-center
                                            justify-center">

                                    <svg class="w-5 h-5"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m4-3h-8m0 0l3-3m-3 3l3 3"/>

                                    </svg>

                                </div>

                                <h2 class="font-bold text-[#102d5b]">
                                    Payment Information
                                </h2>

                            </div>

                        </div>


                        <div class="p-6">

                            <div class="grid grid-cols-1
                                        sm:grid-cols-2
                                        lg:grid-cols-4
                                        gap-5">

                                <div>

                                    <p class="text-[11px]
                                              uppercase tracking-wide
                                              font-bold text-gray-400">
                                        Method
                                    </p>

                                    <p class="mt-2 text-sm
                                              font-semibold text-gray-800">

                                        {{ $request->isPaid() ? 'Over the Counter' : '—' }}

                                    </p>

                                </div>


                                <div>

                                    <p class="text-[11px]
                                              uppercase tracking-wide
                                              font-bold text-gray-400">
                                        Transaction No.
                                    </p>

                                    <p class="mt-2 text-sm
                                              font-semibold text-gray-800">

                                        {{ $request->isPaid() ? $request->request_number : '—' }}

                                    </p>

                                </div>


                                <div>

                                    <p class="text-[11px]
                                              uppercase tracking-wide
                                              font-bold text-gray-400">
                                        Official Receipt No.
                                    </p>

                                    <p class="mt-2 text-sm
                                              font-semibold text-gray-800">

                                        {{ $request->or_number ?? '—' }}

                                    </p>

                                </div>


                                <div>

                                    <p class="text-[11px]
                                              uppercase tracking-wide
                                              font-bold text-gray-400">
                                        Paid At
                                    </p>

                                    <p class="mt-2 text-sm
                                              font-semibold text-gray-800">

                                        {{ $request->paid_at?->format('M d, Y h:i A') ?? '—' }}

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        FULL REQUEST DETAILS
                    ================================================== --}}
                    <div class="bg-white
                                rounded-2xl
                                border border-gray-200
                                shadow-sm
                                overflow-hidden">

                        <details>

                            <summary class="px-6 py-5
                                            cursor-pointer
                                            flex items-center
                                            justify-between
                                            text-sm
                                            font-bold
                                            text-[#102d5b]
                                            hover:bg-gray-50
                                            transition">

                                Full Request Details

                            </summary>


                            <div class="px-6 pb-6
                                        border-t border-gray-100">

                                <dl class="pt-6
                                           grid
                                           sm:grid-cols-2
                                           gap-6
                                           text-sm">


                                    <div>

                                        <dt class="text-[11px]
                                                   uppercase
                                                   tracking-wide
                                                   font-bold
                                                   text-gray-400">
                                            Purpose
                                        </dt>

                                        <dd class="mt-2 font-semibold text-gray-800">

                                            {{ \App\Enums\RequestPurposeType::tryFrom($request->purpose_type)?->label() ?? '—' }}

                                            @if ($request->transfer_to)

                                                <span class="block
                                                             mt-1
                                                             text-sm
                                                             font-normal
                                                             text-gray-500">

                                                    Transfer to: {{ $request->transfer_to }}

                                                </span>

                                            @endif

                                        </dd>

                                    </div>


                                    <div>

                                        <dt class="text-[11px]
                                                   uppercase
                                                   tracking-wide
                                                   font-bold
                                                   text-gray-400">
                                            Student Status / Level
                                        </dt>

                                        <dd class="mt-2 font-semibold text-gray-800">

                                            {{ \App\Enums\EducationalStatus::tryFrom($request->educational_status)?->label() ?? '—' }}

                                            &middot;

                                            {{ \App\Enums\EducationalLevel::tryFrom($request->educational_level)?->label() ?? '—' }}

                                        </dd>

                                    </div>


                                    <div>

                                        <dt class="text-[11px]
                                                   uppercase
                                                   tracking-wide
                                                   font-bold
                                                   text-gray-400">
                                            Contact
                                        </dt>

                                        <dd class="mt-2 font-semibold text-gray-800">

                                            {{ $request->student_contact ?? '—' }}

                                        </dd>

                                    </div>


                                    <div>

                                        <dt class="text-[11px]
                                                   uppercase
                                                   tracking-wide
                                                   font-bold
                                                   text-gray-400">
                                            Address
                                        </dt>

                                        <dd class="mt-2 font-semibold text-gray-800">

                                            {{ $request->student_address ?? '—' }}

                                        </dd>

                                    </div>


                                    <div>

                                        <dt class="text-[11px]
                                                   uppercase
                                                   tracking-wide
                                                   font-bold
                                                   text-gray-400">
                                            Mode of Claiming
                                        </dt>

                                        <dd class="mt-2 font-semibold text-gray-800">

                                            {{ \App\Enums\ClaimMode::tryFrom($request->claim_mode)?->label() ?? '—' }}

                                            @if ($request->claim_mode === 'representative' && $request->representative_name)

                                                <span class="block
                                                             mt-1
                                                             text-sm
                                                             font-normal
                                                             text-gray-500">

                                                    Representative:
                                                    {{ $request->representative_name }}

                                                </span>

                                            @endif

                                        </dd>

                                    </div>


                                    @if (! empty($request->attachments))

                                        <div class="sm:col-span-2">

                                            <dt class="text-[11px]
                                                       uppercase
                                                       tracking-wide
                                                       font-bold
                                                       text-gray-400">
                                                Attachments
                                            </dt>

                                            <dd class="mt-3
                                                       flex flex-wrap gap-2">

                                                @foreach ($request->attachments as $attachment)

                                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($attachment) }}"
                                                       target="_blank"
                                                       class="inline-flex
                                                              items-center gap-2
                                                              px-3 py-2
                                                              rounded-xl
                                                              bg-blue-50
                                                              text-xs
                                                              font-semibold
                                                              text-blue-700
                                                              hover:bg-blue-100
                                                              transition">

                                                        <svg class="w-4 h-4"
                                                             fill="none"
                                                             stroke="currentColor"
                                                             viewBox="0 0 24 24">

                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 00-5.656-5.656L5.758 10.758a6 6 0 108.484 8.484L20 13.485"/>

                                                        </svg>

                                                        {{ basename($attachment) }}

                                                    </a>

                                                @endforeach

                                            </dd>

                                        </div>

                                    @endif

                                </dl>

                            </div>

                        </details>

                    </div>

                </div>


                {{-- =====================================================
                    RIGHT SIDE
                ====================================================== --}}
                <div class="space-y-6">


                    {{-- =================================================
                        ACTIONS
                    ================================================== --}}
                    @if ($request->isActive())

                        <div class="bg-white
                                    rounded-2xl
                                    border border-gray-200
                                    shadow-sm
                                    overflow-hidden">

                            <div class="px-5 py-4
                                        bg-[#f8fbff]
                                        border-b border-gray-100">

                                <h2 class="font-bold text-[#102d5b]">
                                    Request Actions
                                </h2>

                                <p class="mt-1
                                          text-xs
                                          text-gray-500">

                                    The student will be notified for every status change.

                                </p>

                            </div>


                            <div class="p-5 space-y-5">


                                {{-- PAYMENT GATE --}}
                                @unless ($request->isPaid())

                                    <div class="rounded-xl
                                                border border-amber-200
                                                bg-amber-50
                                                px-4 py-3.5">

                                        <p class="flex items-center gap-2
                                                  text-xs
                                                  font-bold
                                                  text-amber-800">

                                            <svg class="h-4 w-4 shrink-0"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="2"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                            </svg>

                                            Approval blocked — payment not yet recorded

                                        </p>

                                        <p class="mt-1.5
                                                  pl-6
                                                  text-[11px]
                                                  leading-relaxed
                                                  text-amber-700">

                                            This request cannot be approved until the
                                            cashier records the payment. You may still
                                            reject it if needed.

                                        </p>

                                    </div>

                                @endunless


                                {{-- REJECT --}}
                                <form method="POST"
                                      action="{{ route('registrar.document-requests.cancel', $request) }}"
                                      data-confirm="The student will be notified that their request was rejected."
                                      data-confirm-title="Reject this document request?"
                                      data-confirm-ok="Yes, reject it">

                                    @csrf


                                    <label class="block
                                                  text-xs
                                                  font-bold
                                                  text-gray-500
                                                  mb-2">

                                        Rejection Reason

                                    </label>


                                    <textarea name="reason"
                                              maxlength="500"
                                              rows="3"
                                              placeholder="Enter reason (optional)"
                                              class="w-full
                                                     rounded-xl
                                                     border-gray-200
                                                     bg-gray-50
                                                     text-sm
                                                     focus:bg-white
                                                     focus:border-red-300
                                                     focus:ring-red-200"></textarea>


                                    <button type="submit"
                                            class="mt-3
                                                   w-full
                                                   px-4 py-2.5
                                                   rounded-xl
                                                   bg-red-50
                                                   text-red-600
                                                   border border-red-200
                                                   text-sm
                                                   font-semibold
                                                   hover:bg-red-100
                                                   transition">

                                        Reject Request

                                    </button>

                                </form>


                                <div class="border-t border-gray-100"></div>


                                {{-- APPROVE --}}
                                <form method="POST"
                                      action="{{ route('registrar.document-requests.next', $request) }}"
                                      data-confirm="{{ $request->status === 'ready_for_pickup'
                                        ? 'Verify that the student is present and release the documents.'
                                        : 'Advance this request to the next pipeline stage.' }}"
                                      data-confirm-title="{{ $nextLabel ? 'Mark as ' . $nextLabel . '?' : 'Advance this request?' }}"
                                      data-confirm-ok="{{ $nextLabel ? 'Yes, mark as ' . $nextLabel : 'Yes, advance' }}"
                                      data-confirm-icon="{{ $request->status === 'ready_for_pickup'
                                        ? 'success'
                                        : 'question' }}">

                                    @csrf


                                    @if ($request->status === 'for_signature')

                                        <div class="grid grid-cols-2 gap-3 mb-4">

                                            <div>

                                                <label class="block
                                                              text-[10px]
                                                              font-bold
                                                              uppercase
                                                              tracking-wide
                                                              text-gray-400
                                                              mb-1">

                                                    Release Date

                                                </label>

                                                <input type="date"
                                                       name="release_date"
                                                       value="{{ now()->toDateString() }}"
                                                       class="w-full
                                                              rounded-xl
                                                              border-gray-200
                                                              text-sm
                                                              focus:border-blue-400
                                                              focus:ring-blue-200">

                                            </div>


                                            <div>

                                                <label class="block
                                                              text-[10px]
                                                              font-bold
                                                              uppercase
                                                              tracking-wide
                                                              text-gray-400
                                                              mb-1">

                                                    Release Time

                                                </label>

                                                <input type="time"
                                                       name="release_time"
                                                       value="08:00"
                                                       class="w-full
                                                              rounded-xl
                                                              border-gray-200
                                                              text-sm
                                                              focus:border-blue-400
                                                              focus:ring-blue-200">

                                            </div>

                                        </div>

                                    @endif


                                    <button type="submit"
                                            @disabled(! $request->isPaid())
                                            class="w-full
                                                   px-4 py-3
                                                   rounded-xl
                                                   text-white
                                                   text-sm
                                                   font-bold
                                                   transition
                                                   {{ $request->isPaid()
                                                       ? 'bg-blue-700 hover:bg-blue-800'
                                                       : 'bg-gray-300 cursor-not-allowed' }}">

                                        @if (! $request->isPaid())
                                            Payment required first
                                        @else
                                            Mark as {{ $nextLabel ?? 'Advance' }}
                                        @endif

                                    </button>

                                </form>

                            </div>

                        </div>

                    @endif


                    {{-- =================================================
                        DELETE
                    ================================================== --}}
                    <div class="bg-white
                                rounded-2xl
                                border border-gray-200
                                shadow-sm
                                overflow-hidden">

                        <div class="px-5 py-4
                                    bg-[#f8fbff]
                                    border-b border-gray-100">

                            <h2 class="font-bold text-[#102d5b]">
                                Delete Request
                            </h2>

                            <p class="mt-1
                                      text-xs
                                      text-gray-500">

                                Removes this request from the registrar's list.

                            </p>

                        </div>


                        <div class="p-5">

                            <form method="POST"
                                  action="{{ route('registrar.document-requests.destroy', $request) }}"
                                  data-confirm="This permanently removes the request and its record. This cannot be undone."
                                  data-confirm-title="Delete this document request?"
                                  data-confirm-ok="Yes, delete it"
                                  data-confirm-icon="warning">

                                @csrf
                                @method('DELETE')


                                <button type="submit"
                                        class="w-full
                                               px-4 py-3
                                               rounded-xl
                                               bg-red-600
                                               text-white
                                               text-sm
                                               font-bold
                                               hover:bg-red-700
                                               transition">

                                    Delete Request

                                </button>

                            </form>

                        </div>

                    </div>


                    {{-- =================================================
                        INFORMATION
                    ================================================== --}}
                    <div class="rounded-2xl
                                border border-blue-100
                                bg-blue-50
                                p-5">

                        <div class="flex items-start gap-3">

                            <div class="w-9 h-9
                                        rounded-full
                                        bg-blue-600
                                        text-white
                                        flex items-center
                                        justify-center
                                        shrink-0
                                        font-bold">

                                i

                            </div>


                            <div>

                                <h3 class="text-sm
                                           font-bold
                                           text-blue-900">

                                    Processing Reminder

                                </h3>

                                <p class="mt-1
                                          text-xs
                                          leading-5
                                          text-blue-700">

                                    Confirm payment and request information
                                    before advancing the transaction to the
                                    next stage.

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>