{{--
    The registrar's answer to a booking.

    Two states, one anchor: a pending or for-reschedule appointment shows the
    Approve & schedule trigger, an approved one shows the green chip with its
    window on it. Either way the same popover opens — pick the day, the start
    and the end, then send. Esc or a click anywhere else closes it untouched.
--}}

@props(['appointment'])

@php
    $hours = \App\Support\TimeSlots::hoursFor($appointment->office);

    // A time input wants the bare clock (08:00); a stored time may arrive
    // with seconds attached, so peel them back.
    $trim = fn ($time) => $time ? substr((string) $time, 0, 5) : null;

    // Where the fields should start: the slot the student asked for when one
    // exists, the office's opening hour otherwise — unless this booking has
    // already been answered, in which case show what was last sent.
    $start = $hours['open'];
    $minutes = 60;

    if ($appointment->time_slot) {
        $parse = function (string $label): ?string {
            if (! preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', trim($label), $m)) {
                return null;
            }

            return sprintf('%02d:%02d', ((int) $m[1]) % 12 + (strtoupper($m[3]) === 'PM' ? 12 : 0), (int) $m[2]);
        };

        [$rawStart, $rawEnd] = array_pad(explode(' - ', (string) $appointment->time_slot, 2), 2, '');

        $slotStart = $parse($rawStart);
        $slotEnd = $parse($rawEnd);

        if ($slotStart !== null) {
            $start = $slotStart;
        }

        if ($slotEnd !== null && $slotStart !== null) {
            $minutes = max(15, ((int) substr($slotEnd, 0, 2)) * 60 + (int) substr($slotEnd, 3, 2)
                - ((int) substr($slotStart, 0, 2)) * 60 - (int) substr($slotStart, 3, 2));
        }
    }

    $plusMinutes = function (string $clock, int $minutes): string {
        $total = min(((int) substr($clock, 0, 2)) * 60 + (int) substr($clock, 3, 2) + $minutes, 23 * 60 + 59);

        return sprintf('%02d:%02d', intdiv($total, 60), $total % 60);
    };

    // old() first, so a bounced submit keeps what the registrar typed; a
    // tampered value that is not a clock falls back to a real one.
    $startValue = old('start_time', $trim($appointment->start_time) ?? $start);
    $startValue = is_string($startValue) && preg_match('/^\d{2}:\d{2}$/', $startValue)
        ? $startValue
        : $start;

    $endValue = old('end_time', $trim($appointment->end_time) ?? $plusMinutes($startValue, $minutes));
    $endValue = is_string($endValue) && preg_match('/^\d{2}:\d{2}$/', $endValue)
        ? $endValue
        : $plusMinutes($startValue, $minutes);

    $dateValue = old('scheduled_date', $appointment->date->toDateString());
    $dateValue = is_string($dateValue) ? $dateValue : $appointment->date->toDateString();

    // A submit that bounced back reopens the popover, so the message the
    // staff needs is already on screen — with every value they typed intact.
    $openOnError = $errors->has('scheduled_date')
        || $errors->has('start_time')
        || $errors->has('end_time');

    $approved = $appointment->status === 'confirmed';
    $approvable = in_array($appointment->status, ['pending', 'for_reschedule'], true);

    $officeClock = fn (string $time): string => sprintf(
        '%d:%02d %s',
        ((int) substr($time, 0, 2)) % 12 ?: 12,
        (int) substr($time, 3, 2),
        (int) substr($time, 0, 2) < 12 ? 'AM' : 'PM'
    );
@endphp

