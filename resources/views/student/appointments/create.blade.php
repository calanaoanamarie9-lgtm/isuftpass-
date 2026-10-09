<x-app-layout>

    @php
        $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
        $appointment = $appointment ?? null;
    @endphp

    <div class="min-h-screen bg-[#f5f7fb] py-8">

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="mb-7">

                <a href="{{ route('student.requests.new') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900 transition">

                    <svg class="w-4 h-4"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 19l-7-7 7-7"/>

                    </svg>

                    Back to New Request
                </a>

                <div class="mt-5 flex items-start gap-4">

                    <div class="w-12 h-12 rounded-xl bg-blue-800
                                flex items-center justify-center shrink-0 shadow-sm">

                        <svg class="w-6 h-6 text-white"
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

                        <h1 class="text-2xl sm:text-3xl font-black text-[#173f7a]">
                            {{ $appointment ? 'Edit Appointment' : 'Book an Appointment' }}
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $appointment
                                ? 'Update your appointment details below.'
                                : 'Schedule a visit with the appropriate university office.' }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                ERROR MESSAGE
            ========================================================== --}}
            @if ($errors->any())

                <div class="mb-6 rounded-xl border border-red-200
                            bg-red-50 px-5 py-4">

                    <div class="flex items-start gap-3">

                        <div class="w-8 h-8 rounded-lg bg-red-100
                                    flex items-center justify-center shrink-0">

                            <svg class="w-5 h-5 text-red-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 8v4m0 4h.01M10.29 3.86l-7.5 13A2 2 0 004.52 20h14.96a2 2 0 001.73-3.14l-7.5-13a2 2 0 00-3.42 0z"/>

                            </svg>

                        </div>

                        <div>

                            <p class="text-sm font-bold text-red-800">
                                Please check the following:
                            </p>

                            <ul class="mt-1.5 text-sm text-red-700 list-disc pl-5 space-y-0.5">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                MAIN FORM
            ========================================================== --}}
            <form method="POST"
                  action="{{ $appointment
                            ? route('student.appointments.update', $appointment)
                            : route('student.appointments.store') }}">

                @csrf

                @if ($appointment)
                    @method('PUT')
                @endif


                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm overflow-hidden">


                    {{-- =================================================
                        FORM HEADER
                    ================================================== --}}
                    <div class="px-6 sm:px-8 py-5 border-b border-gray-100
                                bg-gradient-to-r from-blue-800 to-blue-700">

                        <div class="flex items-center justify-between gap-4">

                            <div>

                                <h2 class="text-lg font-bold text-white">
                                    Appointment Details
                                </h2>

                                <p class="mt-1 text-xs text-blue-100">
                                    Fill in the required information to schedule your visit.
                                </p>

                            </div>

                            <div class="hidden sm:flex items-center gap-2
                                        px-3 py-2 rounded-lg
                                        bg-white/10 border border-white/20">

                                <span class="w-2 h-2 rounded-full bg-green-400"></span>

                                <span class="text-xs font-semibold text-white">
                                    Secure Request
                                </span>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        FORM CONTENT
                    ================================================== --}}
                    <div class="p-6 sm:p-8">


                        {{-- SECTION: OFFICE & PURPOSE --}}
                        <div>

                            <div class="flex items-center gap-3 mb-5">

                                <div class="w-8 h-8 rounded-lg bg-blue-50
                                            flex items-center justify-center">

                                    <span class="text-sm font-black text-blue-700">
                                        1
                                    </span>

                                </div>

                                <div>

                                    <h3 class="text-sm font-black text-gray-900">
                                        Appointment Information
                                    </h3>

                                    <p class="text-xs text-gray-400">
                                        Select the office and tell us your purpose.
                                    </p>

                                </div>

                            </div>


                            <div class="grid sm:grid-cols-2 gap-5">

                                {{-- OFFICE --}}
                                <div>

                                    <label for="office"
                                           class="block text-sm font-bold text-gray-700 mb-2">

                                        Office
                                        <span class="text-red-500">*</span>

                                    </label>

                                    <select id="office"
                                            name="office"
                                            required
                                            class="w-full rounded-xl border-gray-300
                                                   bg-white text-sm text-gray-800
                                                   shadow-sm
                                                   focus:border-blue-600
                                                   focus:ring-blue-600">

                                        <option value="">
                                            Select an office
                                        </option>

                                        @foreach ($offices as $value => $label)

                                            <option value="{{ $value }}"
                                                @selected(old('office', $preselect['office'] ?? $appointment->office ?? null) === $value)>

                                                {{ $label }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- PURPOSE --}}
                                <div>

                                    <label for="purpose"
                                           class="block text-sm font-bold text-gray-700 mb-2">

                                        Purpose
                                        <span class="text-red-500">*</span>

                                    </label>

                                    <input id="purpose"
                                           name="purpose"
                                           type="text"
                                           value="{{ old('purpose', $preselect['purpose'] ?? $appointment->purpose ?? '') }}"
                                           required
                                           placeholder="e.g. Request for enrollment verification"
                                           class="w-full rounded-xl border-gray-300
                                                  bg-white text-sm text-gray-800
                                                  shadow-sm
                                                  focus:border-blue-600
                                                  focus:ring-blue-600">

                                </div>

                            </div>

                        </div>


                        {{-- DIVIDER --}}
                        <div class="my-8 border-t border-gray-100"></div>


                        {{-- SECTION: DATE --}}
                        <div>

                            <div class="flex items-center gap-3 mb-5">

                                <div class="w-8 h-8 rounded-lg bg-blue-50
                                            flex items-center justify-center">

                                    <span class="text-sm font-black text-blue-700">
                                        2
                                    </span>

                                </div>

                                <div>

                                    <h3 class="text-sm font-black text-gray-900">
                                        Preferred Date
                                    </h3>

                                    <p class="text-xs text-gray-400">
                                        Select an available date for your appointment.
                                    </p>

                                </div>

                            </div>


                            <input id="date"
                                   name="date"
                                   type="date"
                                   value="{{ old(
                                        'date',
                                        $appointment?->date->toDateString()
                                        ?? now()->toDateString()
                                   ) }}"
                                   min="{{ now()->toDateString() }}"
                                   required
                                   class="hidden">


