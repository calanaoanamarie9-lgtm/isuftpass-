<x-app-layout>

    <div class="min-h-screen bg-[#f5f7fb] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= PAGE HEADER ================= --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-700 mb-1">
                        Administration
                    </p>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#102d5b]">
                        Slot Availability
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Manage daily appointment capacity per office and time slot.
                    </p>
                </div>

                <div class="flex items-center gap-2">

                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                </div>

            </div>


            {{-- ================= ALERTS ================= --}}

            @if (session('status'))
                <div class="mb-5 flex items-start gap-3 rounded-xl
                            border border-green-200 bg-green-50 px-4 py-3">

                    <svg class="w-5 h-5 text-green-600 shrink-0 mt-0.5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>

                    <p class="text-sm font-medium text-green-700">
                        {{ session('status') }}
                    </p>

                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 flex items-start gap-3 rounded-xl
                            border border-red-200 bg-red-50 px-4 py-3">

                    <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>

                    <p class="text-sm font-medium text-red-700">
                        {{ session('error') }}
                    </p>

                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3">

                    <p class="text-sm font-bold text-red-700 mb-1">
                        Please check the following:
                    </p>

                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
            @endif


            {{-- ================= FILTERS ================= --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-7">

                <div class="px-6 py-5 border-b border-gray-100
                            flex flex-col sm:flex-row sm:items-center
                            sm:justify-between gap-4">

                    <form method="GET"
                          action="{{ route('admin.slots.index') }}"
                          class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">
                                Office
                            </label>

                            <select name="office_id"
                                    onchange="this.form.submit()"
                                    class="w-full sm:w-56 rounded-xl border-gray-300
                                           text-sm font-semibold
                                           focus:border-blue-600
                                           focus:ring-blue-600">

                                @foreach ($offices as $item)
                                    <option value="{{ $item->id }}"
                                            @selected($office && $office->id === $item->id)>
                                        {{ $item->name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">
                                Date
                            </label>

                            <input type="date"
                                   name="date"
                                   value="{{ $date }}"
                                   onchange="this.form.submit()"
                                   class="w-full sm:w-52 rounded-xl border-gray-300
                                          text-sm focus:border-blue-600
                                          focus:ring-blue-600">
                        </div>

                    </form>

                </div>

            </div>


            {{-- ================= SLOT GRID ================= --}}

            @if ($withOffice)

                {{-- Office default capacity --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-7">

                    <div class="px-6 py-5 border-b border-gray-100
                                flex flex-col sm:flex-row sm:items-center
                                sm:justify-between gap-3">

                        <div>

                            <h2 class="text-base font-extrabold text-[#102d5b]">
                                {{ $office->name }}
                            </h2>

                            <p class="text-xs text-gray-500 mt-1">
                                Default capacity applies when no per-slot rule exists.
                            </p>

                        </div>

                        <form method="POST"
                              action="{{ route('admin.slots.capacity', $office) }}"
                              class="flex flex-wrap items-end gap-2">

                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">
                                    Default Capacity
                                </label>

                                <input type="number"
                                       name="default_slot_capacity"
                                       min="1"
                                       max="999"
                                       value="{{ $office->default_slot_capacity }}"
                                       class="w-24 rounded-xl border-gray-300
                                              text-sm font-semibold
                                              focus:border-blue-600
                                              focus:ring-blue-600">
                            </div>

                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2.5
                                           bg-[#0b4ea2] hover:bg-[#083d80]
                                           text-white text-xs font-bold
                                           rounded-xl transition shadow-sm">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>

                                Save

                            </button>

                        </form>

                    </div>

                </div>


                {{-- Time slot rules --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-7">

                    <div class="px-6 py-5 border-b border-gray-100
                                flex flex-col sm:flex-row sm:items-center
                                sm:justify-between gap-3">

                        <div>

                            <h2 class="text-base font-extrabold text-[#102d5b]">
                                Per-Slot Capacity
                            </h2>

                            <p class="text-xs text-gray-500 mt-1">
                                Override the default for individual time slots,
                                for {{ $date }} or all dates.
                            </p>

                        </div>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-100">

                            <thead>

                                <tr class="bg-gray-50 text-left text-xs font-bold
                                           uppercase tracking-wider text-gray-500">

                                    <th class="px-6 py-4">Time Slot</th>
                                    <th class="px-4 py-4">Limit</th>
                                    <th class="px-4 py-4">Booked</th>
                                    <th class="px-4 py-4">Remaining</th>
                                    <th class="px-4 py-4">Status</th>
                                    <th class="px-4 py-4">Rule</th>
                                    <th class="px-6 py-4">Actions</th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                @foreach ($rows as $row)

                                    @php
                                        $check = $row['check'];
                                        $rule = $row['rule'];
                                        $defaultRule = $row['default_rule'];
                                        $ruleSource = $rule?->date
                                            ? 'On ' . $rule->date
                                            : ($rule ? 'All dates' : null);
                                    @endphp

                                    <tr class="text-sm">

                                        <td class="px-6 py-4 font-bold text-[#102d5b] whitespace-nowrap">
                                            {{ $row['time_slot'] }}
                                        </td>

                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center gap-1.5
                                                         px-2.5 py-1 rounded-lg bg-blue-50
                                                         text-blue-700 font-bold">
                                                {{ $check['limit'] }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 text-gray-600 font-semibold">
                                            {{ $check['booked'] }}
                                        </td>

                                        <td class="px-4 py-4 text-gray-600 font-semibold">
                                            {{ $check['remaining'] }}
                                        </td>

                                        <td class="px-4 py-4">

                                            @if ($check['status'] === \App\Models\SlotAvailability::STATUS_BLOCKED)

                                                <span class="inline-flex items-center gap-2 px-3 py-1.5
                                                             rounded-full bg-red-50 text-red-700
                                                             border border-red-200 text-xs font-bold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                    Blocked
                                                </span>

                                            @elseif ($check['status'] === \App\Models\SlotAvailability::STATUS_FULLY_BOOKED)

                                                <span class="inline-flex items-center gap-2 px-3 py-1.5
                                                             rounded-full bg-orange-50 text-orange-700
                                                             border border-orange-200 text-xs font-bold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                                    Fully Booked
                                                </span>

                                            @else

                                                <span class="inline-flex items-center gap-2 px-3 py-1.5
                                                             rounded-full bg-green-50 text-green-700
                                                             border border-green-200 text-xs font-bold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                    Available
                                                </span>

                                            @endif

                                        </td>

                                        <td class="px-4 py-4">

                                            @if ($rule)

                                                <span class="inline-flex items-center gap-2 px-3 py-1.5
                                                             rounded-full bg-indigo-50 text-indigo-700
                                                             border border-indigo-200 text-xs font-bold">
                                                    {{ $rule->max_capacity }} &middot; {{ $ruleSource }}
                                                </span>

                                            @else

                                                <span class="text-xs text-gray-400 font-semibold">
                                                    Default
                                                </span>

                                            @endif

                                        </td>

                                        <td class="px-6 py-4">

                                            <div class="flex flex-col gap-2">

                                                {{-- Set / update rule --}}
                                                <form method="POST"
                                                      action="{{ route('admin.slots.store') }}"
                                                      data-confirm="Save this slot rule for {{ $office->name }}?"
                                                      data-confirm-title="Save slot rule?"
                                                      data-confirm-ok="Yes, save">

                                                    @csrf

                                                    <input type="hidden" name="office_id" value="{{ $office->id }}">
                                                    <input type="hidden" name="date" value="{{ $date }}">
                                                    <input type="hidden" name="time_slot" value="{{ $row['time_slot'] }}">

                                                    <div class="flex items-center gap-2">

                                                        <input type="number"
                                                               name="max_capacity"
                                                               min="0"
                                                               max="999"
                                                               value="{{ $check['limit'] }}"
                                                               class="w-20 rounded-xl border-gray-300
                                                                      text-xs font-semibold
                                                                      focus:border-blue-600
                                                                      focus:ring-blue-600">

                                                        <select name="status"
                                                                class="w-32 rounded-xl border-gray-300
                                                                       text-xs font-semibold
                                                                       focus:border-blue-600
                                                                       focus:ring-blue-600">

                                                            <option value="{{ \App\Models\SlotAvailability::STATUS_AVAILABLE }}">Available</option>
                                                            <option value="{{ \App\Models\SlotAvailability::STATUS_BLOCKED }}">Blocked</option>

                                                        </select>

                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1.5 px-3 py-2
                                                                       bg-[#0b4ea2] hover:bg-[#083d80]
                                                                       text-white text-xs font-bold
                                                                       rounded-xl transition shadow-sm">
                                                            Save
                                                        </button>

                                                    </div>

                                                    <div class="mt-1.5 flex items-center gap-2">

                                                        <label class="inline-flex items-center gap-2 cursor-pointer">

                                                            <input type="hidden" name="apply_to_all_dates" value="0">

                                                            <input type="checkbox"
                                                                   name="apply_to_all_dates"
                                                                   value="1"
                                                                   @checked(! $rule || $rule->date === null)
                                                                   class="rounded border-gray-300 text-blue-700
                                                                          focus:ring-blue-600">

                                                            <span class="text-xs font-semibold text-gray-600">
                                                                All dates (default rule)
                                                            </span>

                                                        </label>

                                                    </div>

                                                </form>

                                                {{-- Remove date-specific override --}}
                                                @if ($rule && $rule->date !== null)

                                                    <form method="POST"
                                                          action="{{ route('admin.slots.destroy', $rule) }}"
                                                          data-confirm="Remove the override for {{ $row['time_slot'] }} and fall back to the default rule?"
                                                          data-confirm-title="Remove slot override?"
                                                          data-confirm-ok="Yes, remove">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1.5 px-3 py-2
                                                                       bg-red-50 hover:bg-red-100
                                                                       text-red-600 text-xs font-bold
                                                                       rounded-xl transition">
                                                            Remove Override
                                                        </button>

                                                    </form>

                                                @endif

                                            </div>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            @else

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-7">

                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto w-14 h-14 rounded-2xl bg-blue-50
                                    flex items-center justify-center">

                            <svg class="w-7 h-7 text-blue-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>

                        </div>

                        <h3 class="mt-4 text-sm font-bold text-gray-800">
                            No Office Configured
                        </h3>

                        <p class="mt-1 text-sm text-gray-400">
                            Add an office under Department Settings to manage slot capacity.
                        </p>

                    </div>

                </div>

            @endif


            {{-- ================= FOOTER NOTE ================= --}}
            <div class="mt-5 px-5 py-4 rounded-xl bg-blue-50 border border-blue-100">

                <div class="flex items-start gap-3">

                    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>

                    <div>

                        <p class="text-xs font-bold text-blue-900">
                            Slot Capacity
                        </p>

                        <p class="text-xs text-blue-700 mt-0.5">
                            Rules marked "all dates" apply every day. A date-specific
                            override takes precedence for that single day.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>