<x-app-layout>

    @php
        $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
        $appointment = $appointment ?? null;
        $requester = $requester ?? auth()->user();
        $requesterType = $requester->classification();
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
                                @php
                                    $selectedOffice   = old('office', $preselect['office'] ?? $appointment->office ?? null);
                                    $selectedPurpose  = old('purpose', $preselect['purpose'] ?? $appointment->purpose ?? '');
                                    $officePurposes   = $purposes[$selectedOffice] ?? [];
                                    $otherPurpose     = \App\Support\AppointmentPurposes::OTHER;
                                @endphp

                                <div>

                                    <label for="purpose"
                                           class="block text-sm font-bold text-gray-700 mb-2">

                                        Purpose
                                        <span class="text-red-500">*</span>

                                    </label>

                                    <select id="purpose"
                                            name="purpose"
                                            required
                                            class="w-full rounded-xl border-gray-300
                                                   bg-white text-sm text-gray-800
                                                   shadow-sm
                                                   focus:border-blue-600
                                                   focus:ring-blue-600">

                                        <option value="">
                                            Select a purpose
                                        </option>

                                        {{-- A purpose kept from an earlier booking or a catalogue deep-link that is
                                             no longer in the list stays selectable and selected. --}}
                                        @if ($selectedPurpose !== '' && $selectedPurpose !== $otherPurpose && ! array_key_exists($selectedPurpose, $officePurposes))
                                            <option value="{{ $selectedPurpose }}" selected>
                                                {{ $selectedPurpose }}
                                            </option>
                                        @endif

                                        @foreach ($officePurposes as $value => $label)

                                            <option value="{{ $value }}"
                                                @selected($selectedPurpose === $value)>

                                                {{ $label }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- OTHER PURPOSE — revealed when "Other" is chosen --}}
                                <div id="purpose-other-group"
                                     @class(['hidden' => $selectedPurpose !== $otherPurpose])>

                                    <label for="purpose_other"
                                           class="block text-sm font-bold text-gray-700 mb-2">

                                        Please specify purpose
                                        <span class="text-red-500">*</span>

                                    </label>

                                    <input id="purpose_other"
                                           name="purpose_other"
                                           type="text"
                                           value="{{ old('purpose_other') }}"
                                           placeholder="Type your purpose"
                                           @disabled($selectedPurpose !== $otherPurpose)
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



                        {{-- DIVIDER --}}
                        <div class="my-8 border-t border-gray-100"></div>


                        {{-- SECTION: REQUESTER DETAILS (role specific) --}}
                        @if (in_array($requesterType, ['parent', 'guest'], true))

                            <div>

                                <div class="flex items-center gap-3 mb-5">

                                    <div class="w-8 h-8 rounded-lg bg-blue-50
                                                flex items-center justify-center">

                                        <span id="requester-step"
                                              class="text-sm font-black text-blue-700">
                                            4
                                        </span>

                                    </div>

                                    <div>

                                        <h3 class="text-sm font-black text-gray-900">
                                            {{ $requesterType === 'parent' ? "Child's Details" : 'Visitor Identification' }}
                                        </h3>

                                        <p class="text-xs text-gray-400">
                                            {{ $requesterType === 'parent'
                                                ? "We need your child's Student ID to match the consultation."
                                                : 'A valid ID is required for campus entry.' }}
                                        </p>

                                    </div>

                                </div>


                                @if ($requesterType === 'parent' && blank($requester->student_id))

                                    <div>

                                        <label for="student_id"
                                               class="block text-sm font-bold text-gray-700 mb-2">

                                            Child's Student ID
                                            <span class="text-red-500">*</span>

                                        </label>

                                        <input type="text"
                                               id="student_id"
                                               name="student_id"
                                               required
                                               value="{{ old('student_id') }}"
                                               placeholder="e.g. 2024-00123"
                                               class="w-full rounded-xl border-gray-300
                                                      bg-white text-sm text-gray-800
                                                      shadow-sm
                                                      focus:border-blue-600
                                                      focus:ring-blue-600">

                                        @error('student_id')
                                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                        @enderror

                                    </div>

                                @elseif ($requesterType === 'parent')

                                    <p class="text-sm text-gray-600">
                                        Child's Student ID on file:
                                        <span class="font-bold text-gray-900">{{ $requester->student_id }}</span>
                                    </p>

                                @elseif (blank($requester->valid_id))

                                    <div>

                                        <label for="valid_id"
                                               class="block text-sm font-bold text-gray-700 mb-2">

                                            Valid ID (type &amp; number)
                                            <span class="text-red-500">*</span>

                                        </label>

                                        <input type="text"
                                               id="valid_id"
                                               name="valid_id"
                                               required
                                               value="{{ old('valid_id') }}"
                                               placeholder="e.g. Driver's License N01-23-456789"
                                               class="w-full rounded-xl border-gray-300
                                                      bg-white text-sm text-gray-800
                                                      shadow-sm
                                                      focus:border-blue-600
                                                      focus:ring-blue-600">

                                        @error('valid_id')
                                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                        @enderror

                                    </div>

                                @else

                                    <p class="text-sm text-gray-600">
                                        Valid ID on file:
                                        <span class="font-bold text-gray-900">{{ $requester->valid_id }}</span>
                                    </p>

                                @endif

                            </div>


                            <div class="my-8 border-t border-gray-100"></div>

                        @endif


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
        const notesStep = document.getElementById('notes-step');
        const requesterStep = document.getElementById('requester-step');

        const purposeSelect = document.getElementById('purpose');
        const purposeOtherGroup = document.getElementById('purpose-other-group');
        const purposeOtherInput = document.getElementById('purpose_other');
        const otherPurpose = {{ Js::from(\App\Support\AppointmentPurposes::OTHER) }};
        const purposesByOffice = {{ Js::from($purposes) }};


        // The student asks for a day; the office answers with the hour, so
        // there is no time to choose here. The requester's details (a
        // parent's child, a guest's ID) sit between the date and the notes
        // for those account types, so the notes step shifts down by one for
        // them.
        function applyStepNumbers() {

            if (! notesStep) {
                return;
            }

            let step = 3;

            if (requesterStep) {

                step += 1;

                requesterStep.textContent = '3';

            }

            notesStep.textContent = String(step);

        }

        // The Purpose options depend on the chosen office, so they are rebuilt
        // on every change. A purpose that no longer appears in the new list
        // (legacy value or a catalogue deep-link) is kept selectable so it is
        // never silently dropped.
        function renderPurposes(office, selected = '') {

            const list = purposesByOffice[office] || {};

            purposeSelect.innerHTML = '';

            const placeholder = document.createElement('option');

            placeholder.value = '';
            placeholder.textContent = 'Select a purpose';

            purposeSelect.appendChild(placeholder);


            if (selected && selected !== otherPurpose && ! (selected in list)) {

                const extra = document.createElement('option');

                extra.value = selected;
                extra.textContent = selected;

                purposeSelect.appendChild(extra);

            }


            Object.entries(list).forEach(([value, label]) => {

                const option = document.createElement('option');

                option.value = value;
                option.textContent = label;

                if (value === selected) {

                    option.selected = true;

                }

                purposeSelect.appendChild(option);

            });

            toggleOtherPurpose();
        }


        // "Other" reveals the mandatory "Please specify purpose" box; every
        // other purpose empties and disables it, so the box is not submitted
        // at all (an emptied text input arrives as null on the server).
        function toggleOtherPurpose() {

            const isOther = purposeSelect.value === otherPurpose;

            purposeOtherGroup.classList.toggle('hidden', ! isOther);
            purposeOtherInput.required = isOther;
            purposeOtherInput.disabled = ! isOther;

            if (! isOther) {

                purposeOtherInput.value = '';

            }

        }



        // Choosing "Other" must reveal the mandatory "Please specify purpose"
        // box right away, so this listens on the Purpose select itself - not
        // only when the office (and with it the whole option list) changes.
        purposeSelect.addEventListener(
            'change',
            toggleOtherPurpose
        );


        officeEl.addEventListener(
            'change',
            () => {

                applyStepNumbers();

                renderPurposes(officeEl.value, '');

            }
        );


        window.addEventListener(
            'date-selected',
            event => {

                dateEl.value =
                    event.detail.date;

            }
        );


        document.addEventListener(
            'DOMContentLoaded',
            () => {

                applyStepNumbers();

                renderPurposes(officeEl.value, purposeSelect.value);

            }
        );

    </script>

</x-app-layout>
