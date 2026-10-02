<x-app-layout>

    <div class="min-h-screen bg-[#f6f8fc] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 mb-8">

                <div class="flex items-center gap-4">

                    {{-- ICON --}}
                    <div class="w-14 h-14 rounded-2xl
                                bg-blue-100
                                flex items-center justify-center">

                        <svg class="w-7 h-7 text-blue-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M9 14h6m-6-4h6m2 11H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                        </svg>

                    </div>

                    <div>

                        <h1 class="text-2xl sm:text-3xl
                                   font-extrabold
                                   text-[#102d5b]">

                            Document Fees & Services

                        </h1>

                        <p class="mt-1 text-sm text-gray-500">

                            Manage document pricing and services for student requests.

                        </p>

                    </div>

                </div>


                {{-- STATUS --}}
                <div class="inline-flex items-center gap-2
                            bg-white
                            border border-gray-200
                            rounded-full
                            px-4 py-2
                            shadow-sm
                            text-sm
                            font-medium
                            text-gray-600">

                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>

                    Registrar Services

                </div>

            </div>


            {{-- =========================================================
                ALERTS
            ========================================================== --}}

            @if (session('status'))

                <div class="mb-6 flex items-center gap-3
                            rounded-2xl
                            bg-green-50
                            border border-green-200
                            px-5 py-4
                            text-sm text-green-700">

                    <div class="w-9 h-9 rounded-xl
                                bg-green-100
                                flex items-center justify-center
                                shrink-0">

                        <svg class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M5 13l4 4L19 7"/>

                        </svg>

                    </div>

                    <span>{{ session('status') }}</span>

                </div>

            @endif


            @if (session('error'))

                <div class="mb-6 flex items-center gap-3
                            rounded-2xl
                            bg-red-50
                            border border-red-200
                            px-5 py-4
                            text-sm text-red-700">

                    <div class="w-9 h-9 rounded-xl
                                bg-red-100
                                flex items-center justify-center
                                shrink-0">

                        <svg class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>

                        </svg>

                    </div>

                    <span>{{ session('error') }}</span>

                </div>

            @endif


            @if ($errors->any())

                <div class="mb-6 rounded-2xl
                            bg-red-50
                            border border-red-200
                            px-5 py-4
                            text-sm text-red-700">

                    <div class="flex items-start gap-3">

                        <svg class="w-5 h-5 mt-0.5 shrink-0"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 9v4m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3h15.64a2 2 0 001.71-3l-7.82-13a2 2 0 00-3.42 0z"/>

                        </svg>

                        <ul class="list-disc list-inside space-y-1">

                            @foreach ($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                STATISTICS
            ========================================================== --}}
            @php

                $totalDocuments = $documents->count();

                $activeDocuments = $documents->where('is_active', true)->count();

                $inactiveDocuments = $documents->where('is_active', false)->count();

                $totalRequests = $documents->sum('requests_count');

            @endphp


            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">


                {{-- TOTAL SERVICES --}}
                <div class="bg-white
                            rounded-2xl
                            border border-gray-200
                            shadow-sm
                            p-5
                            relative overflow-hidden">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold
                                      uppercase tracking-wide
                                      text-gray-400">

                                Total Services

                            </p>

                            <p class="mt-2 text-3xl
                                      font-extrabold
                                      text-[#102d5b]">

                                {{ $totalDocuments }}

                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-blue-50
                                    text-blue-700
                                    flex items-center justify-center">

                            <svg class="w-6 h-6"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M9 14h6m-6-4h6m2 11H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                            </svg>

                        </div>

                    </div>

                    <div class="absolute bottom-0 left-0 right-0
                                h-1 bg-blue-600"></div>

                </div>


                {{-- ACTIVE --}}
                <div class="bg-white
                            rounded-2xl
                            border border-gray-200
                            shadow-sm
                            p-5
                            relative overflow-hidden">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold
                                      uppercase tracking-wide
                                      text-gray-400">

                                Active Services

                            </p>

                            <p class="mt-2 text-3xl
                                      font-extrabold
                                      text-green-600">

                                {{ $activeDocuments }}

                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-green-50
                                    text-green-600
                                    flex items-center justify-center">

                            <svg class="w-6 h-6"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M5 13l4 4L19 7"/>

                            </svg>

                        </div>

                    </div>

                    <div class="absolute bottom-0 left-0 right-0
                                h-1 bg-green-500"></div>

                </div>


                {{-- INACTIVE --}}
                <div class="bg-white
                            rounded-2xl
                            border border-gray-200
                            shadow-sm
                            p-5
                            relative overflow-hidden">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold
                                      uppercase tracking-wide
                                      text-gray-400">

                                Inactive

                            </p>

                            <p class="mt-2 text-3xl
                                      font-extrabold
                                      text-gray-500">

                                {{ $inactiveDocuments }}

                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-gray-100
                                    text-gray-500
                                    flex items-center justify-center">

                            <svg class="w-6 h-6"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M18.36 6.64a9 9 0 11-12.73 0m12.73 0L6.64 18.36"/>

                            </svg>

                        </div>

                    </div>

                    <div class="absolute bottom-0 left-0 right-0
                                h-1 bg-gray-400"></div>

                </div>


                {{-- REQUESTS --}}
                <div class="bg-white
                            rounded-2xl
                            border border-gray-200
                            shadow-sm
                            p-5
                            relative overflow-hidden">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold
                                      uppercase tracking-wide
                                      text-gray-400">

                                Total Requests

                            </p>

                            <p class="mt-2 text-3xl
                                      font-extrabold
                                      text-[#102d5b]">

                                {{ $totalRequests }}

                            </p>

                        </div>

                        <div class="w-12 h-12 rounded-xl
                                    bg-yellow-50
                                    text-yellow-600
                                    flex items-center justify-center">

                            <svg class="w-6 h-6"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>

                            </svg>

                        </div>

                    </div>

                    <div class="absolute bottom-0 left-0 right-0
                                h-1 bg-yellow-400"></div>

                </div>

            </div>


            {{-- =========================================================
                ADD DOCUMENT SERVICE
            ========================================================== --}}
            <div class="bg-white
                        rounded-2xl
                        border border-gray-200
                        shadow-sm
                        overflow-hidden
                        mb-7">

                {{-- HEADER --}}
                <div class="px-6 py-5
                            bg-gradient-to-r
                            from-[#102d5b]
                            to-blue-700">

                    <div class="flex items-center gap-4">

                        <div class="w-11 h-11 rounded-xl
                                    bg-white/10
                                    border border-white/20
                                    flex items-center justify-center">

                            <svg class="w-6 h-6 text-yellow-300"
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

                            <h2 class="text-lg font-bold text-white">

                                Add Document Service

                            </h2>

                            <p class="text-sm text-blue-100 mt-0.5">

                                Create a new document type and set its corresponding fee.

                            </p>

                        </div>

                    </div>

                </div>


                {{-- FORM --}}
                <form method="POST"
                      action="{{ route('registrar.documents.store') }}"
                      class="p-6">

                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5">


                        {{-- DOCUMENT NAME --}}
                        <div class="md:col-span-4">

                            <label class="block text-xs
                                          font-bold
                                          uppercase
                                          tracking-wide
                                          text-gray-500
                                          mb-2">

                                Document Name

                            </label>

                            <input type="text"
                                   name="name"
                                   required
                                   placeholder="e.g. Transcript of Records"
                                   class="w-full h-12
                                          rounded-xl
                                          border-gray-200
                                          bg-gray-50
                                          text-sm
                                          px-4
                                          focus:bg-white
                                          focus:border-blue-600
                                          focus:ring-blue-600">

                        </div>


                        {{-- DESCRIPTION --}}
                        <div class="md:col-span-4">

                            <label class="block text-xs
                                          font-bold
                                          uppercase
                                          tracking-wide
                                          text-gray-500
                                          mb-2">

                                Description

                            </label>

                            <input type="text"
                                   name="description"
                                   placeholder="Short description (optional)"
                                   class="w-full h-12
                                          rounded-xl
                                          border-gray-200
                                          bg-gray-50
                                          text-sm
                                          px-4
                                          focus:bg-white
                                          focus:border-blue-600
                                          focus:ring-blue-600">

                        </div>


                        {{-- FEE --}}
                        <div class="md:col-span-2">

                            <label class="block text-xs
                                          font-bold
                                          uppercase
                                          tracking-wide
                                          text-gray-500
                                          mb-2">

                                Fee

                            </label>

                            <div class="relative">

                                <span class="absolute left-4 top-1/2
                                             -translate-y-1/2
                                             text-gray-400
                                             font-bold">

                                    ₱

                                </span>

                                <input type="number"
                                       name="fee"
                                       min="0"
                                       step="0.01"
                                       required
                                       placeholder="0.00"
                                       class="w-full h-12
                                              rounded-xl
                                              border-gray-200
                                              bg-gray-50
                                              text-sm
                                              pl-9 pr-4
                                              focus:bg-white
                                              focus:border-blue-600
                                              focus:ring-blue-600">

                            </div>

                        </div>


                        {{-- BUTTON --}}
                        <div class="md:col-span-2 flex items-end">

                            <button type="submit"
                                    class="w-full h-12
                                           rounded-xl
                                           bg-blue-700
                                           hover:bg-blue-800
                                           text-white
                                           text-sm
                                           font-bold
                                           shadow-sm
                                           inline-flex
                                           items-center
                                           justify-center
                                           gap-2
                                           transition">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 4v16m8-8H4"/>

                                </svg>

                                Add Service

                            </button>

                        </div>

                    </div>

                </form>

            </div>


            {{-- =========================================================
                DOCUMENT SERVICES
            ========================================================== --}}
            <div class="bg-white
                        rounded-2xl
                        border border-gray-200
                        shadow-sm
                        overflow-hidden">


                {{-- LIST HEADER --}}
                <div class="px-6 py-5
                            border-b border-gray-100
                            flex flex-col sm:flex-row
                            sm:items-center
                            sm:justify-between
                            gap-3">

                    <div>

                        <h2 class="text-lg
                                   font-extrabold
                                   text-[#102d5b]">

                            Document Services

                        </h2>

                        <p class="text-sm
                                  text-gray-400
                                  mt-1">

                            Manage available documents, fees, and service status.

                        </p>

                    </div>


                    <div class="inline-flex
                                items-center
                                gap-2
                                px-3 py-1.5
                                rounded-full
                                bg-blue-50
                                text-blue-700
                                text-xs
                                font-bold">

                        <span class="w-2 h-2
                                     rounded-full
                                     bg-blue-600">
                        </span>

                        {{ $totalDocuments }} Services

                    </div>

                </div>


                {{-- =====================================================
                    DOCUMENT LIST
                ====================================================== --}}

                @forelse ($documents as $document)

                    <div class="px-6 py-5
                                border-b border-gray-100
                                last:border-0
                                hover:bg-blue-50/20
                                transition">


                        <form method="POST"
                              action="{{ route('registrar.documents.update', $document) }}"
                              data-confirm="Save the changes made to this document and its fee?"
                              data-confirm-title="Save document changes?"
                              data-confirm-ok="Yes, save"
                              data-confirm-icon="question">

                            @csrf

                            @method('PUT')


                            <div class="grid grid-cols-1
                                        lg:grid-cols-[1fr_170px_auto]
                                        gap-5
                                        items-center">


                                {{-- DOCUMENT INFO --}}
                                <div class="flex items-start gap-4 min-w-0">

                                    {{-- ICON --}}
                                    <div class="w-11 h-11
                                                rounded-xl
                                                bg-blue-50
                                                text-blue-700
                                                flex items-center
                                                justify-center
                                                shrink-0">

                                        <svg class="w-5 h-5"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="M9 14h6m-6-4h6m2 11H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                                        </svg>

                                    </div>


                                    <div class="flex-1 min-w-0">

                                        <input type="text"
                                               name="name"
                                               value="{{ $document->name }}"
                                               required
                                               class="w-full
                                                      rounded-xl
                                                      border-gray-200
                                                      bg-gray-50
                                                      focus:bg-white
                                                      focus:border-blue-600
                                                      focus:ring-blue-600
                                                      text-sm
                                                      font-bold
                                                      text-[#102d5b]">

                                        <input type="text"
                                               name="description"
                                               value="{{ $document->description }}"
                                               placeholder="Description"
                                               class="mt-2 w-full
                                                      rounded-xl
                                                      border-gray-200
                                                      bg-gray-50
                                                      focus:bg-white
                                                      focus:border-blue-600
                                                      focus:ring-blue-600
                                                      text-xs
                                                      text-gray-500">

                                    </div>

                                </div>


                                {{-- FEE --}}
                                <div>

                                    <label class="block text-[10px]
                                                  font-bold
                                                  uppercase
                                                  tracking-wider
                                                  text-gray-400
                                                  mb-2">

                                        Service Fee

                                    </label>

                                    <div class="relative">

                                        <span class="absolute left-3
                                                     top-1/2
                                                     -translate-y-1/2
                                                     text-gray-400
                                                     font-bold">

                                            ₱

                                        </span>

                                        <input type="number"
                                               name="fee"
                                               min="0"
                                               step="0.01"
                                               value="{{ $document->fee }}"
                                               required
                                               class="w-full
                                                      h-11
                                                      rounded-xl
                                                      border-gray-200
                                                      bg-gray-50
                                                      pl-8
                                                      pr-3
                                                      text-sm
                                                      font-bold
                                                      text-gray-800
                                                      focus:bg-white
                                                      focus:border-blue-600
                                                      focus:ring-blue-600">

                                    </div>

                                </div>


                                {{-- CONTROLS --}}
                                <div class="flex flex-wrap
                                            lg:flex-nowrap
                                            items-center
                                            gap-2">


                                    {{-- ACTIVE --}}
                                    <label class="inline-flex
                                                  items-center
                                                  gap-2
                                                  px-3 py-2.5
                                                  rounded-xl
                                                  bg-gray-50
                                                  border border-gray-200
                                                  cursor-pointer
                                                  whitespace-nowrap">

                                        <input type="hidden"
                                               name="is_active"
                                               value="0">

                                        <input type="checkbox"
                                               name="is_active"
                                               value="1"
                                               @checked($document->is_active)
                                               class="rounded
                                                      border-gray-300
                                                      text-blue-700
                                                      focus:ring-blue-500">

                                        <span class="text-xs
                                                     font-semibold
                                                     text-gray-600">

                                            Active

                                        </span>

                                    </label>


                                    {{-- SAVE --}}
                                    <button type="submit"
                                            class="px-4 py-2.5
                                                   rounded-xl
                                                   bg-blue-700
                                                   hover:bg-blue-800
                                                   text-white
                                                   text-xs
                                                   font-bold
                                                   transition">

                                        Save

                                    </button>


                                    {{-- DELETE / USED --}}
                                    @if ($document->requests_count === 0)

                                        <button type="submit"
                                                form="delete-document-{{ $document->id }}"
                                                class="px-4 py-2.5
                                                       rounded-xl
                                                       bg-red-50
                                                       text-red-600
                                                       border border-red-100
                                                       hover:bg-red-100
                                                       text-xs
                                                       font-bold
                                                       transition">

                                            Delete

                                        </button>

                                    @else

                                        <span class="px-4 py-2.5
                                                     rounded-xl
                                                     bg-gray-50
                                                     border border-gray-100
                                                     text-gray-400
                                                     text-xs
                                                     font-semibold
                                                     whitespace-nowrap"
                                              title="Attached to {{ $document->requests_count }} request(s) — cannot be deleted">

                                            Used {{ $document->requests_count }}×

                                        </span>

                                    @endif

                                </div>

                            </div>

                        </form>


                        {{-- DELETE FORM --}}
                        <form id="delete-document-{{ $document->id }}"
                              method="POST"
                              action="{{ route('registrar.documents.destroy', $document) }}"
                              data-confirm="This document service and its fee will be removed."
                              data-confirm-title="Delete this document service?"
                              data-confirm-ok="Yes, delete it">

                            @csrf

                            @method('DELETE')

                        </form>

                    </div>

                @empty

                    {{-- EMPTY STATE --}}
                    <div class="px-6 py-16 text-center">

                        <div class="w-16 h-16
                                    mx-auto
                                    rounded-2xl
                                    bg-blue-50
                                    text-blue-600
                                    flex items-center
                                    justify-center">

                            <svg class="w-8 h-8"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M9 14h6m-6-4h6m2 11H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                            </svg>

                        </div>

                        <h3 class="mt-5
                                   font-bold
                                   text-gray-700">

                            No Document Services Yet

                        </h3>

                        <p class="mt-1
                                  text-sm
                                  text-gray-400">

                            Add your first document service using the form above.

                        </p>

                    </div>

                @endforelse


            </div>


            {{-- =========================================================
                INFORMATION NOTICE
            ========================================================== --}}
            <div class="mt-6
                        rounded-2xl
                        border border-blue-100
                        bg-blue-50
                        px-5 py-4">

                <div class="flex items-start gap-3">

                    <div class="w-9 h-9
                                rounded-full
                                bg-blue-600
                                text-white
                                flex items-center
                                justify-center
                                shrink-0
                                font-bold">

                        i

                    </div>

                    <div>

                        <p class="text-sm
                                  font-bold
                                  text-blue-900">

                            Document Fee Management

                        </p>

                        <p class="mt-1
                                  text-xs
                                  leading-5
                                  text-blue-700">

                            Changes to document names, descriptions, fees,
                            and active status will be reflected in the
                            student document request process.

                        </p>

                    </div>

                </div>

            </div>


        </div>

    </div>

</x-app-layout>