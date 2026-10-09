<x-app-layout>

    @php
        $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
        $documentRequest = $documentRequest ?? null;
        $selectedDocs = old('document_ids', $documentRequest?->documents->pluck('id')->all() ?? []);
        $isEdit = $documentRequest !== null;
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
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                        </svg>

                    </div>

                    <div>

                        <h1 class="text-2xl sm:text-3xl font-black text-[#173f7a]">
                            {{ $isEdit ? 'Edit Request' : 'Submit a Document Request' }}
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $isEdit
                                ? 'Update your request details below.'
                                : 'Select the documents you need and complete your request details.' }}
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
                  action="{{ $isEdit
                            ? route('student.documents.update', $documentRequest)
                            : route('student.documents.store') }}">

                @csrf

                @if ($isEdit)
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
                                    Request Details
                                </h2>

                                <p class="mt-1 text-xs text-blue-100">
                                    Fill in the required information to submit your request.
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


                        {{-- SECTION: DOCUMENT SELECTION --}}
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
                                        Document Selection
                                    </h3>

                                    <p class="text-xs text-gray-400">
                                        Choose the document(s) you need.
                                    </p>

                                </div>

                            </div>


                            <div class="grid sm:grid-cols-2 gap-3">

                                @foreach ($documents as $document)

                                    <label class="group flex items-start gap-3
                                                  rounded-xl border border-gray-200
                                                  p-4 cursor-pointer
                                                  transition hover:border-blue-400
                                                  hover:bg-blue-50/50">

                                        <input type="checkbox"
                                               name="document_ids[]"
                                               value="{{ $document->id }}"
                                               @checked(in_array($document->id, $selectedDocs))
                                               class="mt-0.5 w-4 h-4 rounded
                                                      text-blue-700
                                                      focus:ring-blue-600
                                                      border-gray-300">

                                        <span class="min-w-0">

                                            <span class="block text-sm font-bold text-gray-900">
                                                {{ $document->name }}
                                            </span>

                                            @if ($document->description)
                                                <span class="block text-xs text-gray-400 mt-0.5">
                                                    {{ $document->description }}
                                                </span>
                                            @endif

                                            @if ($document->fee > 0)
                                                <span class="inline-flex mt-1.5 px-2 py-0.5
                                                             rounded-full text-[11px] font-bold
                                                             bg-blue-50 text-blue-700">
                                                    PHP {{ number_format($document->fee, 2) }}
                                                </span>
                                            @endif

                                        </span>

                                    </label>

                                @endforeach


                                {{-- OTHERS --}}
                                <label class="group flex items-start gap-3
                                              rounded-xl border border-gray-200
                                              p-4 cursor-pointer
                                              transition hover:border-blue-400
                                              hover:bg-blue-50/50">

                                    <input type="checkbox"
                                           id="others"
                                           name="others"
                                           value="1"
                                           @checked(old('others', $documentRequest?->others_specification ? 1 : null))
                                           onchange="var field = document.getElementById('others-spec');
                                                     var input = document.getElementById('others_specification');
                                                     field.classList.toggle('hidden', ! this.checked);
                                                     if (this.checked) { input.focus(); } else { input.value = ''; }"
                                           class="mt-0.5 w-4 h-4 rounded
                                                  text-blue-700
                                                  focus:ring-blue-600
                                                  border-gray-300">

                                    <span class="min-w-0">

                                        <span class="block text-sm font-bold text-gray-900">
                                            Others
                                        </span>

                                        <span class="block text-xs text-gray-400 mt-0.5">
                                            Specify a document not listed above.
                                        </span>

                                    </span>

                                </label>

                            </div>


                            {{-- The field belongs to the option: it opens the
                                 moment Others is ticked and takes the caret,
                                 so the student can start writing the document
                                 nobody listed. Unticking it hands the field
                                 back empty, so nothing can be sent under an
                                 option they walked away from. --}}
                            <div id="others-spec"
                                 class="mt-3 {{ old('others', $documentRequest?->others_specification ? 1 : null) ? '' : 'hidden' }}">

                                <label for="others_specification"
                                       class="block text-sm font-bold text-gray-700 mb-2">

                                    Specify the document
                                    <span class="text-red-500">*</span>

                                </label>

                                <input id="others_specification"
                                       name="others_specification"
                                       type="text"
                                       value="{{ old('others_specification', $documentRequest?->others_specification ?? '') }}"
                                       placeholder="e.g. Certificate of Enrollment"
                                       class="w-full rounded-xl border-gray-300
                                              bg-white text-sm text-gray-800
                                              shadow-sm
                                              focus:border-blue-600
                                              focus:ring-blue-600">

                                <p class="mt-1.5
                                          text-[11px]
                                          text-gray-400">
                                    Whatever you write here is the document the office will prepare for you.
                                </p>

                            </div>

                        </div>


                        {{-- DIVIDER --}}
                        <div class="my-8 border-t border-gray-100"></div>


                        {{-- SECTION: PURPOSE OF REQUEST --}}
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
                                        Purpose of Request
                                    </h3>

                                    <p class="text-xs text-gray-400">
                                        Tell us why you need these documents.
                                    </p>

                                </div>

                            </div>


                            <div class="grid sm:grid-cols-2 gap-5">

                                <div>

                                    <label for="purpose_type"
                                           class="block text-sm font-bold text-gray-700 mb-2">

                                        Purpose Type
                                        <span class="text-red-500">*</span>

                                    </label>

                                    <select id="purpose_type"
                                            name="purpose_type"
                                            required
                                            x-on:change="document.getElementById('transfer-block').classList.toggle('hidden', $event.target.value !== 'transfer')"
                                            class="w-full rounded-xl border-gray-300
                                                   bg-white text-sm text-gray-800
                                                   shadow-sm
                                                   focus:border-blue-600
                                                   focus:ring-blue-600">

                                        <option value="">
                                            Select a purpose
                                        </option>

                                        @foreach ($purposeTypes as $value => $label)

                                            <option value="{{ $value }}"
                                                @selected(old('purpose_type', $documentRequest?->purpose_type ?? '') === $value)>

                                                {{ $label }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- TRANSFER TO --}}
                                <div id="transfer-block"
                                     class="{{ old('purpose_type', $documentRequest?->purpose_type ?? '') === 'transfer' ? '' : 'hidden' }}">

                                    <label for="transfer_to"
                                           class="block text-sm font-bold text-gray-700 mb-2">

                                        Name of School / Address
                                        <span class="text-red-500">*</span>

                                    </label>

                                    <input id="transfer_to"
                                           name="transfer_to"
                                           type="text"
                                           value="{{ old('transfer_to', $documentRequest?->transfer_to ?? '') }}"
                                           placeholder="e.g. UPLB, Los Banos, Laguna"
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


                        {{-- SECTION: STUDENT INFORMATION --}}
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
                                        Student Information
                                    </h3>

                                    <p class="text-xs text-gray-400">
                                        Your enrollment details.
                                    </p>

                                </div>

                            </div>


                            <div class="rounded-xl border border-gray-200
                                        bg-gray-50 p-5 mb-5">

                                <div class="grid sm:grid-cols-2 gap-x-6 gap-y-4">

                                    <div>
                                        <p class="text-[11px] font-bold uppercase
                                                  tracking-wide text-gray-400">Name</p>
                                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ auth()->user()->name }}</p>
                                    </div>

                                    <div>
                                        <p class="text-[11px] font-bold uppercase
                                                  tracking-wide text-gray-400">Student ID</p>
                                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $profile->student_id ?? '—' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-[11px] font-bold uppercase
                                                  tracking-wide text-gray-400">Course</p>
                                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $profile->course ?? '—' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-[11px] font-bold uppercase
                                                  tracking-wide text-gray-400">Year Level</p>
                                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $profile->year_level ?? '—' }}</p>
                                    </div>

                                </div>

                            </div>


                            <div class="grid sm:grid-cols-2 gap-5">

                                <div>

                                    <label for="educational_status"
                                           class="block text-sm font-bold text-gray-700 mb-2">

                                        Educational Status
                                        <span class="text-red-500">*</span>

                                    </label>

                                    <select id="educational_status"
                                            name="educational_status"
                                            required
                                            class="w-full rounded-xl border-gray-300
                                                   bg-white text-sm text-gray-800
                                                   shadow-sm
                                                   focus:border-blue-600
                                                   focus:ring-blue-600">

                                        <option value="">
                                            Select status
                                        </option>

                                        @foreach ($educationalStatuses as $value => $label)

                                            <option value="{{ $value }}"
                                                @selected(old('educational_status', $documentRequest?->educational_status ?? '') === $value)>

                                                {{ $label }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                <div>

                                    <label for="educational_level"
                                           class="block text-sm font-bold text-gray-700 mb-2">

                                        Educational Level
                                        <span class="text-red-500">*</span>

                                    </label>

                                    <select id="educational_level"
                                            name="educational_level"
                                            required
                                            class="w-full rounded-xl border-gray-300
                                                   bg-white text-sm text-gray-800
                                                   shadow-sm
                                                   focus:border-blue-600
                                                   focus:ring-blue-600">

                                        <option value="">
                                            Select level
                                        </option>

                                        @foreach ($educationalLevels as $value => $label)

                                            <option value="{{ $value }}"
                                                @selected(old('educational_level', $documentRequest?->educational_level ?? '') === $value)>

                                                {{ $label }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>

                            </div>

                        </div>


                        {{-- DIVIDER --}}
                        <div class="my-8 border-t border-gray-100"></div>


                        {{-- SECTION: MODE OF CLAIMING --}}
                        <div>

                            <div class="flex items-center gap-3 mb-5">

                                <div class="w-8 h-8 rounded-lg bg-blue-50
                                            flex items-center justify-center">

                                    <span class="text-sm font-black text-blue-700">
                                        4
                                    </span>

                                </div>

                                <div>

                                    <h3 class="text-sm font-black text-gray-900">
                                        Mode of Claiming
                                    </h3>

                                    <p class="text-xs text-gray-400">
                                        How do you want to claim your documents?
                                    </p>

                                </div>

                            </div>


                            <div class="flex flex-wrap gap-4">

                                <label class="flex items-start gap-3
                                              rounded-xl border border-gray-200
                                              p-4 cursor-pointer
                                              transition hover:border-blue-400
                                              hover:bg-blue-50/50">

                                    <input type="radio"
                                           name="claim_mode"
                                           value="personal"
                                           required
                                           @checked(old('claim_mode', $documentRequest?->claim_mode ?? 'personal') === 'personal')
                                           class="mt-0.5 w-4 h-4 text-blue-700
                                                  focus:ring-blue-600 border-gray-300">

                                    <span class="text-sm font-bold text-gray-900">
                                        I shall come back for my record personally.
                                    </span>

                                </label>


                                <label class="flex items-start gap-3
                                              rounded-xl border border-gray-200
                                              p-4 cursor-pointer
                                              transition hover:border-blue-400
                                              hover:bg-blue-50/50">

                                    <input type="radio"
                                           name="claim_mode"
                                           value="representative"
                                           x-on:change="document.getElementById('rep-block').classList.toggle('hidden', $event.target.value !== 'representative')"
                                           @checked(old('claim_mode', $documentRequest?->claim_mode ?? 'personal') === 'representative')
                                           class="mt-0.5 w-4 h-4 text-blue-700
                                                  focus:ring-blue-600 border-gray-300">

                                    <span class="text-sm font-bold text-gray-900">
                                        I shall have my authorized representative claim my request.
                                    </span>

                                </label>

                            </div>


                            <div id="rep-block"
                                 class="mt-4 {{ old('claim_mode', $documentRequest?->claim_mode ?? 'personal') === 'representative' ? '' : 'hidden' }}">

                                <label for="representative_name"
                                       class="block text-sm font-bold text-gray-700 mb-2">

                                    Representative Name
                                    <span class="text-red-500">*</span>

                                </label>

                                <input id="representative_name"
                                       name="representative_name"
                                       type="text"
                                       value="{{ old('representative_name', $documentRequest?->representative_name ?? '') }}"
                                       placeholder="Full name of your representative"
                                       class="w-full rounded-xl border-gray-300
                                              bg-white text-sm text-gray-800
                                              shadow-sm
                                              focus:border-blue-600
                                              focus:ring-blue-600">

                            </div>

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

                                <a href="{{ route('student.documents.index') }}"
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

                                    {{ $isEdit ? 'Save Changes' : 'Submit Request' }}

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
                    Your request information is securely handled by ISUFSTPASS.
                </span>

            </div>

        </div>

    </div>


    {{-- =========================================================
        JAVASCRIPT (initial visibility for prefilled forms)
    ========================================================== --}}
    <script>

        document.addEventListener('DOMContentLoaded', () => {

            const purposeSelect = document.getElementById('purpose_type');

            if (purposeSelect) {

                const transferBlock = document.getElementById('transfer-block');

                if (transferBlock) {

                    transferBlock.classList.toggle(
                        'hidden',
                        purposeSelect.value !== 'transfer'
                    );

                }

            }


            const repRadio = document.querySelector('input[name="claim_mode"][value="representative"]');

            if (repRadio) {

                const repBlock = document.getElementById('rep-block');

                if (repBlock) {

                    repBlock.classList.toggle('hidden', ! repRadio.checked);

                }

            }

        });

    </script>

</x-app-layout>