@if ($approved || $approvable)

    <div class="relative"
         x-data="{ open: {{ $openOnError ? 'true' : 'false' }} }"
         @keydown.escape.window="open = false"
         @click.outside="open = false">

        {{-- APPROVED — the answer is already given, so the chip carries it
             and Edit only reopens the same window to move it. --}}
        @if ($approved)

            <span class="inline-flex items-center gap-2
                         pl-3 pr-2 py-2 rounded-xl
                         bg-emerald-50
                         border border-emerald-200
                         text-emerald-700
                         text-xs font-bold">

                <svg class="w-4 h-4 shrink-0"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5 13l4 4L19 7"/>

                </svg>

                <span>
                    Approved: {{ $appointment->date->format('M j, Y') }},
                    {{ $appointment->scheduledRange() }}
                </span>

                <button type="button"
                        @click="open = ! open"
                        :aria-expanded="open ? 'true' : 'false'"
                        class="ml-1 px-1 rounded
                               underline underline-offset-2
                               text-emerald-800
                               hover:text-emerald-950
                               focus:outline-none
                               focus-visible:ring-2
                               focus-visible:ring-emerald-300">

                    Edit

                </button>

            </span>

        {{-- NOT YET — one click opens the whole answer. --}}
        @else

            <button type="button"
                    @click="open = ! open"
                    :aria-expanded="open ? 'true' : 'false'"
                    class="inline-flex items-center gap-2
                           px-4 py-2.5 rounded-xl
                           bg-blue-800 text-white
                           text-xs font-bold
                           hover:bg-blue-900
                           shadow-sm hover:shadow-md
                           focus:outline-none
                           focus-visible:ring-2
                           focus-visible:ring-blue-300
                           transition">

                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5 13l4 4L19 7"/>

                </svg>

                Approve &amp; schedule

            </button>

        @endif

        {{-- THE POPOVER — anchored under whichever button opened it, over
             the cards below. The appointment card does not clip it (see the
             action bar note in the show page), so it can open the natural
             downward way and stay fully on screen. --}}
        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"

             class="absolute left-0 top-full mt-2 z-40
                    w-80 max-w-[calc(100vw_-_2rem)]
                    rounded-2xl
                    border border-gray-100
                    bg-white
                    p-5
                    shadow-xl shadow-slate-900/10">

            <form method="POST"
                  action="{{ route('registrar.appointments.confirm', $appointment) }}">

                @csrf

                <h4 class="text-sm font-black text-gray-800">

                    Set date &amp; time

                </h4>

                <p class="mt-0.5 text-[11px] font-semibold text-gray-400">

                    Tell the student when to arrive.

                </p>

                {{-- THE DAY --}}
                <div class="mt-4">

                    <label for="approve-date-{{ $appointment->id }}"
                           class="block text-[10px]
                                  uppercase
                                  tracking-widest
                                  font-black
                                  text-gray-500
                                  mb-1.5">

                        Date

                    </label>

                    <input type="date"
                           id="approve-date-{{ $appointment->id }}"
                           name="scheduled_date"
                           value="{{ $dateValue }}"
                           min="{{ now()->toDateString() }}"
                           required

                           class="w-full
                                  rounded-xl
                                  border border-gray-200
                                  bg-white
                                  px-3 py-2.5
                                  text-sm
                                  font-bold
                                  text-gray-800
                                  focus:border-blue-500
                                  focus:ring-2
                                  focus:ring-blue-200
                                  outline-none
                                  transition
                                  @error('scheduled_date') border-red-400 @enderror">

                    @error('scheduled_date')
                        <p class="mt-1.5 text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- THE WINDOW — start and end sit side by side, both editable. --}}
                <div class="mt-3">

                    <div class="flex items-end gap-2">

                        <div class="flex-1 min-w-0">

                            <label for="approve-start-{{ $appointment->id }}"
                                   class="block text-[10px]
                                          uppercase
                                          tracking-widest
                                          font-black
                                          text-gray-500
                                          mb-1.5">

                                Start time

                            </label>

                            <input type="time"
                                   id="approve-start-{{ $appointment->id }}"
                                   name="start_time"
                                   value="{{ $startValue }}"
                                   min="{{ $hours['open'] }}"
                                   max="{{ $hours['close'] }}"
                                   required

                                   class="w-full
                                          min-w-0
                                          rounded-xl
                                          border border-gray-200
                                          bg-white
                                          px-2 py-2.5
                                          text-xs
                                          font-bold
                                          text-gray-800
                                          focus:border-blue-500
                                          focus:ring-2
                                          focus:ring-blue-200
                                          outline-none
                                          transition
                                          @error('start_time') border-red-400 @enderror">

                        </div>

                        <span class="pb-2.5 text-gray-400 font-bold">–</span>

                        <div class="flex-1 min-w-0">

                            <label for="approve-end-{{ $appointment->id }}"
                                   class="block text-[10px]
                                          uppercase
                                          tracking-widest
                                          font-black
                                          text-gray-500
                                          mb-1.5">

                                End time

                            </label>

                            <input type="time"
                                   id="approve-end-{{ $appointment->id }}"
                                   name="end_time"
                                   value="{{ $endValue }}"
                                   min="{{ $hours['open'] }}"
                                   max="{{ $hours['close'] }}"
                                   required

                                   class="w-full
                                          min-w-0
                                          rounded-xl
                                          border border-gray-200
                                          bg-white
                                          px-2 py-2.5
                                          text-xs
                                          font-bold
                                          text-gray-800
                                          focus:border-blue-500
                                          focus:ring-2
                                          focus:ring-blue-200
                                          outline-none
                                          transition
                                          @error('end_time') border-red-400 @enderror">

                        </div>

                    </div>

                    @error('start_time')
                        <p class="mt-1.5 text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    @error('end_time')
                        <p class="mt-1.5 text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-1.5 text-[11px]
                              font-semibold
                              text-gray-400">

                        Office hours
                        {{ $officeClock($hours['open']) }}
                        –
                        {{ $officeClock($hours['close']) }}

                    </p>

                </div>

                {{-- SEND OR WALK AWAY --}}
                <div class="mt-4 flex items-center justify-end gap-2">

                    <button type="button"
                            @click="open = false"
                            class="px-3.5 py-2.5 rounded-xl
                                   border border-gray-200
                                   text-gray-500
                                   text-xs font-bold
                                   hover:bg-gray-50
                                   focus:outline-none
                                   focus-visible:ring-2
                                   focus-visible:ring-gray-300
                                   transition">

                        Cancel

                    </button>

                    <button type="submit"
                            class="inline-flex items-center gap-2
                                   px-4 py-2.5 rounded-xl
                                   bg-blue-800 text-white
                                   text-xs font-bold
                                   hover:bg-blue-900
                                   shadow-sm hover:shadow-md
                                   focus:outline-none
                                   focus-visible:ring-2
                                   focus-visible:ring-blue-300
                                   transition">

                        <svg class="w-4 h-4"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M5 13l4 4L19 7"/>

                        </svg>

                        Set date &amp; time

                    </button>

                </div>

            </form>

        </div>

    </div>

@endif