<div class="rounded-xl border border-gray-200
                            bg-gray-50 p-2.5 sm:p-3
                            max-w-md mx-auto w-full">

                            <div class="w-full mx-auto">

                                <x-availability-calendar
                                    office=""
                                    endpoint="{{ route('student.appointments.availability') }}"
                                    :initial-month="substr(
                                        old(
                                            'date',
                                            $appointment?->date->toDateString()
                                            ?? now()->toDateString()
                                        ),
                                        0,
                                        7
                                    )"
                                    :selected="old(
                                        'date',
                                        $appointment?->date->toDateString()
                                        ?? now()->toDateString()
                                    )"
                                    watch-office="office"
                                    emit />

                            </div>

                            </div>


                            <div class="mt-3 flex items-start gap-2">

                                <svg class="w-4 h-4 text-blue-500 mt-0.5 shrink-0"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>

                                </svg>

                                <p class="text-xs text-gray-500">
                                    Green dates have available slots. Red dates are fully booked
                                    or unavailable. Select an office first.
                                </p>

                            </div>

                        </div>


                        {{-- SECTION: TIME
                             A student booking the Registrar picks a date
                             only; the office answers with the time when it
                             approves, so this whole block hides for it.
                        --}}
                        <div id="time-section">

                            <div class="my-8 border-t border-gray-100"></div>


                        <div>

                            <div class="flex items-center gap-3 mb-5">

                                <div class="w-8 h-8 rounded-lg bg-blue-50
                                            flex items-center justify-center">

                                    <span class="text-sm font-black text-blue-700">
                                        3
                                    </span>

                                </div>

                                <div>

                                    <h3 class="text-sm font-black text-gray-900">
                                        Select Time
                                    </h3>

                                    <p class="text-xs text-gray-400">
                                        Choose from the available appointment slots.
                                    </p>

                                </div>

                            </div>


                            <label for="time_slot"
                                   class="block text-sm font-bold text-gray-700 mb-2">

                                Available Time Slots
                                <span class="text-red-500">*</span>

                            </label>


                            <select id="time_slot"
                                    name="time_slot"
                                    required
                                    class="w-full rounded-xl border-gray-300
                                           bg-white text-sm text-gray-800
                                           shadow-sm
                                           focus:border-blue-600
                                           focus:ring-blue-600">

                                <option value="">
                                    Select an office and date first
                                </option>

                            </select>


                            <p id="slot-hint"
                               class="mt-2 text-xs text-gray-400">

                                Available slots will load automatically after
                                selecting an office and date.

                            </p>

                        </div>

                        </div>{{-- /#time-section --}}


                        {{-- DIVIDER --}}
                        <div class="my-8 border-t border-gray-100"></div>


                        {{-- SECTION: NOTES --}}
                        <div>

                            <div class="flex items-center gap-3 mb-5">

                                <div class="w-8 h-8 rounded-lg bg-blue-50
                                            flex items-center justify-center">

                                    <span id="notes-step"
                                          class="text-sm font-black text-blue-700">
                                        4
                                    </span>

                                </div>

                                <div>

                                    <h3 class="text-sm font-black text-gray-900">
                                        Additional Information
                                    </h3>

                                    <p class="text-xs text-gray-400">
                                        Add notes if the office needs more details.
                                    </p>

                                </div>

                            </div>


                            <label for="notes"
                                   class="block text-sm font-bold text-gray-700 mb-2">

                                Notes
                                <span class="text-xs font-normal text-gray-400">
                                    (Optional)
                                </span>

                            </label>

                            <textarea id="notes"
                                      name="notes"
                                      rows="4"
                                      placeholder="Any additional details for the office?"
                                      class="w-full rounded-xl border-gray-300
                                             bg-white text-sm text-gray-800
                                             shadow-sm resize-none
                                             focus:border-blue-600
                                             focus:ring-blue-600">{{ old('notes', $appointment->notes ?? '') }}</textarea>

                        </div>

                    </div>


                    {{-- =================================================
                        FORM FOOTER
                    ================================================== --}}
                    <div class="px-6 sm:px-8 py-5
                                border-t border-gray-100
                                bg-gray-50">

                        <div class="flex flex-col-reverse sm:flex-row
                                    sm:items-center sm:justify-between gap-4">

                            <p class="text-xs text-gray-400">
                                <span class="text-red-500">*</span>
                                Required fields
                            </p>


                            <div class="flex items-center gap-3">

                                <a href="{{ route('student.appointments.index') }}"
                                   class="inline-flex items-center justify-center
                                          px-5 py-2.5 rounded-xl
                                          border border-gray-300
                                          bg-white text-sm font-bold
                                          text-gray-700
                                          hover:bg-gray-100 transition">

                                    Cancel

                                </a>


                                <button type="submit"
                                        class="inline-flex items-center justify-center
                                               gap-2 px-6 py-2.5
                                               rounded-xl
                                               bg-blue-800
                                               hover:bg-blue-900
                                               text-white text-sm font-bold
                                               shadow-sm transition">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M5 13l4 4L19 7"/>

                                    </svg>

                                    {{ $appointment ? 'Update Appointment' : 'Book Appointment' }}

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </form>


            {{-- =========================================================
                FOOTER INFORMATION
            ========================================================== --}}
            <div class="mt-5 flex items-center justify-center gap-2 text-xs text-gray-400">

                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 15v2m0-8v4m0-9a9 9 0 100 18 9 9 0 000-18z"/>

                </svg>

                <span>
                    Your appointment information is securely handled by ISUFSTPASS.
                </span>

            </div>

        </div>

    </div>


    {{-- =========================================================
        JAVASCRIPT
    ========================================================== --}}
    <script>

        const officeEl = document.getElementById('office');
        const dateEl = document.getElementById('date');
        const slotSelect = document.getElementById('time_slot');
        const slotHint = document.getElementById('slot-hint');
        const timeSection = document.getElementById('time-section');
        const notesStep = document.getElementById('notes-step');
        const registrarOffice = {{ Js::from(\App\Enums\Office::Registrar->value) }};


        // The Registrar does not ask for a time: booking it shows a date only,
        // and the office answers with a time when it approves. A disabled
        // control is not submitted, so the request reaches the server with no
        // slot at all — which is exactly what a Registrar booking is.
        function applyOfficeMode() {

            const registrar = officeEl.value === registrarOffice;

            timeSection.classList.toggle('hidden', registrar);
            slotSelect.disabled = registrar;
            slotSelect.required = ! registrar;

            if (notesStep) {

                notesStep.textContent = registrar ? '3' : '4';

            }

        }


        const keepOption = (label, value = '') => {

            slotSelect.innerHTML = '';

            const placeholder = document.createElement('option');

            placeholder.value = value;
            placeholder.textContent = label;

            slotSelect.appendChild(placeholder);

        };


        async function loadSlots() {

            const office = officeEl.value;
            const date = dateEl.value;


            if (office === registrarOffice) {

                applyOfficeMode();

                keepOption('The office will set your time');

                slotHint.textContent =
                    'The Registrar assigns the exact time when your appointment is approved.';

                return;

            }


            if (!office || !date) {

                keepOption('Select an office and date first');

                slotHint.textContent =
                    'Available slots will load automatically after selecting an office and date.';

                return;

            }


            keepOption('Loading available slots...');

            slotHint.textContent =
                'Checking availability for ' + office + ' on ' + date + '…';


            try {

                const response = await fetch(
                    '{{ route('student.appointments.slots') }}?office='
                    + encodeURIComponent(office)
                    + '&date='
                    + encodeURIComponent(date)
                );


                const slots = await response.json();


                keepOption(
                    'Select an available time slot',
                    ''
                );


                const selected =
                    {{ Js::from(old(
                        'time_slot',
                        $appointment->time_slot ?? ''
                    )) }};


                slots.forEach(slot => {

                    const full = slot.remaining < 1;

                    const option =
                        document.createElement('option');

                    option.value = slot.time;

                    option.disabled = full;


                    option.textContent = full
                        ? slot.time + ' — Full'
                        : slot.time + ' — '
                            + slot.remaining
                            + ' seat'
                            + (slot.remaining === 1 ? '' : 's')
                            + ' left';


                    if (slot.time === selected) {

                        option.selected = true;

                    }


                    slotSelect.appendChild(option);

                });


                slotHint.textContent =
                    slots.length + ' time slots available for the selected day.';

            }


            catch (e) {

                keepOption(
                    'Failed to load slots. Please try again.'
                );

                slotHint.textContent =
                    'Unable to check availability. Please try again.';

            }

        }


        officeEl.addEventListener(
            'change',
            () => {

                applyOfficeMode();

                loadSlots();

            }
        );


        dateEl.addEventListener(
            'change',
            loadSlots
        );


        window.addEventListener(
            'date-selected',
            event => {

                dateEl.value =
                    event.detail.date;

                dateEl.dispatchEvent(
                    new Event('change')
                );

            }
        );


        document.addEventListener(
            'DOMContentLoaded',
            () => {

                applyOfficeMode();

                if (
                    officeEl.value &&
                    dateEl.value
                ) {

                    loadSlots();

                }

            }
        );

    </script>

</x-app-layout>