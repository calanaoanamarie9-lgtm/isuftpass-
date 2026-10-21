{{--
    The card that moves a booking to another day and hour.

    The registrar is the only one who moves a booking: the student asks, the
    office answers with the hour, and the registrar is the one who changes it
    when the office cannot keep it.

    The booking it works on travels on the element as data attributes, and
    the pages it reads come from props:

        appointment       the booking, for the student's details
        slots-url         where the open slots are read from
        month-url         where the calendar reads the month's availability
        submit-url        where the new schedule is saved
        method            how it is saved
        require-reason    whether a reason has to be given
        pick-slot         whether the office takes a time slot at all
--}}

@props([
    'appointment' => null,
    'slotsUrl' => '',
    'monthUrl' => '',
    'submitUrl' => '',
    'method' => 'PUT',
    'requireReason' => true,
    'showReason' => true,
    'pickSlot' => true,
    'label' => 'Reschedule',
    'variant' => 'secondary',
    'triggerClass' => 'px-4 py-2.5 text-xs',
])

@php
    $reasons = [
        'Office Conflict',
        'Holiday / Non-working Day',
        'Staff Unavailable',
        'User Request',
    ];
@endphp

<div class="relative"
     x-data="rescheduleCard({
         slotsUrl: @js($slotsUrl),
         method: @js($method),
         requireReason: @js($requireReason),
         showReason: @js($showReason),
         pickSlot: @js($pickSlot),
     })"
     @keydown.escape.window="open = false"

     {{-- The calendar announces the day it picked; the card takes it and
          reads the slots that day still has open. --}}
     @date-selected="pickDate($event.detail.date)"

     data-appointment-id="{{ $appointment?->id }}"
     data-office="{{ $appointment?->office }}"
     data-submit-url="{{ $submitUrl }}"
     data-current-date="{{ $appointment?->date->format('F j, Y') }}"
     data-current-slot="{{ $appointment?->confirmed_time ?? $appointment?->time_slot }}"
     data-current-time-slot="{{ $appointment?->time_slot }}">

    {{-- THE TRIGGER --}}
    <button type="button"
            @click="open = true; reset()"
            :aria-expanded="open ? 'true' : 'false'"
            class="inline-flex items-center justify-center gap-2
                   rounded-xl
                   font-bold
                   shadow-sm transition
                   focus:outline-none focus-visible:ring-2
                   {{ $triggerClass }}
                   {{ $variant === 'primary'
                       ? 'bg-blue-900 text-white hover:bg-blue-800 focus-visible:ring-blue-300'
                       : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 focus-visible:ring-gray-300' }}">

        <svg class="w-4 h-4 shrink-0"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 7V3m8 4V3m-9 4h10M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z"/>

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 15l2 2 4-4"/>

        </svg>

        {{ $label }}

    </button>


    {{-- THE MODAL --}}
    <div x-show="open"
         x-cloak
         x-transition.opacity
         class="fixed inset-0 z-[60]
                flex justify-center p-4 overflow-y-auto
                bg-slate-950/50 backdrop-blur-sm"
         @click.self="open = false">

        <div class="relative w-full max-w-xl
                    p-6 my-8
                    bg-white rounded-2xl shadow-xl
                    overflow-y-auto max-h-[90vh]"

             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-3 pb-4 border-b">

                <div>

                    <div class="flex items-center gap-2 flex-wrap">

                        <h3 class="text-lg font-bold text-gray-800">

                            Reschedule Appointment

                        </h3>

                        {{-- The reference the student quotes on the phone. --}}
                        <span class="px-2 py-0.5 rounded-md
                                     bg-blue-100 text-blue-700
                                     text-[10px] font-black uppercase
                                     tracking-wide">
                            {{ $appointment?->reference_code }}
                        </span>

                    </div>

                    <p class="mt-1 text-xs font-semibold text-gray-400">

                        Pick the new day and hour, and say why it moves.

                    </p>

                </div>

                <button type="button"
                        @click="open = false"
                        class="w-9 h-9 shrink-0 rounded-lg
                               text-gray-400 hover:bg-gray-100 hover:text-gray-600
                               transition flex items-center justify-center">

                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>

                    </svg>

                </button>

            </div>


            {{-- Whose booking this is, and what it holds today --}}
            <div class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded-xl">

                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-500">

                    Student

                </span>

                <p class="font-bold text-gray-800">

                    {{ $appointment?->user->name }}

                </p>

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">

                    <div>

                        <p class="text-[10px] uppercase tracking-widest
                                  font-black text-blue-500">
                            Office
                        </p>

                        <p class="text-sm font-bold text-gray-800">
                            {{ $appointment?->office }}
                        </p>

                    </div>

                    <div>

                        <p class="text-[10px] uppercase tracking-widest
                                  font-black text-blue-500">
                            Current date
                        </p>

                        <p class="text-sm font-bold text-gray-800">
                            {{ $appointment?->date->format('F j, Y') }}
                        </p>

                    </div>

                    <div>

                        <p class="text-[10px] uppercase tracking-widest
                                  font-black text-blue-500">
                            Current time slot
                        </p>

                        <p class="text-sm font-bold text-gray-800">
                            {{ $appointment?->time_slot
                                ?? $appointment?->confirmed_time
                                ?? 'Set by the office' }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Why --}}
            <div class="mt-4" x-show="showReason">

                <label for="reschedule-reason"
                       class="block text-[10px] uppercase tracking-widest font-black text-gray-500 mb-1.5">

                    Reason for rescheduling

                </label>

                <select id="reschedule-reason"
                        x-model="reasonPreset"
                        @change="if (reasonPreset !== 'Other') reasonOther = ''"
                        class="w-full rounded-xl
                               border border-gray-200
                               bg-white
                               px-3 py-2.5
                               text-sm font-bold text-gray-800
                               outline-none transition
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-200">

                    <option value="">Choose a reason</option>

                    @foreach ($reasons as $reason)
                        <option value="{{ $reason }}">{{ $reason }}</option>
                    @endforeach

                    <option value="Other">Other</option>

                </select>

                <input type="text"
                       x-show="reasonPreset === 'Other'"
                       x-model="reasonOther"
                       maxlength="500"
                       placeholder="Type the reason"

                       class="w-full mt-2 rounded-xl
                              border border-gray-200
                              bg-white
                              px-3 py-2.5
                              text-sm font-semibold text-gray-800
                              outline-none transition
                              focus:border-blue-500 focus:ring-2 focus:ring-blue-200">

            </div>


            {{-- The new day: the same calendar the booking form uses, so a
                 registrar sees the same green and red the student saw. It is
                 built only while the modal is open - the list holds one card
                 per appointment, and a month of availability per card would
                 be a hundred requests nobody asked for. --}}
            <div class="mt-5">

                <p class="block text-[10px] uppercase tracking-widest font-black text-gray-500 mb-1.5">

                    New date

                </p>

                <template x-if="open">

                    <x-availability-calendar
                        office="{{ $appointment?->office }}"
                        endpoint="{{ $monthUrl }}"
                        :selected="''"
                        :min-month="now()->format('Y-m')"
                        block-full
                        emit />

                </template>

                <p class="mt-2 text-[11px] font-semibold text-gray-500">

                    Green days have open slots; red days are full or closed.

                </p>

                <p x-show="date" class="mt-1 text-sm font-bold text-gray-800">

                    New day: <span x-text="date ? dateLabel(date) : ''"></span>

                </p>

            </div>


            {{-- The new hour: the slots the office still has open that day. --}}
            <div class="mt-5" x-show="pickSlot">

                <p class="block text-[10px] uppercase tracking-widest font-black text-gray-500 mb-1.5">

                    New time slot

                </p>

                <p x-show="loadingSlots"
                   class="text-xs font-semibold text-blue-600">

                    Checking the open slots…

                </p>

                <p x-show="! loadingSlots && ! date"
                   class="text-xs font-semibold text-gray-500">

                    Pick a date first.

                </p>

                <p x-show="! loadingSlots && date && openSlots.length === 0"
                   class="text-xs font-semibold text-gray-500">

                    No open slots on that date. Try another day.

                </p>

                <div x-show="! loadingSlots && openSlots.length > 0"
                     class="grid grid-cols-1 sm:grid-cols-2 gap-2">

                    <template x-for="slot in slots" :key="slot.time">

                        <label class="flex items-center justify-between gap-2
                                      px-3 py-2.5 rounded-xl border
                                      text-sm font-bold transition
                                      cursor-pointer"

                               :class="slot.is_open
                                   ? (time === slot.time
                                       ? 'border-blue-600 bg-blue-50 text-blue-800 ring-1 ring-blue-200'
                                       : 'border-gray-200 bg-white text-gray-700 hover:border-blue-300')
                                   : 'border-gray-100 bg-gray-50 text-gray-400 cursor-not-allowed'">

                            <span class="flex items-center gap-2 min-w-0">

                                <input type="radio"
                                       class="sr-only"
                                       :name="'reschedule-slot-' + (appointmentId || 'any')"
                                       :value="slot.time"
                                       :disabled="! slot.is_open"
                                       x-model="time" />

                                <span class="truncate" x-text="slot.time"></span>

                            </span>

                            <span class="text-[11px] font-semibold shrink-0"
                                  x-text="slot.is_open
                                      ? slot.remaining + ' seat' + (slot.remaining === 1 ? '' : 's')
                                      : 'Full'"></span>

                        </label>

                    </template>

                </div>

            </div>


            {{-- What went wrong --}}
            <div x-show="error"
                 x-transition
                 class="mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3">

                <p class="text-sm font-medium text-red-700" x-text="error"></p>

            </div>


            {{-- Actions --}}
            <div class="mt-6 pt-4 border-t flex items-center justify-end gap-3">

                <button type="button"
                        @click="open = false"
                        :disabled="submitting"
                        class="px-4 py-2.5 rounded-xl
                               bg-gray-100 text-gray-700
                               text-xs font-bold
                               hover:bg-gray-200
                               transition disabled:opacity-50">

                    Cancel

                </button>

                <button type="button"
                        @click="confirm()"
                        :disabled="submitting || !canSave"
                        class="inline-flex items-center gap-2
                               px-4 py-2.5 rounded-xl
                               bg-[#1e40af] text-white
                               text-xs font-bold
                               shadow-sm hover:bg-blue-800
                               transition
                               disabled:opacity-50 disabled:cursor-not-allowed">

                    <svg x-show="submitting"
                         class="animate-spin w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24">

                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>

                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>

                    </svg>

                    <span x-text="submitting ? 'Saving...' : 'Confirm Reschedule'"></span>

                </button>

            </div>

        </div>

    </div>

</div>
