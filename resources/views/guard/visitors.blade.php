<x-app-layout>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER --}}
            <div class="mb-7 flex flex-col sm:flex-row sm:items-end
                        justify-between gap-5">

                <div>
                    <span class="inline-flex items-center gap-2 rounded-full
                                 bg-slate-900 border border-slate-700 px-3.5 py-1.5
                                 text-xs font-bold uppercase tracking-widest
                                 text-amber-300">
                        Campus Security · {{ $gateName }}
                    </span>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold text-gray-900">
                        Visitor Gate Passes
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Issue a pass to a walk-in guest, or find one already issued.
                    </p>
                </div>

                <a href="{{ route('guard.dashboard') }}"
                   class="shrink-0 rounded-xl bg-blue-700 px-5 py-2.5 text-sm
                          font-bold text-white hover:bg-blue-800 transition">
                    ← Back to scanner
                </a>

            </div>


            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100
                            px-5 py-4 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif


            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

                {{-- ISSUE FORM --}}
                <section class="lg:col-span-2 bg-white rounded-2xl
                                border border-gray-100 shadow-sm overflow-hidden">

                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="font-bold text-gray-900">Issue a new pass</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            The QR is generated automatically once the pass is saved.
                        </p>
                    </div>

                    <form method="POST"
                          action="{{ route('guard.visitors.store') }}"
                          enctype="multipart/form-data"
                          class="p-6 space-y-4">

                        @csrf

                        <div>
                            <label for="name" class="block text-xs font-bold
                                                      text-gray-500 uppercase
                                                      tracking-wide mb-1.5">
                                Visitor name
                            </label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name') }}" required
                                   class="w-full rounded-xl border border-gray-300
                                          bg-gray-50 px-4 py-2.5 text-sm
                                          focus:bg-white focus:border-blue-500
                                          focus:ring-blue-500">
                            @error('name')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="id_number" class="block text-xs font-bold
                                                              text-gray-500 uppercase
                                                              tracking-wide mb-1.5">
                                    ID presented
                                </label>
                                <input type="text" id="id_number" name="id_number"
                                       value="{{ old('id_number') }}"
                                       placeholder="e.g. driver's license no."
                                       class="w-full rounded-xl border border-gray-300
                                              bg-gray-50 px-4 py-2.5 text-sm
                                              focus:bg-white focus:border-blue-500
                                              focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="contact_number" class="block text-xs font-bold
                                                                   text-gray-500 uppercase
                                                                   tracking-wide mb-1.5">
                                    Contact number
                                </label>
                                <input type="text" id="contact_number"
                                       name="contact_number"
                                       value="{{ old('contact_number') }}"
                                       class="w-full rounded-xl border border-gray-300
                                              bg-gray-50 px-4 py-2.5 text-sm
                                              focus:bg-white focus:border-blue-500
                                              focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="destination_office" class="block text-xs
                                                                   font-bold text-gray-500
                                                                   uppercase tracking-wide
                                                                   mb-1.5">
                                Destination office
                            </label>
                            <select id="destination_office" name="destination_office"
                                    required
                                    class="w-full rounded-xl border border-gray-300
                                           bg-gray-50 px-4 py-2.5 text-sm
                                           focus:bg-white focus:border-blue-500
                                           focus:ring-blue-500">
                                @foreach ($destinations as $destinationValue => $destinationLabel)
                                    <option value="{{ $destinationValue }}"
                                            @selected(old('destination_office') === $destinationValue)>
                                        {{ $destinationLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('destination_office')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="purpose" class="block text-xs font-bold
                                                        text-gray-500 uppercase
                                                        tracking-wide mb-1.5">
                                Purpose of visit
                            </label>
                            <input type="text" id="purpose" name="purpose"
                                   value="{{ old('purpose') }}" required
                                   placeholder="e.g. Meeting with the Dean"
                                   class="w-full rounded-xl border border-gray-300
                                          bg-gray-50 px-4 py-2.5 text-sm
                                          focus:bg-white focus:border-blue-500
                                          focus:ring-blue-500">
                            @error('purpose')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="host_name" class="block text-xs font-bold
                                                              text-gray-500 uppercase
                                                              tracking-wide mb-1.5">
                                    Person to visit
                                </label>
                                <input type="text" id="host_name" name="host_name"
                                       value="{{ old('host_name') }}"
                                       class="w-full rounded-xl border border-gray-300
                                              bg-gray-50 px-4 py-2.5 text-sm
                                              focus:bg-white focus:border-blue-500
                                              focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="valid_until" class="block text-xs font-bold
                                                                text-gray-500 uppercase
                                                                tracking-wide mb-1.5">
                                    Valid until
                                </label>
                                <input type="date" id="valid_until" name="valid_until"
                                       value="{{ old('valid_until', \App\Support\CampusTime::today()) }}"
                                       class="w-full rounded-xl border border-gray-300
                                              bg-gray-50 px-4 py-2.5 text-sm
                                              focus:bg-white focus:border-blue-500
                                              focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="photo" class="block text-xs font-bold
                                                      text-gray-500 uppercase
                                                      tracking-wide mb-1.5">
                                Visitor photo (optional)
                            </label>
                            <input type="file" id="photo" name="photo"
                                   accept="image/*"
                                   class="w-full text-sm text-gray-600
                                          file:mr-3 file:rounded-lg file:border-0
                                          file:bg-blue-50 file:px-4 file:py-2
                                          file:text-sm file:font-bold
                                          file:text-blue-700">
                            @error('photo')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full rounded-xl bg-blue-700 px-6 py-3
                                       text-sm font-bold text-white
                                       hover:bg-blue-800 transition">
                            Issue Visitor Gate Pass
                        </button>

                    </form>

                </section>


                {{-- ISSUED PASSES --}}
                <section class="lg:col-span-3 bg-white rounded-2xl
                                border border-gray-100 shadow-sm overflow-hidden">

                    <div class="px-6 py-5 border-b border-gray-100">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="font-bold text-gray-900">Issued passes</h2>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Most recent first
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-slate-100
                                         px-3 py-1 text-xs font-bold text-slate-600">
                                {{ $visitors->count() }}
                            </span>
                        </div>

                        <form method="GET" action="{{ route('guard.visitors.index') }}"
                              class="mt-4">
                            <input type="search" name="q" value="{{ $search }}"
                                   placeholder="Search by name or reference code"
                                   autocomplete="off"
                                   class="w-full rounded-xl border border-gray-300
                                          bg-gray-50 px-4 py-2.5 text-sm
                                          focus:bg-white focus:border-blue-500
                                          focus:ring-blue-500">
                        </form>
                    </div>


                    <div class="divide-y divide-gray-50 max-h-[36rem] overflow-y-auto">

                        @forelse ($visitors as $visitor)
                            <div class="px-6 py-4 flex items-start justify-between gap-4">

                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">
                                        {{ $visitor->name }}
                                    </p>

                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $visitor->reference_code }} ·
                                        {{ $visitor->destination_office }} ·
                                        {{ $visitor->purpose }}
                                    </p>

                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Valid until
                                        {{ $visitor->valid_until?->format('M j, Y') ?? '—' }}
                                    </p>
                                </div>


                                <div class="shrink-0 flex flex-col items-end gap-2">

                                    @php
                                        $tone = $visitor->isValid()
                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                            : 'bg-red-50 text-red-700 ring-red-200';
                                    @endphp

                                    <span class="rounded-full px-2.5 py-1 text-[10px]
                                                 font-bold uppercase tracking-wide
                                                 ring-1 {{ $tone }}">
                                        {{ $visitor->statusLabel() }}
                                    </span>

                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('guard.visitors.show', $visitor) }}"
                                           class="rounded-lg bg-slate-100 px-3 py-1.5
                                                  text-xs font-bold text-slate-700
                                                  hover:bg-slate-200 transition">
                                            View pass
                                        </a>

                                        @if ($visitor->isValid())
                                            <form method="POST"
                                                  action="{{ route('guard.visitors.revoke', $visitor) }}"
                                                  onsubmit="return confirm('Revoke this pass?');">
                                                @csrf
                                                <button type="submit"
                                                        class="rounded-lg bg-red-50 px-3 py-1.5
                                                               text-xs font-bold text-red-700
                                                               hover:bg-red-100 transition">
                                                    Revoke
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                </div>

                            </div>
                        @empty
                            <div class="px-6 py-14 text-center">
                                <p class="text-sm font-semibold text-gray-600">
                                    No visitor passes yet
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Issued passes appear here and can be scanned at the gate.
                                </p>
                            </div>
                        @endforelse

                    </div>

                </section>

            </div>
        </div>
    </div>

</x-app-layout>
