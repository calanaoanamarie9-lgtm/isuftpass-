@props(['appointment'])

{{--
    The four steps a booking walks - Requested, Confirmed, Checked in,
    Completed - drawn as a vertical timeline, the way the request tracking
    on My Requests draws its own: the steps behind the student are solid
    blue ticks, the step the appointment stands on is the same tick with a
    Current badge beside its name, the steps ahead are grey circles still
    carrying their number, and a line joins one to the next as far as the
    student has actually got.

    An appointment that left the path (cancelled, or a no show) stops in
    place with a cross instead, and the panel under the timeline says what
    happened - the same place My Requests leaves its history.

    What a status means, and where it puts the student, is decided in
    App\Support\AppointmentProgress. This file only draws it.
--}}

@php
    $steps = \App\Support\AppointmentProgress::steps($appointment);
    $notice = \App\Support\AppointmentProgress::notice($appointment);
    $stoppedTone = $notice['tone'] ?? null;

    $reached = fn ($step) => in_array($step['state'], ['done', 'current'], true);

    $circle = [
        'done' => 'bg-[#174b91] text-white',
        'current' => 'bg-[#174b91] text-white',
        'ahead' => 'bg-white border-2 border-gray-300 text-gray-400',
        'stopped' => $stoppedTone === 'no_show'
            ? 'bg-white border-2 border-gray-300 text-gray-500'
            : 'bg-red-50 border-2 border-red-300 text-red-500',
    ];

    $title = [
        'done' => 'text-gray-900',
        'current' => 'text-gray-900',
        'ahead' => 'text-gray-500',
        'stopped' => $stoppedTone === 'no_show'
            ? 'text-gray-600'
            : 'text-red-600',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'pt-5 sm:pt-6']) }}>

    <div class="mb-7">

        <p class="text-[10px] font-black tracking-[0.18em]
                  text-[#174b91] uppercase">

            Tracking

        </p>

        <h2 class="text-xl font-black text-gray-900 mt-1">
            Appointment progress
        </h2>

        <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">

            <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>

            <span>
                Tracking live — updates automatically.
            </span>

        </div>

    </div>


    {{-- ================================================================
         TIMELINE
    ================================================================= --}}
    <div class="relative">

        @foreach ($steps as $index => $step)

            <div class="relative flex gap-5 pb-9 last:pb-0"

                 @if ($step['state'] === 'current') aria-current="step" @endif>

                {{-- =================================================
                     CONNECTING LINE
                ================================================== --}}
                @unless ($loop->last)

                    <div class="absolute left-[19px] top-10 bottom-0 w-[2px]
                                {{ $step['state'] === 'done'
                                    ? 'bg-[#2459a5]'
                                    : 'bg-gray-200' }}"
                         aria-hidden="true"></div>

                @endunless


                {{-- =================================================
                     STEP CIRCLE
                ================================================== --}}
                <div class="relative z-10 w-10 h-10 rounded-full
                            flex items-center justify-center flex-shrink-0
                            {{ $circle[$step['state']] }}">

                    @if ($reached($step))

                        <svg class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2.5"
                                  d="M5 13l4 4L19 7"/>

                        </svg>

                    @elseif ($step['state'] === 'stopped')

                        <svg class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2.5"
                                  d="M6 6l12 12M18 6L6 18"/>

                        </svg>

                    @else

                        {{ $index + 1 }}

                    @endif

                </div>


                {{-- =================================================
                     STEP CONTENT
                ================================================== --}}
                <div class="pt-1 min-w-0">

                    <div class="flex flex-wrap items-center gap-2">

                        <h3 class="text-base font-black {{ $title[$step['state']] }}">
                            {{ $step['label'] }}
                        </h3>

                        @if ($step['state'] === 'current')

                            <span class="px-2 py-0.5 rounded-md
                                         bg-blue-100 text-blue-700
                                         text-[9px] font-black uppercase
                                         tracking-wide">

                                Current

                            </span>

                        @endif

                    </div>

                    <p class="mt-1 text-xs text-gray-500 leading-5">

                        {{ $step['state'] === 'stopped'
                            ? 'This step was never reached.'
                            : $step['subtext'] }}

                    </p>

                    @if ($step['caption'])

                        <p class="mt-1 text-[10px] font-semibold
                                  text-gray-400">

                            {{ $step['caption'] }}

                        </p>

                    @endif

                </div>

            </div>

        @endforeach

    </div>


    @if ($notice)

        <div class="mt-1 rounded-xl border px-4 py-3 text-sm font-semibold

            @if ($stoppedTone === 'no_show')
                border-gray-200 bg-gray-50 text-gray-600
            @elseif ($stoppedTone === 'cancelled')
                border-red-200 bg-red-50 text-red-700
            @else
                border-orange-200 bg-orange-50 text-orange-700
            @endif
        ">
            {{ $notice['text'] }}
        </div>

    @endif

</div>
