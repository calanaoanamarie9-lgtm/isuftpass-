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

                            Manage the dates and time slots available for
                            student appointments. Students can only book
                            slots marked as available.

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
                                @change="loadSelectedDate()"
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

                                            All official slots will be available.

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

                                <template x-for="n in rosterCount" :key="n">

                                    <div class="flex items-center
                                                gap-3
                                                rounded-xl
                                                border
                                                border-gray-100
                                                bg-white
                                                px-3 py-3">

                                        <span class="inline-flex
                                                     items-center
                                                     justify-center
                                                     w-6 h-6
                                                     shrink-0
                                                     rounded-full
                                                     bg-gray-100
                                                     text-gray-600
                                                     text-[11px]
                                                     font-extrabold
                                                     tabular-nums"
                                              x-text="n"></span>

                                        <span class="text-xs
                                                     font-semibold
                                                     text-gray-500"
                                              x-show="n <= slotsPerDay">
                                            Slot
                                        </span>

                                        <span class="ml-auto
                                                     text-[9px]
                                                     font-extrabold
                                                     uppercase
                                                     text-amber-600"
                                              x-show="n > slotsPerDay">
                                            Bukas na
                                        </span>

                                    </div>

                                </template>

                                <div x-show="rosterCount === 0"
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

                                    <p class="text-[10px]
                                              uppercase
                                              tracking-widest
                                              font-bold
                                              text-blue-500">

                                        Calendar Management

                                    </p>

                                    <h2 class="text-lg
                                               font-extrabold
                                               text-blue-950">

                                        Schedule

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


                        {{-- EMPTY --}}
                        <template x-if="schedule.length === 0">

                            <div class="min-h-[450px]
                                        flex
                                        flex-col
                                        items-center
                                        justify-center
                                        text-center">

                                <div class="w-20 h-20
                                            rounded-3xl
                                            bg-blue-50
                                            flex items-center
                                            justify-center
                                            mb-5">

                                    <svg
                                        class="w-9 h-9 text-blue-300"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >

                                        <rect
                                            x="3"
                                            y="4"
                                            width="18"
                                            height="17"
                                            rx="2"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="M16 2v4M8 2v4M3 10h18"
                                        />

                                    </svg>

                                </div>


                                <h3 class="text-base
                                           font-extrabold
                                           text-blue-950">

                                    No Schedule Set

                                </h3>

                                <p class="max-w-md
                                          mt-2
                                          text-sm
                                          leading-6
                                          text-gray-400">

                                    All dates and time slots remain available
                                    unless they are fully booked or manually
                                    closed.

                                </p>

                            </div>

                        </template>


                        {{-- SCHEDULE LIST --}}
                        <template x-if="schedule.length > 0">

                            <div class="space-y-4">

                                <template
                                    x-for="item in schedule"
                                    :key="item.id"
                                >

                                    <div
                                        @click="editItem(item)"
                                        class="group
                                               cursor-pointer
                                               rounded-2xl
                                               border border-gray-100
                                               bg-[#f8fafc]
                                               p-5
                                               transition
                                               hover:border-blue-300
                                               hover:bg-blue-50/40
                                               hover:shadow-sm"
                                    >

                                        {{-- TOP --}}
                                        <div class="flex flex-col
                                                    sm:flex-row
                                                    sm:items-center
                                                    sm:justify-between
                                                    gap-3">

                                            <div class="flex items-center
                                                        gap-3">

                                                <div class="w-11 h-11
                                                            rounded-xl
                                                            bg-white
                                                            border border-gray-100
                                                            flex items-center
                                                            justify-center
                                                            group-hover:bg-blue-50">

                                                    <svg class="w-5 h-5 text-blue-600"
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

                                                    <p
                                                        class="text-sm
                                                               font-extrabold
                                                               text-blue-950"
                                                        x-text="item.date"
                                                    ></p>

                                                </div>

                                            </div>


                                            {{-- TYPE --}}
                                            <span
                                                class="inline-flex
                                                       self-start
                                                       sm:self-center
                                                       px-3 py-1.5
                                                       rounded-full
                                                       text-[10px]
                                                       font-extrabold
                                                       uppercase
                                                       tracking-wide"
                                                :class="{
                                                    'bg-green-50 text-green-700 ring-1 ring-green-200':
                                                        item.type === 'open',

                                                    'bg-red-50 text-red-600 ring-1 ring-red-200':
                                                        item.type === 'closed',

                                                    'bg-blue-50 text-blue-700 ring-1 ring-blue-200':
                                                        item.type === 'slots'
                                                }"
                                                x-text="item.typeLabel"
                                            ></span>

                                        </div>


                                    </div>

                                </template>

                            </div>

                        </template>

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
                   How many appointments the selected date already
                   holds. The list numbers these instead of the office's
                   clock hours, so its length follows the day rather
                   than the shape of the working day.
                ===================================================== */
                appointmentsByDate: @json($appointmentsByDate),

                get rosterCount() {

                    return parseInt(this.appointmentsByDate[this.date] ?? 0, 10) || 0;

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


                    if (
                        this.availabilityType === 'slots'
                        &&
                        this.selectedSlots.length === 0
                    ) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'No Time Slots',
                            text: 'Please select at least one time slot.',
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