<x-app-layout>

    {{-- =========================================================
        CONSULTATION SERVICE — CREATE / EDIT FORM

        Shared by every office / department. The route prefix is derived
        from $office, which the controller already resolved server-side
        from the signed-in account.
    ========================================================== --}}
    @php($prefix = strtolower($office))

    <div class="min-h-screen bg-[#f6f8fc] py-8">

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- PAGE HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">

                <div class="flex items-center gap-4">

                    <div class="w-12 h-12 rounded-2xl
                                bg-blue-100
                                flex items-center justify-center shrink-0">

                        <svg class="w-6 h-6 text-blue-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>

                        </svg>

                    </div>

                    <div>

                        <h1 class="text-2xl sm:text-3xl
                                   font-extrabold
                                   text-[#102d5b]">

                            {{ $service ? 'Edit Service' : 'Add Consultation Service' }}

                        </h1>

                        <p class="mt-1 text-sm text-gray-500">

                            {{ $office }} ·
                            {{ $service ? 'Update the details of this service.' : 'Offer a new consultation service to students.' }}

                        </p>

                    </div>

                </div>

                <a href="{{ route($prefix . '.consultations') }}"
                   class="inline-flex items-center gap-2
                          w-fit
                          px-4 py-2.5
                          rounded-xl
                          border border-gray-200
                          bg-white
                          text-sm font-bold
                          text-gray-600
                          hover:bg-gray-50
                          transition">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>

                    Back to services

                </a>

            </div>


            {{-- VALIDATION ERRORS --}}
            @if ($errors->any())

                <div class="mb-6 rounded-xl
                            border border-red-200
                            bg-red-50
                            px-4 py-3.5">

                    <p class="text-xs font-bold text-red-700 mb-1.5">
                        Please fix the following:
                    </p>

                    <ul class="list-disc pl-5 space-y-0.5
                               text-[11px]
                               text-red-600">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- FORM CARD --}}
            <div class="bg-white rounded-2xl
                        border border-gray-200
                        shadow-sm
                        overflow-hidden">

                <form method="POST"
                      action="{{ $service
                          ? route($prefix . '.consultations.update', $service)
                          : route($prefix . '.consultations.store') }}">

                    @csrf

                    @if ($service)
                        @method('PUT')
                    @endif

                    <div class="p-6 sm:p-8 space-y-6">

                        {{-- NAME --}}
                        <div>

                            <label for="name"
                                   class="block text-sm font-bold text-gray-700 mb-2">

                                Service name
                                <span class="text-red-500">*</span>

                            </label>

                            <input id="name"
                                   name="name"
                                   type="text"
                                   required
                                   maxlength="150"
                                   value="{{ old('name', $service?->name) }}"
                                   placeholder="e.g. Academic Advising"
                                   class="w-full
                                          rounded-xl
                                          border-gray-300
                                          text-sm
                                          shadow-sm
                                          focus:border-blue-600
                                          focus:ring-blue-600">

                        </div>


                        {{-- DESCRIPTION --}}
                        <div>

                            <label for="description"
                                   class="block text-sm font-bold text-gray-700 mb-2">

                                Description

                                <span class="text-gray-400 font-normal">
                                    (optional)
                                </span>

                            </label>

                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="What does this service cover?"
                                      class="w-full
                                             rounded-xl
                                             border-gray-300
                                             text-sm
                                             shadow-sm
                                             focus:border-blue-600
                                             focus:ring-blue-600">{{ old('description', $service?->description) }}</textarea>

                            <p class="mt-1.5 text-[11px] text-gray-400">
                                Shown to students on the consultation catalogue.
                            </p>

                        </div>


                        {{-- ACTIVE --}}
                        <label class="flex items-start gap-3
                                      rounded-xl
                                      border border-gray-200
                                      bg-gray-50
                                      px-4 py-3.5
                                      cursor-pointer">

                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   @checked(old('is_active', $service?->is_active ?? true))
                                   class="mt-0.5
                                          rounded
                                          border-gray-300
                                          text-blue-600
                                          shadow-sm
                                          focus:ring-blue-500">

                            <span>

                                <span class="block text-sm font-bold text-gray-700">
                                    Active
                                </span>

                                <span class="block mt-0.5
                                              text-[11px]
                                              text-gray-500">

                                    Students can see and book this service.
                                    Uncheck to hide it without deleting it.

                                </span>

                            </span>

                        </label>

                    </div>


                    {{-- ACTIONS --}}
                    <div class="px-6 sm:px-8 py-5
                                bg-gray-50
                                border-t border-gray-100
                                flex flex-col-reverse sm:flex-row
                                sm:items-center
                                sm:justify-end
                                gap-3">

                        <a href="{{ route($prefix . '.consultations') }}"
                           class="inline-flex items-center justify-center
                                  px-5 py-2.5
                                  rounded-xl
                                  border border-gray-200
                                  bg-white
                                  text-sm font-bold
                                  text-gray-600
                                  hover:bg-gray-50
                                  transition">

                            Cancel

                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center
                                       px-5 py-2.5
                                       rounded-xl
                                       bg-blue-700
                                       text-white
                                       text-sm font-bold
                                       hover:bg-blue-800
                                       transition">

                            {{ $service ? 'Save changes' : 'Add service' }}

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>
