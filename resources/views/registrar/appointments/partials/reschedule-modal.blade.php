{{-- =========================================================
    ISUFSTPASS - RESCHEDULE APPOINTMENT MODAL
    Open via:
    $dispatch('open-reschedule', {
        id,
        student,
        studentId,
        reference,
        office,
        dateLabel,
        timeSlot,
        reason
    })
========================================================= --}}

<style>
    [x-cloak] {
        display: none !important;
    }
</style>


<div
    x-data="rescheduleModal()"
    @open-reschedule.window="start($event.detail)"
    x-cloak
    x-show="open"
    class="fixed inset-0 z-50"
    style="display: none;"
>

    {{-- =====================================================
        BACKDROP
    ====================================================== --}}
    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        @click="if (!busy) close()"
        class="absolute inset-0 bg-[#071a38]/70 backdrop-blur-sm"
    ></div>


    {{-- =====================================================
        MODAL CONTAINER
    ====================================================== --}}
    <div class="absolute inset-0 overflow-y-auto">

        <div class="min-h-full flex items-center justify-center p-4 sm:p-6">

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-5 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-5 scale-95"
                class="relative w-full max-w-2xl
                       bg-white
                       rounded-3xl
                       shadow-2xl
                       overflow-hidden"
            >

                {{-- =================================================
                    HEADER
                ================================================== --}}
                <div class="relative
                            bg-gradient-to-r
                            from-[#102d5b]
                            via-blue-900
                            to-[#0b4ea2]
                            px-6 sm:px-7
                            py-6">

                    {{-- Decorative accent --}}
                    <div class="absolute -right-14 -top-16
                                w-44 h-44
                                rounded-full
                                bg-white/5">
                    </div>


                    <div class="relative flex items-start justify-between gap-4">

                        <div class="flex items-center gap-4">

                            {{-- ICON --}}
                            <div class="w-12 h-12
                                        rounded-2xl
                                        bg-white/10
                                        border border-white/10
                                        flex items-center justify-center
                                        shrink-0">

                                <svg class="w-6 h-6 text-yellow-300"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M15 14l2 2 4-4"/>

                                </svg>

                            </div>


                            <div>

                                <p class="text-[11px]
                                          font-bold
                                          uppercase
                                          tracking-[0.18em]
                                          text-blue-200">

                                    ISUFSTPASS Appointment Services

                                </p>

                                <h2 class="mt-1
                                           text-xl sm:text-2xl
                                           font-extrabold
                                           text-white">

                                    Reschedule Appointment

                                </h2>

                                <p class="mt-1
                                          text-sm
                                          text-blue-100">

                                    Select a new available date and time slot.

                                </p>

                            </div>

                        </div>


                        {{-- CLOSE --}}
                        <button
                            type="button"
                            @click="if (!busy) close()"
                            class="w-10 h-10
                                   rounded-xl
                                   bg-white/10
                                   border border-white/10
                                   text-white/80
                                   hover:bg-white/20
                                   hover:text-white
                                   transition
                                   flex items-center justify-center
                                   shrink-0"
                        >

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>

                            </svg>

                        </button>

                    </div>

                </div>


                {{-- =================================================
                    BODY
                ================================================== --}}
                <form
                    @submit.prevent="submit()"
                    class="p-6 sm:p-7 space-y-6"
                >


                    {{-- =================================================
                        STUDENT INFORMATION
                    ================================================== --}}
                    <div class="rounded-2xl
                                border border-blue-100
                                bg-blue-50/70
                                p-4">

                        <div class="flex items-center gap-4">

                            {{-- AVATAR --}}
                            <div class="w-12 h-12
                                        rounded-2xl
                                        bg-blue-700
                                        text-white
                                        flex items-center justify-center
                                        font-extrabold
                                        text-lg
                                        shadow-sm
                                        shrink-0">

                                <span x-text="(appointment?.student || '?').charAt(0).toUpperCase()"></span>

                            </div>


                            <div class="min-w-0 flex-1">

                                <p class="text-[10px]
                                          font-bold
                                          uppercase
                                          tracking-[0.14em]
                                          text-blue-500">

                                    Student

                                </p>

                                <p class="mt-0.5
                                          text-base
                                          font-extrabold
                                          text-[#102d5b]
                                          truncate"
                                   x-text="appointment?.student">
                                </p>

                                <div class="mt-1
                                            flex flex-wrap
                                            items-center
                                            gap-x-2 gap-y-1
                                            text-xs
                                            text-gray-500">

                                    <span
                                        x-show="appointment?.studentId"
                                        x-text="'Student ID: ' + appointment?.studentId">
                                    </span>

                                    <span
                                        x-show="appointment?.studentId && appointment?.reference"
                                        class="text-gray-300">
                                        •
                                    </span>

                                    <span x-text="appointment?.reference"></span>

                                </div>

                            </div>


                            {{-- OFFICE BADGE --}}
                            <template x-if="appointment?.office">

                                <span class="hidden sm:inline-flex
                                             items-center
                                             px-3 py-1.5
                                             rounded-full
                                             bg-white
                                             border border-blue-100
                                             text-[11px]
                                             font-bold
                                             text-blue-700
                                             shadow-sm"
                                      x-text="appointment?.office">
                                </span>

                            </template>

                        </div>

                    </div>


                    {{-- =================================================
                        CURRENT SCHEDULE
                    ================================================== --}}
                    <div>

                        <p class="text-[11px]
                                  font-bold
                                  uppercase
                                  tracking-wider
                                  text-gray-400
                                  mb-2">

                            Current Schedule

                        </p>


                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                            {{-- DATE --}}
                            <div class="rounded-2xl
                                        border border-gray-200
                                        bg-gray-50
                                        p-4">

                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10
                                                rounded-xl
                                                bg-white
                                                border border-gray-200
                                                text-blue-700
                                                flex items-center justify-center">

                                        <svg class="w-5 h-5"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                                        </svg>

                                    </div>

                                    <div>

                                        <p class="text-[10px]
                                                  uppercase
                                                  font-bold
                                                  tracking-wider
                                                  text-gray-400">

                                            Date

                                        </p>

                                        <p class="mt-0.5
                                                  text-sm
                                                  font-bold
                                                  text-gray-800"
                                           x-text="appointment?.dateLabel">
                                        </p>

                                    </div>

                                </div>

                            </div>


                            {{-- TIME --}}
                            <div class="rounded-2xl
                                        border border-gray-200
                                        bg-gray-50
                                        p-4">

                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10
                                                rounded-xl
                                                bg-white
                                                border border-gray-200
                                                text-blue-700
                                                flex items-center justify-center">

                                        <svg class="w-5 h-5"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>

                                        </svg>

                                    </div>

                                    <div>

                                        <p class="text-[10px]
                                                  uppercase
                                                  font-bold
                                                  tracking-wider
                                                  text-gray-400">

                                            Time Slot

                                        </p>

                                        <p class="mt-0.5
                                                  text-sm
                                                  font-bold
                                                  text-gray-800"
                                           x-text="appointment?.timeSlot">
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        PREVIOUS RESCHEDULE REASON
                    ================================================== --}}
                    <div
                        x-show="appointment?.reason"
                        x-transition
                        class="rounded-2xl
                               bg-amber-50
                               border border-amber-200
                               p-4"
                    >

                        <div class="flex items-start gap-3">

                            <div class="w-9 h-9
                                        rounded-xl
                                        bg-amber-100
                                        text-amber-600
                                        flex items-center justify-center
                                        shrink-0">

                                <svg class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 9v4m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3h15.64a2 2 0 001.71-3l-7.82-13a2 2 0 00-3.42 0z"/>

                                </svg>

                            </div>


                            <div>

                                <p class="text-[10px]
                                          uppercase
                                          font-bold
                                          tracking-wider
                                          text-amber-600">

                                    Previous Reschedule Reason

                                </p>

                                <p class="mt-1
                                          text-sm
                                          leading-5
                                          text-gray-700"
                                   x-text="appointment?.reason || ''">
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        NEW SCHEDULE
                    ================================================== --}}
                    <div>

                        <div class="flex items-center gap-2 mb-4">

                            <div class="w-7 h-7
                                        rounded-lg
                                        bg-blue-100
                                        text-blue-700
                                        flex items-center justify-center">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M5 13l4 4L19 7"/>

                                </svg>

                            </div>

                            <h3 class="text-sm
                                       font-extrabold
                                       text-[#102d5b]">

                                Select New Schedule

                            </h3>

                        </div>


                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">


                            {{-- NEW DATE --}}
                            <div>

                                <label class="block
                                              text-xs
                                              font-bold
                                              text-gray-600
                                              mb-2">

                                    New Date

                                    <span class="text-red-500">*</span>

                                </label>


                                <input
                                    type="date"
                                    x-model="newDate"
                                    min="{{ now()->toDateString() }}"
                                    @change="loadSlots()"
                                    class="w-full
                                           h-12
                                           rounded-xl
                                           border-gray-200
                                           bg-gray-50
                                           text-sm
                                           px-4
                                           focus:bg-white
                                           focus:border-blue-500
                                           focus:ring-blue-200
                                           transition"
                                >

                            </div>


                            {{-- NEW TIME --}}
                            <div>

                                <label class="block
                                              text-xs
                                              font-bold
                                              text-gray-600
                                              mb-2">

                                    Available Time Slot

                                    <span class="text-red-500">*</span>

                                </label>


                                <select
                                    x-model="timeSlot"
                                    :disabled="!newDate || loadingSlots || slots.length === 0"
                                    class="w-full
                                           h-12
                                           rounded-xl
                                           border-gray-200
                                           bg-gray-50
                                           text-sm
                                           px-4
                                           focus:bg-white
                                           focus:border-blue-500
                                           focus:ring-blue-200
                                           disabled:bg-gray-100
                                           disabled:text-gray-400
                                           disabled:cursor-not-allowed"
                                >

                                    <option value="" disabled>
                                        <span x-text="timePlaceholder"></span>
                                    </option>


                                    <template x-for="slot in slots" :key="slot.time">

                                        <option
                                            :value="slot.time"
                                            x-text="slot.time + ' — ' + slot.remaining + (slot.remaining === 1 ? ' seat left' : ' seats left')">
                                        </option>

                                    </template>

                                </select>


                                {{-- Loading --}}
                                <div
                                    x-show="loadingSlots"
                                    class="mt-2
                                           flex items-center gap-2
                                           text-xs
                                           text-blue-600"
                                >

                                    <svg class="animate-spin w-3.5 h-3.5"
                                         fill="none"
                                         viewBox="0 0 24 24">

                                        <circle class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4">
                                        </circle>

                                        <path class="opacity-75"
                                              fill="currentColor"
                                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                        </path>

                                    </svg>

                                    Checking available slots...

                                </div>


                                <p
                                    x-show="slotError"
                                    x-text="slotError"
                                    class="mt-2 text-xs font-medium text-red-600"
                                ></p>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        REASON
                    ================================================== --}}
                    <div>

                        <div class="flex items-center justify-between mb-2">

                            <label class="text-xs
                                          font-bold
                                          text-gray-600">

                                Reason for Rescheduling

                                <span class="text-red-500">*</span>

                            </label>

                            <span class="text-[10px] text-gray-400">
                                Maximum 500 characters
                            </span>

                        </div>


                        <textarea
                            x-model="reason"
                            rows="4"
                            maxlength="500"
                            required
                            placeholder="Enter the reason why the appointment needs to be rescheduled..."
                            class="w-full
                                   rounded-2xl
                                   border-gray-200
                                   bg-gray-50
                                   px-4 py-3
                                   text-sm
                                   resize-none
                                   focus:bg-white
                                   focus:border-blue-500
                                   focus:ring-blue-200
                                   transition"
                        ></textarea>

                    </div>


                    {{-- =================================================
                        ERROR BOX
                    ================================================== --}}
                    <div
                        x-show="formError"
                        x-transition
                        class="rounded-2xl
                               bg-red-50
                               border border-red-200
                               px-4 py-3"
                    >

                        <div class="flex items-start gap-3">

                            <svg class="w-5 h-5
                                        text-red-500
                                        shrink-0 mt-0.5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>

                            </svg>

                            <p class="text-sm
                                      font-medium
                                      text-red-700"
                               x-text="formError">
                            </p>

                        </div>

                    </div>


                    {{-- =================================================
                        INFO NOTICE
                    ================================================== --}}
                    <div class="rounded-2xl
                                bg-blue-50
                                border border-blue-100
                                px-4 py-3">

                        <div class="flex items-start gap-3">

                            <div class="w-8 h-8
                                        rounded-lg
                                        bg-blue-100
                                        text-blue-700
                                        flex items-center
                                        justify-center
                                        shrink-0
                                        font-bold">

                                i

                            </div>

                            <div>

                                <p class="text-xs
                                          font-bold
                                          text-blue-900">

                                    Student Notification

                                </p>

                                <p class="mt-1
                                          text-xs
                                          leading-5
                                          text-blue-700">

                                    The student will be notified of the new appointment date,
                                    time, and rescheduling reason.

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        ACTION BUTTONS
                    ================================================== --}}
                    <div class="flex flex-col-reverse
                                sm:flex-row
                                sm:items-center
                                sm:justify-end
                                gap-3
                                pt-2">

                        {{-- CANCEL --}}
                        <button
                            type="button"
                            @click="close()"
                            :disabled="busy"
                            class="w-full sm:w-auto
                                   px-6 py-3
                                   rounded-xl
                                   bg-white
                                   border border-gray-200
                                   text-sm
                                   font-bold
                                   text-gray-600
                                   hover:bg-gray-50
                                   hover:text-gray-800
                                   transition
                                   disabled:opacity-50"
                        >

                            Cancel

                        </button>


                        {{-- SUBMIT --}}
                        <button
                            type="submit"
                            :disabled="busy || !newDate || !timeSlot || !reason.trim()"
                            class="w-full sm:w-auto
                                   inline-flex
                                   items-center
                                   justify-center
                                   gap-2
                                   px-6 py-3
                                   rounded-xl
                                   bg-blue-700
                                   text-white
                                   text-sm
                                   font-bold
                                   hover:bg-blue-800
                                   shadow-sm
                                   transition
                                   disabled:opacity-50
                                   disabled:cursor-not-allowed"
                        >

                            {{-- Spinner --}}
                            <svg
                                x-show="busy"
                                class="animate-spin w-4 h-4"
                                fill="none"
                                viewBox="0 0 24 24"
                            >

                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4">
                                </circle>

                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>

                            </svg>


                            {{-- Calendar icon --}}
                            <svg
                                x-show="!busy"
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                            </svg>


                            <span
                                x-text="busy
                                    ? 'Rescheduling...'
                                    : 'Confirm Reschedule'">
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>



