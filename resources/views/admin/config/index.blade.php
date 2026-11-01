<x-app-layout>

    <div class="min-h-screen bg-[#f5f7fb] py-8">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6">

                <div class="px-6 py-5 flex flex-col sm:flex-row
                            sm:items-center sm:justify-between gap-4">

                    <div class="flex items-center gap-4">

                        {{-- Settings Icon --}}
                        <div class="w-11 h-11 rounded-xl
                                    bg-blue-50
                                    flex items-center justify-center">

                            <svg class="w-6 h-6 text-blue-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M10.325 4.317a1 1 0 011.35-.936l.63.252a1 1 0 001.39-.83l.08-.67a1 1 0 011.99 0l.08.67a1 1 0 001.39.83l.63-.252a1 1 0 011.35.936l-.04.678a1 1 0 001.04 1.04l.678-.04a1 1 0 01.936 1.35l-.252.63a1 1 0 00.83 1.39l.67.08a1 1 0 010 1.99l-.67.08a1 1 0 00-.83 1.39l.252.63a1 1 0 01-.936 1.35l-.678-.04a1 1 0 00-1.04 1.04l.04.678a1 1 0 01-1.35.936l-.63-.252a1 1 0 00-1.39.83l-.08.67a1 1 0 01-1.99 0l-.08-.67a1 1 0 00-1.39-.83l-.63.252a1 1 0 01-1.35-.936l.04-.678a1 1 0 00-1.04-1.04l-.678.04a1 1 0 01-.936-1.35l.252-.63a1 1 0 00-.83-1.39l-.67-.08a1 1 0 010-1.99l.67-.08a1 1 0 00.83-1.39l-.252-.63a1 1 0 01.936-1.35l.678.04a1 1 0 001.04-1.04l-.04-.678z"/>

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>

                            </svg>

                        </div>

                        <div>

                            <h1 class="text-xl sm:text-2xl font-extrabold
                                       text-[#102d5b]">

                                System Configuration

                            </h1>

                            <p class="text-sm text-gray-500 mt-0.5">

                                Manage institutional information and system settings.

                            </p>

                        </div>

                    </div>


                    {{-- Status --}}
                    <div class="inline-flex items-center gap-2
                                px-3 py-2
                                rounded-xl
                                bg-green-50
                                border border-green-100
                                w-fit">

                        <span class="w-2 h-2 rounded-full bg-green-500"></span>

                        <span class="text-xs font-bold text-green-700">

                            System Active

                        </span>

                    </div>

                </div>

                {{-- Blue Accent --}}
                <div class="h-1 bg-[#063b91]"></div>

            </div>


            {{-- =========================================================
                SUCCESS MESSAGE
            ========================================================== --}}
            @if (session('status'))

                <div class="mb-6 flex items-start gap-3
                            rounded-xl
                            bg-green-50
                            border border-green-200
                            px-4 py-3">

                    <div class="w-7 h-7 shrink-0 rounded-lg
                                bg-green-100
                                flex items-center justify-center">

                        <svg class="w-4 h-4 text-green-600"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M5 13l4 4L19 7"/>

                        </svg>

                    </div>

                    <div>

                        <p class="text-sm font-bold text-green-800">
                            Configuration Updated
                        </p>

                        <p class="text-xs text-green-700 mt-0.5">
                            {{ session('status') }}
                        </p>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                VALIDATION ERRORS
            ========================================================== --}}
            @if ($errors->any())

                <div class="mb-6 rounded-xl
                            bg-red-50
                            border border-red-200
                            px-4 py-3">

                    <div class="flex items-start gap-3">

                        <div class="w-7 h-7 shrink-0 rounded-lg
                                    bg-red-100
                                    flex items-center justify-center">

                            <svg class="w-4 h-4 text-red-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 8v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>

                            </svg>

                        </div>

                        <div>

                            <p class="text-sm font-bold text-red-800">
                                Please check the following:
                            </p>

                            <ul class="mt-1 text-xs text-red-700
                                       list-disc list-inside">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                MAIN CONFIGURATION FORM
            ========================================================== --}}
            <form method="POST"
                  action="{{ route('admin.settings.update') }}">

                @csrf
                @method('PUT')


                {{-- =====================================================
                    INSTITUTIONAL DETAILS
                ====================================================== --}}
                <div class="bg-white rounded-2xl
                            border border-gray-200
                            shadow-sm overflow-hidden">

                    {{-- Section Header --}}
                    <div class="px-6 py-5
                                border-b border-gray-100">

                        <div class="flex items-center gap-3">

                            <div class="w-10 h-10 rounded-xl
                                        bg-blue-50
                                        flex items-center justify-center">

                                <svg class="w-5 h-5 text-blue-700"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 7h2m-2 4h2m-2 4h2m4-8h2m-2 4h2m-2 4h2"/>

                                </svg>

                            </div>

                            <div>

                                <h2 class="text-base font-extrabold
                                           text-[#102d5b]">

                                    Institutional Details

                                </h2>

                                <p class="text-xs text-gray-400 mt-0.5">

                                    Information displayed throughout ISUFSTPASS.

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- Fields --}}
                    <div class="p-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">


                            {{-- Institution Name --}}
                            <div class="md:col-span-2">

                                <label class="block text-xs font-bold
                                              uppercase tracking-wide
                                              text-gray-500 mb-1.5">

                                    Institution Name

                                </label>

                                <input type="text"
                                       name="institution_name"
                                       required
                                       value="{{ $settings['institution_name'] }}"

                                       class="w-full rounded-xl
                                              border-gray-300
                                              bg-white
                                              px-4 py-2.5
                                              text-sm
                                              text-gray-800
                                              focus:border-blue-500
                                              focus:ring-blue-500">

                            </div>


                            {{-- Academic Term --}}
                            <div>

                                <label class="block text-xs font-bold
                                              uppercase tracking-wide
                                              text-gray-500 mb-1.5">

                                    Academic Term

                                </label>

                                <input type="text"
                                       name="academic_term"
                                       value="{{ $settings['academic_term'] }}"
                                       placeholder="e.g. 1st Semester, AY 2026-2027"

                                       class="w-full rounded-xl
                                              border-gray-300
                                              bg-white
                                              px-4 py-2.5
                                              text-sm
                                              text-gray-800
                                              focus:border-blue-500
                                              focus:ring-blue-500">

                            </div>


                            {{-- Institution Address --}}
                            <div>

                                <label class="block text-xs font-bold
                                              uppercase tracking-wide
                                              text-gray-500 mb-1.5">

                                    Institution Address

                                </label>

                                <input type="text"
                                       name="institution_address"
                                       value="{{ $settings['institution_address'] }}"

                                       class="w-full rounded-xl
                                              border-gray-300
                                              bg-white
                                              px-4 py-2.5
                                              text-sm
                                              text-gray-800
                                              focus:border-blue-500
                                              focus:ring-blue-500">

                            </div>


                            {{-- Support Email --}}
                            <div>

                                <label class="block text-xs font-bold
                                              uppercase tracking-wide
                                              text-gray-500 mb-1.5">

                                    Support Email

                                </label>

                                <input type="email"
                                       name="support_email"
                                       value="{{ $settings['support_email'] }}"

                                       class="w-full rounded-xl
                                              border-gray-300
                                              bg-white
                                              px-4 py-2.5
                                              text-sm
                                              text-gray-800
                                              focus:border-blue-500
                                              focus:ring-blue-500">

                            </div>


                            {{-- Support Phone --}}
                            <div>

                                <label class="block text-xs font-bold
                                              uppercase tracking-wide
                                              text-gray-500 mb-1.5">

                                    Support Phone

                                </label>

                                <input type="text"
                                       name="support_phone"
                                       value="{{ $settings['support_phone'] }}"

                                       class="w-full rounded-xl
                                              border-gray-300
                                              bg-white
                                              px-4 py-2.5
                                              text-sm
                                              text-gray-800
                                              focus:border-blue-500
                                              focus:ring-blue-500">

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        SAVE FOOTER
                    ================================================== --}}
                    <div class="px-6 py-4
                                bg-gray-50
                                border-t border-gray-100
                                flex flex-col sm:flex-row
                                sm:items-center
                                sm:justify-between gap-3">

                        <div>

                            <p class="text-xs font-semibold text-gray-600">
                                Configuration Settings
                            </p>

                            <p class="text-[11px] text-gray-400 mt-0.5">
                                Changes will apply across the system.
                            </p>

                        </div>


                        <button type="submit"
                                class="inline-flex items-center
                                       justify-center gap-2
                                       px-6 py-2.5
                                       bg-[#063b91]
                                       hover:bg-[#052f73]
                                       text-white
                                       text-sm font-bold
                                       rounded-xl
                                       shadow-sm
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

                            Save Configuration

                        </button>

                    </div>

                </div>

            </form>


            {{-- =========================================================
                INFORMATION NOTICE
            ========================================================== --}}
            <div class="mt-6 bg-blue-50
                        border border-blue-100
                        rounded-2xl px-5 py-4">

                <div class="flex items-start gap-3">

                    <div class="w-9 h-9 shrink-0
                                rounded-xl
                                bg-blue-700
                                flex items-center justify-center">

                        <svg class="w-5 h-5 text-white"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 15v2m0-8v4m0-9a9 9 0 100 18 9 9 0 000-18z"/>

                        </svg>

                    </div>

                    <div>

                        <p class="text-sm font-bold text-blue-900">
                            System Configuration
                        </p>

                        <p class="text-xs text-blue-700 mt-1">
                            Keep institutional information accurate because
                            these details may appear on system pages,
                            notifications, emails, and generated reports.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>