<x-app-layout>

    <div
        x-data="availabilityManager()"
        class="min-h-screen bg-[#f4f7fb] py-8"
    >

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =====================================================
                HEADER
            ====================================================== --}}
            <div class="mb-8">

                <div class="flex flex-col lg:flex-row
                            lg:items-center
                            lg:justify-between
                            gap-5">

                    <div>

                        <div class="flex items-center gap-3 mb-3">

                            <div class="w-11 h-11
                                        rounded-2xl
                                        bg-blue-950
                                        flex items-center
                                        justify-center
                                        shadow-sm">

                                <svg class="w-5 h-5 text-yellow-400"
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

                                <p class="text-[10px]
                                          font-extrabold
                                          uppercase
                                          tracking-[0.18em]
                                          text-blue-600">

                                    Registrar's Office

                                </p>

                                <h1 class="text-2xl sm:text-3xl
                                           font-extrabold
                                           tracking-tight
                                           text-blue-950">

                                    Availability Schedule

                                </h1>

                            </div>

                        </div>


                        <p class="text-sm
                                  sm:text-base
                                  text-gray-500
                                  max-w-2xl">

                            Manage which dates are open for student
                            appointments. Students can only book days marked
                            as available, and the day's list is numbered
                            first come, first served.

                        </p>

                    </div>


                    {{-- STATUS --}}
                    <div class="inline-flex
                                items-center
                                gap-2
                                self-start
                                lg:self-center
                                px-4 py-2.5
                                rounded-2xl
                                bg-white
                                border border-gray-100
                                shadow-sm">

                        <span class="w-2.5 h-2.5
                                     rounded-full
                                     bg-green-500">
                        </span>

                        <span class="text-xs
                                     font-bold
                                     text-gray-600">

                            Scheduling Active

                        </span>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                OFFICE HOURS (drives the slot list below)
            =====================================================
            --}}
            <x-office-hours />

            {{-- =====================================================
                MAIN GRID
            ====================================================== --}}
            <div class="grid grid-cols-1
                        xl:grid-cols-[420px_1fr]
                        gap-6">


                {{-- =================================================
                    LEFT: SET AVAILABILITY
                ================================================== --}}
                <div
                    x-ref="form"
                    class="bg-white
                           rounded-3xl
                           border border-gray-100
                           shadow-sm
                           overflow-hidden"
                >

                    {{-- CARD HEADER --}}
                    <div class="px-6 py-5
                                bg-gradient-to-r
                                from-blue-950
                                to-blue-900">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10
                                        rounded-xl
                                        bg-white/10
                                        border border-white/10
                                        flex items-center
                                        justify-center">

                                <svg class="w-5 h-5 text-yellow-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>

                                </svg>

                            </div>

                            <div>

                                <p class="text-[10px]
                                          uppercase
                                          tracking-widest
                                          font-bold
                                          text-blue-300">

                                    Schedule Settings

                                </p>

                                <h2 class="text-lg
                                           font-extrabold
                                           text-white">

                                    Set Availability

                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="p-6">


                        {{-- DATE --}}
                        <div>

                            <label class="block
                                          text-xs
                                          font-extrabold
                                          uppercase
                                          tracking-widest
                                          text-gray-500
                                          mb-2">

                                Select Date

                            </label>

                            <input
                                type="date"
                                x-model="date"
                                min="{{ now()->toDateString() }}"
                                @change="loadSelectedDate(); syncMonthTo(date)"
                                class="w-full
                                       h-12
                                       rounded-xl
                                       border-gray-200
                                       bg-gray-50
                                       text-sm
                                       font-semibold
                                       text-blue-950
                                       shadow-sm
                                       focus:border-blue-600
                                       focus:ring-2
                                       focus:ring-blue-100"
                            >

                        </div>


                        {{-- AVAILABILITY TYPE --}}
                        <div class="mt-7">

                            <p class="text-xs
                                      font-extrabold
                                      uppercase
                                      tracking-widest
                                      text-gray-500
                                      mb-3">

                                Availability Type

                            </p>


                            <div class="space-y-2">


                                {{-- OPEN --}}
                                <label
                                    class="flex cursor-pointer
                                           items-center
                                           gap-3
                                           rounded-xl
                                           border
                                           px-4 py-3
                                           transition"
                                    :class="availabilityType === 'open'
                                        ? 'border-green-300 bg-green-50'
                                        : 'border-gray-100 bg-gray-50 hover:border-blue-200'"
                                >

                                    <input
                                        type="radio"
                                        name="availability_type"
                                        value="open"
                                        x-model="availabilityType"
                                        class="h-5 w-5
                                               border-gray-300
                                               text-green-600
                                               focus:ring-green-500"
                                    >

                                    <div class="flex-1">

                                        <p class="text-sm
                                                  font-bold
                                                  text-gray-800">

                                            Open Entire Day

                                        </p>

                                        <p class="text-[11px]
                                                  text-gray-400">

                                            The whole day will be open for
                                            bookings.

                                        </p>

                                    </div>

                                    <span
                                        x-show="availabilityType === 'open'"
                                        class="text-[9px]
                                               font-extrabold
                                               uppercase
                                               text-green-600">

                                        Selected

                                    </span>

                                </label>


                                {{-- CLOSED --}}
                                <label
                                    class="flex cursor-pointer
                                           items-center
                                           gap-3
                                           rounded-xl
                                           border
                                           px-4 py-3
                                           transition"
                                    :class="availabilityType === 'closed'
                                        ? 'border-red-300 bg-red-50'
                                        : 'border-gray-100 bg-gray-50 hover:border-blue-200'"
                                >

                                    <input
                                        type="radio"
                                        name="availability_type"
                                        value="closed"
                                        x-model="availabilityType"
                                        class="h-5 w-5
                                               border-gray-300
                                               text-red-600
                                               focus:ring-red-500"
                                    >

                                    <div class="flex-1">

                                        <p class="text-sm
                                                  font-bold
                                                  text-gray-800">

                                            Close Entire Day

                                        </p>

                                        <p class="text-[11px]
                                                  text-gray-400">

                                            No student appointments can be booked.

                                        </p>

                                    </div>

                                    <span
                                        x-show="availabilityType === 'closed'"
                                        class="text-[9px]
                                               font-extrabold
                                               uppercase
                                               text-red-600">

                                        Selected

                                    </span>

                                </label>


                            </div>

                        </div>


                        {{-- =================================================
                            APPOINTMENTS
                            Always shown: the registrar reads how full a
                            day is whatever the day is set to.
                        ================================================== --}}
                        <div
                            x-transition
                            class="mt-6
                                   rounded-2xl
                                   border border-blue-100
                                   bg-blue-50/50
                                   p-4"
                        >

                            <div class="flex items-center
                                        justify-between
                                        mb-4">

                                <div>

                                    <p class="text-sm
                                              font-extrabold
                                              text-blue-950">

                                        Appointments

                                    </p>

                                    <p class="text-[10px]
                                              text-gray-400
                                              mt-0.5">

                                        Numbered as they are booked. The first
                                        <span x-text="slotsPerDay"></span> fill
                                        the day; anything past that still books
                                        and is marked bukas na.

                                    </p>

                                </div>

                                <span class="px-2.5 py-1
                                             rounded-lg
                                             bg-white
                                             border border-blue-100
                                             text-[10px]
                                             font-bold
                                             text-blue-600">

                                    <span x-text="rosterCount + ' Booked'"></span>

                                </span>

                            </div>


                            {{-- APPOINTMENT ROSTER --}}
                            {{-- The list numbers the day's bookings instead of
                                 the office's clock hours: the registrar is
                                 reading how full a day is, not choosing between
                                 times. The day's slots fill in order and
                                 anything past them still books — there is no
                                 limit on how many students may ask — so the
                                 tail is simply marked as coming after the
                                 day's first fill. --}}
                            <div class="max-h-64
                                        overflow-y-auto
                                        space-y-2
                                        pr-1">

                                <template x-for="(row, i) in roster" :key="i">

                                    <div class="flex items-center
                                                gap-3
                                                rounded-xl
                                                border
                                                border-gray-100
                                                bg-white
                                                px-3 py-3"
                                         :class="row.cancelled
                                             ? 'bg-red-50/40 border-red-100'
                                             : ''">

                                        {{-- WHERE THE BOOKING SITS IN THE
                                             DAY. A cancelled one takes no
                                             number: it is not going to
                                             occupy one. --}}
                                        <span class="inline-flex
                                                     items-center
                                                     justify-center
                                                     w-6 h-6
                                                     shrink-0
                                                     rounded-full
                                                     text-[11px]
                                                     font-extrabold
                                                     tabular-nums"
                                              :class="row.cancelled
                                                  ? 'bg-red-100 text-red-500'
                                                  : 'bg-gray-100 text-gray-600'"
                                              x-text="row.number === null ? '✕' : row.number"></span>

                                        <span class="min-w-0
                                                     flex-1
                                                     truncate
                                                     text-sm
                                                     font-semibold"
                                              :class="row.cancelled
                                                  ? 'text-gray-400 line-through'
                                                  : 'text-gray-800'"
                                              x-text="row.name"></span>

                                        {{-- When the office told this
                                             student to arrive, once it has
                                             answered their request. --}}
                                        <span class="shrink-0
                                                     text-[11px]
                                                     font-extrabold
                                                     tabular-nums"
                                              x-show="row.time && ! row.cancelled"
                                              :class="row.status === 'confirmed'
                                                  ? 'text-blue-700'
                                                  : 'text-gray-400'"
                                              x-text="row.time"></span>

                                        {{-- WHAT THIS BOOKING IS: approved
                                             by this office, still waiting
                                             on it, or cancelled. --}}
                                        <span class="shrink-0
                                                     px-1.5 py-0.5
                                                     rounded-full
                                                     text-[9px]
                                                     font-extrabold
                                                     uppercase"
                                              :class="{
                                                  'bg-green-50 text-green-700 ring-1 ring-green-200':
                                                      row.status === 'confirmed',

                                                  'bg-amber-50 text-amber-700 ring-1 ring-amber-200':
                                                      row.status === 'pending',

                                                  'bg-red-50 text-red-600 ring-1 ring-red-200':
                                                      row.cancelled,

                                                  'bg-blue-50 text-blue-700 ring-1 ring-blue-200':
                                                      row.status !== 'confirmed'
                                                      && row.status !== 'pending'
                                                      && ! row.cancelled
                                              }"
                                              x-text="row.statusLabel"></span>

                                        <span class="ml-auto
                                                     shrink-0
                                                     text-[9px]
                                                     font-extrabold
                                                     uppercase"
                                              x-show="! row.cancelled"
                                              :class="row.number <= slotsPerDay
                                                  ? 'text-blue-600'
                                                  : 'text-amber-600'"
                                              x-text="row.number <= slotsPerDay
                                                  ? 'Slot'
                                                  : 'Bukas na'"></span>

                                    </div>

                                </template>

                                <div x-show="roster.length === 0"
                                     x-transition
                                     class="rounded-xl
                                            border border-dashed
                                            border-gray-200
                                            bg-gray-50
                                            px-3 py-6
                                            text-center">

                                    <p class="text-xs
                                              font-semibold
                                              text-gray-500">
                                        No appointments on this date yet.
                                    </p>

                                    <p class="text-[11px]
                                              text-gray-400
                                              mt-1">
                                        Booking is unlimited — the first <span
                                        x-text="slotsPerDay"></span> fill the
                                        day, and the rest follow.
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- LIVE PREVIEW --}}
                        <div class="mt-6
                                    rounded-2xl
                                    border border-gray-100
                                    bg-gray-50
                                    p-4">

                            <div class="flex items-center
                                        justify-between">

                                <div>

                                    <p class="text-[10px]
                                              uppercase
                                              tracking-widest
                                              font-bold
                                              text-gray-400">

                                        Live Preview

                                    </p>

                                    <p class="mt-1
                                              text-sm
                                              font-bold
                                              text-blue-950">

                                        <span
                                            x-text="date || 'Select a date'"
                                        ></span>

                                    </p>

                                </div>

                                <div class="text-right">

                                    <p class="text-2xl
                                              font-extrabold
                                              text-blue-700"
                                       x-text="openPreview()">
                                    </p>

                                    <p class="text-[9px]
                                              uppercase
                                              tracking-wide
                                              font-bold
                                              text-gray-400">

                                        <span
                                            x-text="openPreview() === 1 ? 'Open Slot' : 'Open Slots'"
                                        ></span>

                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- SAVE --}}
                        <button
                            type="button"
                            @click="saveAvailability()"
                            class="mt-4
                                   w-full
                                   h-12
                                   inline-flex
                                   items-center
                                   justify-center
                                   gap-2
                                   rounded-xl
                                   bg-blue-900
                                   px-6
                                   text-sm
                                   font-extrabold
                                   text-white
                                   shadow-lg
                                   shadow-blue-900/20
                                   transition
                                   hover:bg-blue-950
                                   focus:outline-none
                                   focus:ring-4
                                   focus:ring-blue-100"
                        >

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M5 13l4 4L19 7"/>

                            </svg>

                            Save Availability

                        </button>

                    </div>

                </div>


                {{-- =================================================
                    RIGHT: SCHEDULE
                ================================================== --}}
                <div class="bg-white
                            rounded-3xl
                            border border-gray-100
                            shadow-sm
                            overflow-hidden">

                    {{-- HEADER --}}
                    <div class="px-6 sm:px-7 py-5
                                border-b border-gray-100
                                bg-gradient-to-r
                                from-white
                                to-blue-50/50">

                        <div class="flex flex-col sm:flex-row
                                    sm:items-center
                                    sm:justify-between
                                    gap-3">

                            <div class="flex items-center gap-3">

                                <div class="w-10 h-10
                                            rounded-xl
                                            bg-blue-50
                                            flex items-center
                                            justify-center">

                                    <svg class="w-5 h-5 text-blue-700"
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

                                    <h2 class="text-lg
                                               font-extrabold
                                               text-blue-950">

                                        Availability Schedule

                                    </h2>

                                </div>

                            </div>


                            <div class="flex items-center gap-2">

                                <span class="w-2 h-2
                                             rounded-full
                                             bg-green-500">
                                </span>

                                <span class="text-[10px]
                                             font-bold
                                             text-gray-400">

                                    Click a date to edit

                                </span>

                            </div>

                        </div>

                    </div>


                    {{-- CONTENT --}}
                    <div class="p-6 sm:p-7">


                        {{-- MONTH NAVIGATION --}}
                        <div class="flex items-center
                                    justify-between
                                    mb-5">

                            <button type="button"
                                    @click="shiftMonth(-1)"
                                    aria-label="Previous month"
                                    class="inline-flex items-center
                                           px-3 py-2
                                           rounded-xl
                                           text-sm font-bold
                                           text-gray-500
                                           hover:bg-gray-100
                                           transition">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M15 19l-7-7 7-7"/>

                                </svg>

                            </button>

                            <p class="text-sm
                                      font-extrabold
                                      text-blue-950"
                               x-text="monthLabel"></p>

                            <button type="button"
                                    @click="shiftMonth(1)"
                                    aria-label="Next month"
                                    class="inline-flex items-center
                                           px-3 py-2
                                           rounded-xl
                                           text-sm font-bold
                                           text-gray-500
                                           hover:bg-gray-100
                                           transition">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 5l7 7-7 7"/>

                                </svg>

                            </button>

                        </div>


                        {{-- WEEKDAYS --}}
                        <div class="grid grid-cols-7 gap-2 mb-2">

                            <template x-for="weekday in weekdays" :key="weekday">

                                <div class="text-center
                                            text-[10px]
                                            font-bold
                                            uppercase
                                            tracking-wide
                                            text-gray-400"
                                     x-text="weekday"></div>

                            </template>

                        </div>


                        {{-- ONE CELL PER DAY
                             The month's own shape: what was set on a date
                             sits inside that date's cell — one badge naming
                             it — instead of being read down a list. A day's
                             bookings are numbered first come, first served
                             on the roster below, so a cell carries no clock
                             times of its own. Padding days are inert; days
                             that have passed can be read but not written. --}}
                        <div class="grid grid-cols-7 gap-2">

                            <template x-for="(cell, index) in calendarCells" :key="index">

                                <div @click="editCell(cell)"
                                     :title="cell.entry ? cell.entry.date : (cell.iso || '')"
                                     class="min-h-[124px]
                                            rounded-xl
                                            border
                                            p-2
                                            transition"
                                     :class="cellClass(cell)">

                                    <template x-if="!cell.blank">

                                        <div class="flex flex-col
                                                    gap-1.5
                                                    h-full">

                                            {{-- DAY --}}
                                            <span class="inline-flex
                                                         items-center
                                                         justify-center
                                                         w-6 h-6
                                                         rounded-full
                                                         text-xs
                                                         font-extrabold"
                                                  :class="cell.today
                                                      ? 'bg-blue-800 text-white'
                                                      : (
                                                          cell.past
                                                              ? 'text-gray-400'
                                                              : 'text-gray-700'
                                                      )"
                                                  x-text="cell.day"></span>

                                            {{-- NOTHING SET ON THIS DATE --}}
                                            <span class="text-[10px]
                                                          font-bold
                                                          uppercase
                                                          tracking-wide
                                                          text-gray-400"
                                                  x-show="!cell.entry">
                                                Not set
                                            </span>

                                            {{-- WHAT WAS SET ON THIS DATE --}}
                                            <template x-if="cell.entry">

                                                <div class="flex flex-col
                                                            gap-1.5">

                                                    <span class="inline-flex
                                                                 w-full
                                                                 text-center
                                                                 px-1 py-0.5
                                                                 rounded-md
                                                                 text-[9px]
                                                                 font-extrabold
                                                                 uppercase
                                                                 leading-tight
                                                                 tracking-tight"
                                                          :class="{
                                                              'bg-green-50 text-green-700 ring-1 ring-green-200':
                                                                  cell.entry.type === 'open',

                                                              'bg-red-50 text-red-600 ring-1 ring-red-200':
                                                                  cell.entry.type === 'closed',

                                                              'bg-blue-50 text-blue-700 ring-1 ring-blue-200':
                                                                  cell.entry.type === 'slots'
                                                          }"
                                                          x-text="cell.entry.typeLabel"></span>

                                                </div>

                                            </template>

                                        </div>

                                    </template>

                                </div>

                            </template>

                        </div>


                        {{-- LEGEND --}}
                        <div class="mt-5
                                    flex
                                    flex-wrap
                                    items-center
                                    gap-x-4
                                    gap-y-2
                                    text-[11px]
                                    font-semibold
                                    text-gray-500">

                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-green-600 inline-block"></span>
                                Open Entire Day
                            </span>

                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-blue-600 inline-block"></span>
                                Partly Open
                            </span>

                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-red-500 inline-block"></span>
                                Closed
                            </span>

                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-gray-300 inline-block"></span>
                                Not set
                            </span>

                        </div>


                        <p x-show="schedule.length === 0"
                           class="mt-4
                                  text-xs
                                  leading-5
                                  text-gray-400">
                            No dates have been set for this month yet — every day
                            runs on its regular hours until you change one.
                        </p>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                FOOTER
            ====================================================== --}}
            <div class="mt-7
                        flex flex-col
                        sm:flex-row
                        items-center
                        justify-between
                        gap-3
                        px-2">

                <div class="flex items-center gap-2">

                    <div class="w-8 h-8
                                rounded-xl
                                bg-blue-950
                                flex items-center
                                justify-center">

                        <span class="text-xs
                                     font-black
                                     text-yellow-400">

                            P

                        </span>

                    </div>

                    <div>

                        <p class="text-[9px]
                                  font-extrabold
                                  uppercase
                                  tracking-widest
                                  text-blue-950">

                            ISUFSTPASS

                        </p>

                        <p class="text-[9px]
                                  text-gray-400">

                            Secure • Reliable • Official

                        </p>

                    </div>

                </div>


                <p class="text-[10px]
                          text-gray-400">

                    Registrar Appointment Availability

                </p>

            </div>

        </div>

    </div>


    {{-- =============================================================
        ALPINE JS
    ============================================================= --}}
    <script>

        function availabilityManager() {

            return {

                date: '',

                availabilityType: 'open',

                selectedSlots: [...@json($timeSlots)],

                schedule: @json($schedule),

                /* =====================================================
                   CALENDAR
                   The month as a grid rather than a line at a time.
                   Every day gets a cell, and whatever was set on that
                   date is printed inside its own cell — one badge
                   naming the setting — so a whole month can be read at
                   a glance. A day's bookings are counted on the roster
                   below, first come, first served, so no cell carries
                   an hour of its own. Days nothing was set on say so,
                   and are still clickable: that is how a date gets set
                   in the first place.
                ===================================================== */
                monthCursor: @json($month),

                months: ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'],

                weekdays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],

                scheduleUrl: '{{ route('registrar.availability.schedule') }}',

                get monthLabel() {

                    const [year, month] = this.monthCursor.split('-').map(Number);

                    return this.months[month - 1] + ' ' + year;

                },

                /* The month's set dates, reachable straight from a cell. */
                get scheduleByDate() {

                    return this.schedule.reduce((byDate, item) => {

                        byDate[item.id] = item;

                        return byDate;

                    }, {});

                },

                /* =====================================================
                   THE GRID
                   Leading and trailing days are blank so the month
                   lands in whole weeks; each real day carries its date,
                   whether it has passed, and its own setting if any.
                ===================================================== */
                get calendarCells() {

                    const [year, month] = this.monthCursor.split('-').map(Number);

                    // Week starts Monday, matching the rest of the system.
                    const leading = (new Date(year, month - 1, 1).getDay() + 6) % 7;

                    const days = new Date(year, month, 0).getDate();

                    const today = this.todayIso();

                    const blank = () => ({
                        blank: true, day: null, iso: null,
                        today: false, past: false, entry: null
                    });

                    const cells = Array.from({ length: leading }, blank);

                    for (let day = 1; day <= days; day++) {

                        const iso = this.monthCursor + '-' + String(day).padStart(2, '0');

                        cells.push({
                            blank: false,
                            day: day,
                            iso: iso,
                            today: iso === today,
                            past: iso < today,
                            entry: this.scheduleByDate[iso] || null,
                        });

                    }

                    while (cells.length % 7 !== 0) {
                        cells.push(blank());
                    }

                    return cells;

                },

                todayIso() {

                    const now = new Date();

                    return now.getFullYear()
                        + '-' + String(now.getMonth() + 1).padStart(2, '0')
                        + '-' + String(now.getDate()).padStart(2, '0');

                },

                /* How a day is dressed: padding is inert, days already
                   behind us sit flat, and the rest invite a click. */
                cellClass(cell) {

                    if (cell.blank) {
                        return 'border-dashed border-gray-100 bg-gray-50/60';
                    }

                    if (cell.past) {
                        return 'border-gray-100 bg-gray-50';
                    }

                    if (cell.entry) {
                        return 'border-gray-200 bg-white cursor-pointer '
                            + 'hover:border-blue-300 hover:shadow-sm';
                    }

                    return 'border-gray-100 bg-white/70 cursor-pointer '
                        + 'hover:border-blue-200';

                },

                /* A day that has passed can still be read but no longer
                   written — saving one would be refused anyway. */
                editCell(cell) {

                    if (cell.blank || cell.past) {
                        return;
                    }

                    this.editItem({ id: cell.iso });

                },

                /* =====================================================
                   PAGING THE MONTH
                ===================================================== */
                shiftMonth(step) {

                    const [year, month] = this.monthCursor.split('-').map(Number);

                    const shifted = new Date(year, month - 1 + step, 1);

                    this.monthCursor = shifted.getFullYear()
                        + '-' + String(shifted.getMonth() + 1).padStart(2, '0');

                    this.loadMonth();

                },

                /* Follow a date typed straight into the date box. */
                syncMonthTo(iso) {

                    const month = (iso || '').slice(0, 7);

                    if (month && month !== this.monthCursor) {

                        this.monthCursor = month;

                        this.loadMonth();

                    }

                },

                loadMonth() {

                    fetch(
                        this.scheduleUrl + '?month=' + encodeURIComponent(this.monthCursor),
                        {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    )

                    .then(response => response.json())

                    .then(data => {

                        this.schedule = data.schedule || [];

                    })

                    .catch(() => {

                        Swal.fire({
                            icon: 'error',
                            title: 'Unable to Load',
                            text: 'That month\'s schedule could not be loaded.',
                            confirmButtonColor: '#1e3a8a'
                        });

                    });

                },

                /* =====================================================
                   DAY SIZE
                   How many of a day's appointments are the day's slots.
                   This is not read off the office's clock hours — those
                   decide the buckets students are booked into — it is
                   how full one day is allowed to get before the rest
                   are marked as coming after it.
                ===================================================== */
                slotsPerDay: @json($slotsPerDay),

                /* =====================================================
                   ROSTER
                   The day's bookings in the order they arrived. The
                   list numbers these instead of the office's clock
                   hours, so whoever booked first is the first row and
                   takes one of the day's slots before anyone below.
                ===================================================== */
                rosterByDate: @json($rosterByDate),

                get roster() {

                    return this.rosterByDate[this.date] || [];

                },

                get rosterCount() {

                    // Cancelled bookings stay on the list so they can be
                    // seen, but nobody is coming for them: they neither
                    // fill the day nor count towards it.
                    return this.roster.filter(row => ! row.cancelled).length;

                },

                settingsUrl: '{{ route('registrar.availability.settings', ['date' => ':date']) }}',

                saveUrl: '{{ route('registrar.availability.save') }}',


                /* =====================================================
                   PREVIEW
                ===================================================== */
                openPreview() {

                    if (this.availabilityType === 'closed') {
                        return 0;
                    }

                    // How many of the day's slots are still unfilled: a day
                    // holds slotsPerDay people, the ones already booked have
                    // taken their place, and the rest of the day is open.
                    return Math.max(0, this.slotsPerDay - this.rosterCount);

                },


                /* =====================================================
                   EDIT EXISTING DATE
                ===================================================== */
                editItem(item) {

                    this.date = item.id;

                    this.loadSelectedDate();

                    this.$refs.form.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                },


                /* =====================================================
                   LOAD DATE SETTINGS
                ===================================================== */
                loadSelectedDate() {

                    if (!this.date) {
                        return;
                    }

                    fetch(
                        this.settingsUrl.replace(':date', this.date),
                        {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    )

                    .then(response => response.json())

                    .then(data => {

                        this.availabilityType =
                            data.type;

                        this.selectedSlots =
                            data.slots || [];

                    })

                    .catch(() => {

                        Swal.fire({
                            icon: 'error',
                            title: 'Unable to Load',
                            text: 'The selected schedule could not be loaded.',
                            confirmButtonColor: '#1e3a8a'
                        });

                    });

                },


                /* =====================================================
                   SAVE AVAILABILITY
                ===================================================== */
                async saveAvailability() {

                    if (!this.date) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Date Required',
                            text: 'Please select a date first.',
                            confirmButtonColor: '#1e3a8a'
                        });

                        return;

                    }


                    const response = await fetch(
                        this.saveUrl,
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN':
                                    document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        .content
                            },

                            body: JSON.stringify({

                                date: this.date,

                                type: this.availabilityType,

                                slots: this.selectedSlots

                            })
                        }
                    );


                    const data =
                        await response.json();


                    if (!response.ok) {

                        const message =
                            data.errors
                                ? Object.values(data.errors)[0][0]
                                : (
                                    data.message
                                    ||
                                    'Something went wrong.'
                                );


                        Swal.fire({
                            icon: 'error',
                            title: 'Not Saved',
                            text: message,
                            confirmButtonColor: '#1e3a8a'
                        });

                        return;

                    }


                    this.schedule =
                        data.schedule;

                    // The date written may sit outside the month on
                    // screen, so bring the grid to the month that just
                    // changed — it is the one holding what was saved.
                    this.monthCursor =
                        data.month || this.monthCursor;


                    Swal.fire({
                        icon: 'success',
                        title: 'Availability Saved!',
                        text: data.message,
                        confirmButtonColor: '#1e3a8a'
                    });

                }

            }

        }

    </script>

</x-app-layout>