{{-- =========================================================
    ALPINE JS
========================================================= --}}
<script>

function rescheduleModal() {

    return {

        open: false,
        busy: false,

        appointment: null,

        newDate: '',
        timeSlot: '',
        reason: '',

        slots: [],
        loadingSlots: false,
        slotError: '',
        formError: '',

        slotsUrl: '{{ route('registrar.appointments.slots') }}',

        actionTemplate:
            '{{ url('registrar/appointments') }}/:id/reschedule',


        get timePlaceholder() {

            if (!this.newDate) {
                return 'Select a date first';
            }

            if (this.loadingSlots) {
                return 'Loading available slots...';
            }

            if (this.slots.length === 0) {
                return 'No available slots';
            }

            return 'Select an available time';

        },


        start(detail) {

            this.appointment = detail;

            this.newDate = '';

            this.timeSlot = '';

            this.reason = '';

            this.slots = [];

            this.slotError = '';

            this.formError = '';

            this.open = true;

        },


        close() {

            if (this.busy) {
                return;
            }

            this.open = false;

        },


        loadSlots() {

            if (!this.newDate || !this.appointment) {
                return;
            }

            this.loadingSlots = true;

            this.slotError = '';

            this.timeSlot = '';

            this.slots = [];


            const url =
                this.slotsUrl
                + '?office='
                + encodeURIComponent(this.appointment.office)
                + '&date='
                + encodeURIComponent(this.newDate)
                + '&ignore_id='
                + encodeURIComponent(this.appointment.id);


            fetch(url, {

                headers: {
                    'Accept': 'application/json'
                }

            })

            .then(async (response) => {

                if (!response.ok) {
                    throw new Error('Unable to load available slots.');
                }

                return response.json();

            })

            .then((data) => {

                this.slots = data.filter((slot) => {

                    return slot.is_open && slot.remaining > 0;

                });


                if (this.slots.length === 0) {

                    this.slotError =
                        'No open time slots are available on this date. Please choose another date.';

                }

                this.loadingSlots = false;

            })

            .catch(() => {

                this.loadingSlots = false;

                this.slotError =
                    'Could not load available time slots. Please try again.';

            });

        },


        submit() {

            if (!this.appointment || this.busy) {
                return;
            }


            if (!this.newDate) {

                this.formError =
                    'Please select a new appointment date.';

                return;

            }


            if (!this.timeSlot) {

                this.formError =
                    'Please select an available time slot.';

                return;

            }


            if (!this.reason.trim()) {

                this.formError =
                    'Please provide a reason for rescheduling.';

                return;

            }


            this.busy = true;

            this.formError = '';


            fetch(
                this.actionTemplate.replace(
                    ':id',
                    this.appointment.id
                ),
                {

                    method: 'PUT',

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

                        date: this.newDate,

                        time_slot: this.timeSlot,

                        reschedule_reason:
                            this.reason.trim()

                    })

                }
            )

            .then(async (response) => {

                const data =
                    await response
                        .json()
                        .catch(() => ({}));


                if (!response.ok) {

                    this.busy = false;

                    this.formError =
                        data.errors
                            ? Object.values(
                                data.errors
                            )[0][0]
                            : (
                                data.message
                                || 'Could not reschedule the appointment.'
                            );

                    return;

                }


                if (data.redirect) {

                    window.location.href =
                        data.redirect;

                    return;

                }


                window.location.reload();

            })

            .catch(() => {

                this.busy = false;

                this.formError =
                    'Network error. Please try again.';

            });

        }

    };

}

</script>
