
<x-app-layout>
    @php
        $totalDocuments = $documents->count();
        $activeDocuments = $documents->where('is_active', true)->count();
        $inactiveDocuments = $documents->where('is_active', false)->count();
        $totalRequests = $documents->sum('requests_count');
    @endphp

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- PAGE HEADER --}}
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-900 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor"
                             stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            Document Fees & Services
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Manage document types, service fees, and availability.
                        </p>
                    </div>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    Registrar Services
                </div>
            </div>

            {{-- ALERTS --}}
            @if (session('status'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="mb-2 font-semibold">Please check the following:</p>
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- STATISTICS --}}
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Services</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $totalDocuments }}
                            </p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Active Services</p>
                            <p class="mt-2 text-3xl font-bold text-green-700">
                                {{ $activeDocuments }}
                            </p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-green-50 text-green-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Inactive Services</p>
                            <p class="mt-2 text-3xl font-bold text-slate-500">
                                {{ $inactiveDocuments }}
                            </p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M18.36 6.64a9 9 0 11-12.73 0m12.73 0L6.64 18.36"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Requests</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $totalRequests }}
                            </p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ADD DOCUMENT SERVICE --}}
            <section class="mb-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 bg-[#102d5b] px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 text-amber-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-white">Add Document Service</h2>
                            <p class="mt-1 text-sm text-blue-100">
                                Create a document type and assign its service fee.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('registrar.documents.store') }}" class="p-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-12">
                        <div class="xl:col-span-4">
                            <label for="new-name" class="mb-2 block text-sm font-semibold text-slate-700">
                                Document Name <span class="text-red-500">*</span>
                            </label>
                            <input id="new-name" type="text" name="name"
                                   value="{{ old('name') }}" required
                                   placeholder="e.g. Transcript of Records"
                                   class="h-11 w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                        </div>

                        <div class="xl:col-span-4">
                            <label for="new-description" class="mb-2 block text-sm font-semibold text-slate-700">
                                Description
                            </label>
                            <input id="new-description" type="text" name="description"
                                   value="{{ old('description') }}"
                                   placeholder="Short description (optional)"
                                   class="h-11 w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                        </div>

                        <div class="xl:col-span-2">
                            <label for="new-fee" class="mb-2 block text-sm font-semibold text-slate-700">
                                Fee (₱) <span class="text-red-500">*</span>
                            </label>
                            <input id="new-fee" type="number" name="fee" min="0" step="0.01"
                                   value="{{ old('fee') }}" required placeholder="0.00"
                                   class="h-11 w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                        </div>

                        <div class="flex items-end xl:col-span-2">
                            <button type="submit"
                                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-blue-800 px-4 text-sm font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                     stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/>
                                </svg>
                                Add Service
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 max-w-sm">
                        <label for="new-audience" class="mb-2 block text-sm font-semibold text-slate-700">
                            Available To
                        </label>
                        <select id="new-audience" name="audience"
                                class="h-11 w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            @foreach (\App\Models\Document::audiences() as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('audience', \App\Models\Document::AUDIENCE_BOTH) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </section>

            {{-- DOCUMENT SERVICES LIST --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Document Services</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Update document details, fees, audience, and availability.
                        </p>
                    </div>

                    <span class="inline-flex w-fit items-center gap-2 rounded-lg bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-800">
                        <span class="h-2 w-2 rounded-full bg-blue-700"></span>
                        {{ $totalDocuments }} {{ \Illuminate\Support\Str::plural('service', $totalDocuments) }}
                    </span>
                </div>

                @forelse ($documents as $document)
                    <div class="border-b border-slate-100 p-5 last:border-b-0 hover:bg-slate-50/70 sm:p-6"
                         x-data="{ editing: false }">
                        <form method="POST"
                              action="{{ route('registrar.documents.update', $document) }}"
                              data-confirm="Save the changes made to this document and its fee?"
                              data-confirm-title="Save document changes?"
                              data-confirm-ok="Yes, save"
                              data-confirm-icon="question">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-12">
                                {{-- DOCUMENT DETAILS --}}
                                <div class="flex min-w-0 items-start gap-3 xl:col-span-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-800">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                             stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        {{-- READ-ONLY --}}
                                        <div x-show="! editing">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Document Name
                                            </p>
                                            <p class="mt-1 truncate text-sm font-semibold text-slate-900">
                                                {{ $document->name }}
                                            </p>

                                            <p class="mt-3 text-xs font-medium text-slate-500">Description</p>
                                            <p class="mt-1 text-sm text-slate-600">
                                                {{ $document->description ?: 'No description' }}
                                            </p>
                                        </div>

                                        {{-- EDITABLE --}}
                                        <div x-show="editing" x-cloak>
                                            <label for="document-name-{{ $document->id }}"
                                                   class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Document Name
                                            </label>
                                            <input id="document-name-{{ $document->id }}"
                                                   type="text" name="name"
                                                   value="{{ $document->name }}" required
                                                   class="w-full rounded-lg border-slate-300 text-sm font-semibold text-slate-900 shadow-sm focus:border-blue-700 focus:ring-blue-700">

                                            <label for="document-description-{{ $document->id }}"
                                                   class="mb-1 mt-3 block text-xs font-medium text-slate-500">
                                                Description
                                            </label>
                                            <input id="document-description-{{ $document->id }}"
                                                   type="text" name="description"
                                                   value="{{ $document->description }}"
                                                   placeholder="Add a short description"
                                                   class="w-full rounded-lg border-slate-300 text-sm text-slate-600 shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                        </div>
                                    </div>
                                </div>

                                {{-- SERVICE FEE --}}
                                <div class="xl:col-span-2">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Service Fee
                                    </p>

                                    <p x-show="! editing" class="text-sm font-semibold text-slate-800">
                                        ₱{{ number_format((float) $document->fee, 2) }}
                                    </p>

                                    <div x-show="editing" x-cloak class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-500">₱</span>
                                        <input id="document-fee-{{ $document->id }}"
                                               type="number" name="fee" min="0" step="0.01"
                                               value="{{ $document->fee }}" required
                                               aria-label="Service fee"
                                               class="h-11 w-full rounded-lg border-slate-300 pl-8 pr-3 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                    </div>
                                </div>

                                {{-- AUDIENCE --}}
                                <div class="xl:col-span-2">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Available To
                                    </p>

                                    <p x-show="! editing" class="text-sm font-medium text-slate-700">
                                        {{ \App\Models\Document::audiences()[$document->audience] ?? $document->audience }}
                                    </p>

                                    <select x-show="editing" x-cloak
                                            id="document-audience-{{ $document->id }}"
                                            name="audience"
                                            aria-label="Available to"
                                            class="h-11 w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                        @foreach (\App\Models\Document::audiences() as $value => $label)
                                            <option value="{{ $value }}"
                                                @selected($document->audience === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- STATUS --}}
                                <div class="xl:col-span-2">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Service Status
                                    </p>

                                    <span x-show="! editing"
                                          class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold {{ $document->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                        <span class="h-2 w-2 rounded-full {{ $document->is_active ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                                        {{ $document->is_active ? 'Active' : 'Hidden' }}
                                    </span>

                                    <label x-show="editing" x-cloak
                                           class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1"
                                               @checked($document->is_active)
                                               class="rounded border-slate-300 text-blue-800 focus:ring-blue-700">
                                        <span class="text-sm font-medium text-slate-700">Active</span>
                                    </label>
                                </div>

                                {{-- ACTIONS --}}
                                <div class="flex flex-wrap items-center gap-2 xl:col-span-12">
                                    {{-- VIEW MODE: Remove, then Edit --}}
                                    <div x-show="! editing" class="flex flex-wrap items-center gap-2">
                                        <button type="submit"
                                                form="remove-document-{{ $document->id }}"
                                                class="inline-flex h-10 items-center justify-center rounded-lg border border-red-200 bg-white px-4 text-sm font-semibold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                            Remove
                                        </button>

                                        <button type="button"
                                                data-no-confirm
                                                @click="editing = true"
                                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                 stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M11 5h6m-2.5 3.5L7 16l-3 .5.5-3 7.5-7.5z"/>
                                            </svg>
                                            Edit
                                        </button>
                                    </div>

                                    {{-- EDIT MODE: Save, Cancel --}}
                                    <div x-show="editing" x-cloak class="flex flex-wrap items-center gap-2">
                                        <button type="submit"
                                                class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-800 px-5 text-sm font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            Save Changes
                                        </button>

                                        <button type="button"
                                                data-no-confirm
                                                @click="editing = false"
                                                class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- SEPARATE REMOVE FORM --}}
                        <form id="remove-document-{{ $document->id }}"
                              method="POST"
                              action="{{ route('registrar.documents.destroy', $document) }}"
                              data-confirm="{{ $document->requests_count > 0
                                    ? 'This service is used by '.$document->requests_count.' '.\Illuminate\Support\Str::plural('request', $document->requests_count).', so it will be removed from the student catalogue instead of deleted. Existing requests keep their record.'
                                    : 'This document service and its fee will be removed.' }}"
                              data-confirm-title="{{ $document->requests_count > 0 ? 'Remove this service from the catalogue?' : 'Remove this document service?' }}"
                              data-confirm-ok="Yes, remove it">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                @empty
                    {{-- EMPTY STATE --}}
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor"
                                 stroke-width="1.6" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-slate-800">
                            No Document Services Yet
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Use the form above to add your first document service.
                        </p>
                    </div>
                @endforelse
            </section>

            {{-- INFORMATION NOTICE --}}
            <div class="mt-6 flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                         stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 16v-4m0-4h.01M22 12a10 10 0 11-20 0 10 10 0 0120 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-950">
                        About Document Fee Management
                    </h3>
                    <p class="mt-1 text-sm leading-6 text-blue-800">
                        Changes to document names, descriptions, fees, audience,
                        and active status will be reflected in the student
                        document request process.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
