<x-app-layout>

    <div class="min-h-screen bg-[#f5f7fb] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="mb-7">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600 mb-1">
                            Administration
                        </p>

                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#102d5b]">
                            Department Settings
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Configure university offices and their operational information.
                        </p>
                    </div>

                    {{-- Office count --}}
                    <div class="flex items-center gap-3 bg-white border border-gray-200
                                rounded-xl px-4 py-3 shadow-sm">

                        <div class="w-9 h-9 rounded-lg bg-blue-50
                                    flex items-center justify-center">

                            <svg class="w-5 h-5 text-blue-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 7h2m-2 4h2m2-4h2m-2 4h2M9 21v-4h6v4"/>
                            </svg>

                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                                Total Offices
                            </p>

                            <p class="text-lg font-black text-[#102d5b]">
                                {{ $offices->count() }}
                            </p>
                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                SUCCESS / ERROR MESSAGES
            ========================================================== --}}
            @if (session('status'))

                <div class="mb-5 flex items-start gap-3 rounded-xl
                            bg-green-50 border border-green-200
                            px-4 py-3">

                    <div class="w-6 h-6 rounded-full bg-green-100
                                flex items-center justify-center shrink-0">

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

                    <p class="text-sm font-medium text-green-700">
                        {{ session('status') }}
                    </p>

                </div>

            @endif


            @if (session('error'))

                <div class="mb-5 flex items-start gap-3 rounded-xl
                            bg-red-50 border border-red-200
                            px-4 py-3">

                    <div class="w-6 h-6 rounded-full bg-red-100
                                flex items-center justify-center shrink-0">

                        <svg class="w-4 h-4 text-red-600"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>

                    </div>

                    <p class="text-sm font-medium text-red-700">
                        {{ session('error') }}
                    </p>

                </div>

            @endif


            @if ($errors->any())

                <div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3">

                    <p class="text-sm font-bold text-red-700 mb-2">
                        Please check the following:
                    </p>

                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- =========================================================
                ADD DEPARTMENT
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200
                        shadow-sm overflow-hidden mb-7">

                {{-- Section Header --}}
                <div class="px-6 py-5 border-b border-gray-100">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 rounded-xl bg-blue-700
                                    flex items-center justify-center">

                            <svg class="w-5 h-5 text-white"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>

                        </div>

                        <div>

                            <h2 class="text-base font-extrabold text-[#102d5b]">
                                Add New Department
                            </h2>

                            <p class="text-xs text-gray-400 mt-0.5">
                                Add an office and configure its basic information.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Form --}}
                <form method="POST"
                      action="{{ route('admin.offices.store') }}"
                      class="p-6">

                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

                        {{-- Department Name --}}
                        <div class="lg:col-span-2">

                            <label class="block text-xs font-bold uppercase
                                          tracking-wide text-gray-500 mb-2">
                                Department Name
                            </label>

                            <input type="text"
                                   name="name"
                                   required
                                   placeholder="e.g. Office of the Registrar"
                                   class="w-full rounded-xl border-gray-300
                                          focus:border-blue-600 focus:ring-blue-600
                                          text-sm">

                        </div>


                        {{-- Location --}}
                        <div>

                            <label class="block text-xs font-bold uppercase
                                          tracking-wide text-gray-500 mb-2">
                                Location
                            </label>

                            <input type="text"
                                   name="location"
                                   placeholder="Main Building"
                                   class="w-full rounded-xl border-gray-300
                                          focus:border-blue-600 focus:ring-blue-600
                                          text-sm">

                        </div>


                        {{-- Contact --}}
                        <div>

                            <label class="block text-xs font-bold uppercase
                                          tracking-wide text-gray-500 mb-2">
                                Contact
                            </label>

                            <input type="text"
                                   name="contact"
                                   placeholder="Contact number"
                                   class="w-full rounded-xl border-gray-300
                                          focus:border-blue-600 focus:ring-blue-600
                                          text-sm">

                        </div>


                        {{-- Office Hours --}}
                        <div class="lg:col-span-2">

                            <label class="block text-xs font-bold uppercase
                                          tracking-wide text-gray-500 mb-2">
                                Office Hours
                            </label>

                            <input type="text"
                                   name="hours"
                                   placeholder="Monday - Friday, 8:00 AM - 5:00 PM"
                                   class="w-full rounded-xl border-gray-300
                                          focus:border-blue-600 focus:ring-blue-600
                                          text-sm">

                        </div>


                        {{-- Description --}}
                        <div class="lg:col-span-2">

                            <label class="block text-xs font-bold uppercase
                                          tracking-wide text-gray-500 mb-2">
                                Description
                            </label>

                            <input type="text"
                                   name="description"
                                   placeholder="Short description of the department"
                                   class="w-full rounded-xl border-gray-300
                                          focus:border-blue-600 focus:ring-blue-600
                                          text-sm">

                        </div>

                    </div>


                    {{-- Submit --}}
                    <div class="mt-6 pt-5 border-t border-gray-100
                                flex justify-end">

                        <button type="submit"
                                class="inline-flex items-center justify-center
                                       gap-2 px-6 py-2.5 rounded-xl
                                       bg-[#063b91] hover:bg-[#052f75]
                                       text-white text-sm font-bold
                                       shadow-sm transition">

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>

                            Add Department

                        </button>

                    </div>

                </form>

            </div>


            {{-- =========================================================
                DEPARTMENT LIST
            ========================================================== --}}
            <div>

                <div class="flex items-center justify-between mb-4">

                    <div>
                        <h2 class="text-lg font-extrabold text-[#102d5b]">
                            University Offices
                        </h2>

                        <p class="text-xs text-gray-400 mt-0.5">
                            Manage existing departments and their operational details.
                        </p>
                    </div>

                </div>


                @forelse ($offices as $office)

                    <div class="bg-white rounded-2xl border border-gray-200
                                shadow-sm overflow-hidden mb-4">

                        {{-- Office Top --}}
                        <div class="px-6 py-5 border-b border-gray-100">

                            <div class="flex flex-col lg:flex-row
                                        lg:items-center lg:justify-between gap-4">

                                <div class="flex items-center gap-4">

                                    <div class="w-11 h-11 rounded-xl
                                                bg-blue-50
                                                flex items-center justify-center">

                                        <svg class="w-5 h-5 text-blue-700"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 7h2m-2 4h2m2-4h2m-2 4h2"/>
                                        </svg>

                                    </div>

                                    <div>

                                        <h3 class="text-base font-extrabold text-gray-900">
                                            {{ $office->name }}
                                        </h3>

                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ $office->description ?: 'No description provided.' }}
                                        </p>

                                    </div>

                                </div>


                                {{-- Status --}}
                                @if ($office->is_active)

                                    <span class="inline-flex items-center gap-2
                                                 self-start lg:self-auto
                                                 px-3 py-1.5 rounded-full
                                                 bg-green-50 text-green-700
                                                 border border-green-200
                                                 text-xs font-bold">

                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>

                                        Active

                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-2
                                                 self-start lg:self-auto
                                                 px-3 py-1.5 rounded-full
                                                 bg-gray-100 text-gray-500
                                                 border border-gray-200
                                                 text-xs font-bold">

                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>

                                        Inactive

                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- Edit Form --}}
                        <form method="POST"
                              action="{{ route('admin.offices.update', $office) }}"
                              data-confirm="Save the changes made to this department?"
                              data-confirm-title="Save department changes?"
                              data-confirm-ok="Yes, save"
                              data-confirm-icon="question">

                            @csrf
                            @method('PUT')

                            <div class="p-6">

                                <div class="grid grid-cols-1 sm:grid-cols-2
                                            lg:grid-cols-4 gap-5">

                                    {{-- Name --}}
                                    <div>

                                        <label class="block text-[10px] font-bold
                                                      uppercase tracking-wide
                                                      text-gray-400 mb-1.5">
                                            Department Name
                                        </label>

                                        <input type="text"
                                               name="name"
                                               value="{{ $office->name }}"
                                               required
                                               class="w-full rounded-xl border-gray-300
                                                      focus:border-blue-600
                                                      focus:ring-blue-600
                                                      text-sm font-semibold">

                                    </div>


                                    {{-- Location --}}
                                    <div>

                                        <label class="block text-[10px] font-bold
                                                      uppercase tracking-wide
                                                      text-gray-400 mb-1.5">
                                            Location
                                        </label>

                                        <input type="text"
                                               name="location"
                                               value="{{ $office->location }}"
                                               placeholder="Location"
                                               class="w-full rounded-xl border-gray-300
                                                      focus:border-blue-600
                                                      focus:ring-blue-600
                                                      text-sm">

                                    </div>


                                    {{-- Contact --}}
                                    <div>

                                        <label class="block text-[10px] font-bold
                                                      uppercase tracking-wide
                                                      text-gray-400 mb-1.5">
                                            Contact
                                        </label>

                                        <input type="text"
                                               name="contact"
                                               value="{{ $office->contact }}"
                                               placeholder="Contact"
                                               class="w-full rounded-xl border-gray-300
                                                      focus:border-blue-600
                                                      focus:ring-blue-600
                                                      text-sm">

                                    </div>


                                    {{-- Hours --}}
                                    <div>

                                        <label class="block text-[10px] font-bold
                                                      uppercase tracking-wide
                                                      text-gray-400 mb-1.5">
                                            Office Hours
                                        </label>

                                        <input type="text"
                                               name="hours"
                                               value="{{ $office->hours }}"
                                               placeholder="Office hours"
                                               class="w-full rounded-xl border-gray-300
                                                      focus:border-blue-600
                                                      focus:ring-blue-600
                                                      text-sm">

                                    </div>


                                    {{-- Description --}}
                                    <div class="sm:col-span-2 lg:col-span-4">

                                        <label class="block text-[10px] font-bold
                                                      uppercase tracking-wide
                                                      text-gray-400 mb-1.5">
                                            Description
                                        </label>

                                        <input type="text"
                                               name="description"
                                               value="{{ $office->description }}"
                                               placeholder="Description"
                                               class="w-full rounded-xl border-gray-300
                                                      focus:border-blue-600
                                                      focus:ring-blue-600
                                                      text-sm">

                                    </div>

                                </div>


                                {{-- Actions --}}
                                <div class="mt-6 pt-5 border-t border-gray-100
                                            flex flex-wrap items-center
                                            justify-between gap-4">

                                    {{-- Active Toggle --}}
                                    <label class="inline-flex items-center gap-2
                                                  cursor-pointer">

                                        <input type="hidden"
                                               name="is_active"
                                               value="0">

                                        <input type="checkbox"
                                               name="is_active"
                                               value="1"
                                               @checked($office->is_active)
                                               class="rounded border-gray-300
                                                      text-blue-700
                                                      focus:ring-blue-500">

                                        <span class="text-sm font-semibold text-gray-600">
                                            Department is active
                                        </span>

                                    </label>


                                    {{-- Buttons --}}
                                    <div class="flex items-center gap-2">

                                        <button type="submit"
                                                class="inline-flex items-center gap-2
                                                       px-5 py-2.5 rounded-xl
                                                       bg-[#063b91]
                                                       hover:bg-[#052f75]
                                                       text-white text-xs font-bold
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

                                            Save Changes

                                        </button>


                                        <button type="submit"
                                                form="delete-office-{{ $office->id }}"
                                                class="inline-flex items-center gap-2
                                                       px-5 py-2.5 rounded-xl
                                                       bg-red-50
                                                       hover:bg-red-100
                                                       text-red-600 text-xs font-bold
                                                       transition">

                                            <svg class="w-4 h-4"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"/>
                                            </svg>

                                            Delete

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </form>

                    </div>


                    {{-- Delete Form --}}
                    <form id="delete-office-{{ $office->id }}"
                          method="POST"
                          action="{{ route('admin.offices.destroy', $office) }}"
                          data-confirm="This department will be removed from the system."
                          data-confirm-title="Delete this department?"
                          data-confirm-ok="Yes, delete it">

                        @csrf
                        @method('DELETE')

                    </form>

                @empty

                    {{-- Empty State --}}
                    <div class="bg-white rounded-2xl border border-gray-200
                                shadow-sm p-12 text-center">

                        <div class="mx-auto w-14 h-14 rounded-2xl
                                    bg-blue-50 flex items-center justify-center">

                            <svg class="w-7 h-7 text-blue-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16"/>
                            </svg>

                        </div>

                        <h3 class="mt-4 text-base font-bold text-gray-800">
                            No Departments Configured
                        </h3>

                        <p class="mt-1 text-sm text-gray-400">
                            Add offices such as Registrar, Cashier, or SSC.
                        </p>

                    </div>

                @endforelse

            </div>

        </div>

    </div>

</x-app-layout>