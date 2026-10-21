@props(['appointment'])

{{--
    The four steps a booking walks - Requested, Confirmed, Checked in,
    Completed - drawn as one line across the card. The steps behind the
    student are filled blue, the step the appointment stands on is ringed,
    the steps ahead are grey, and the line joins them in the order they are
    walked. An appointment that left the path (cancelled, or a no show)
    stops in place with a cross instead, and the line under the track says
    what happened.

    What a status means, and where it puts the student, is decided in
    App\Support\AppointmentProgress. This file only draws it.
--}}

@php
    $steps = \App\Support\AppointmentProgress::steps($appointment);
    $notice = \App\Support\AppointmentProgress::notice($appointment);
    $stoppedTone = $notice['tone'] ?? null;

    $circle = [
        'done' => 'bg-blue-600 border-blue-600 text-white',
        'current' => 'bg-blue-50 border-blue-600 text-blue-700 ring-4 ring-blue-100',
        'ahead' => 'bg-white border-gray-200 text-gray-400',
        'stopped' => $stoppedTone === 'no_show'
            ? 'bg-gray-50 border-gray-300 text-gray-500'
            : 'bg-red-50 border-red-300 text-red-500',
    ];

    $label = [
        'done' => 'text-blue-700 font-bold',
        'current' => 'text-blue-700 font-black',
        'ahead' => 'text-gray-400 font-semibold',
        'stopped' => $stoppedTone === 'no_show'
            ? 'text-gray-600 font-bold'
            : 'text-red-500 font-bold',
    ];

    // The join between two steps is blue only as far as the student has
    // actually got: a line out of a step they have passed, and grey once
    // it leads into one that is still ahead or where the path stopped.
    $track = [
        'done' => 'bg-blue-600',
        'current' => 'bg-gray-200',
        'ahead' => 'bg-gray-200',
        'stopped' => 'bg-gray-200',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'mt-5']) }}>

    <p class="text-[10px] uppercase tracking-widest
              text-gray-400 font-bold mb-3">
        Appointment progress
    </p>

    <ol class="flex items-start gap-1">

        @foreach ($steps as $step)

            <li class="flex-1 min-w-0 flex items-start"

                @if ($step['state'] === 'current') aria-current="step" @endif>

                <div class="flex-1 min-w-0 flex flex-col items-center gap-1.5">

                    {{-- The step itself --}}
                    <span class="w-8 h-8 shrink-0 rounded-full border-2
                                 flex items-center justify-center
                                 {{ $circle[$step['state']] }}">

                        @if ($step['state'] === 'done')

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="3"
                                      d="M20 6L9 17l-5-5"/>
                            </svg>

                        @elseif ($step['state'] === 'stopped')

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="3"
                                      d="M6 6l12 12M18 6L6 18"/>
                            </svg>

                        @else

                            <span class="text-xs font-black">
                                {{ $loop->iteration }}
                            </span>

                        @endif

                    </span>

                    <span class="text-[10px] leading-tight text-center
                                 {{ $label[$step['state']] }}">
                        {{ $step['label'] }}
                    </span>

                    @if ($step['caption'])

                        <span class="text-[10px] leading-tight text-center
                                     text-gray-400">
                            {{ $step['caption'] }}
                        </span>

                    @endif

                </div>

                {{-- The line on to the next step --}}
                @unless ($loop->last)

                    <span class="w-2 shrink-0 flex items-center mt-[15px]"
                          aria-hidden="true">
                        <span class="h-0.5 w-full rounded-full
                                     {{ $track[$step['state']] }}"></span>
                    </span>

                @endunless

            </li>

        @endforeach

    </ol>

    @if ($notice)

        <div class="mt-3 rounded-xl border px-4 py-3 text-sm font-semibold

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
