<x-app-layout>

    <div class="min-h-screen bg-[#f5f7fb] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6">

                <div class="px-6 py-5 flex flex-col sm:flex-row
                            sm:items-center sm:justify-between gap-4">

                    <div class="flex items-center gap-4">

                        {{-- Icon --}}
                        <div class="w-11 h-11 rounded-xl
                                    bg-amber-50 flex items-center justify-center">

                            <svg class="w-6 h-6 text-amber-500"
                                 fill="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"/>

                            </svg>

                        </div>

                        <div>

                            <h1 class="text-xl sm:text-2xl font-extrabold
                                       text-[#102d5b]">

                                Feedback Management

                            </h1>

                            <p class="text-sm text-gray-500 mt-0.5">

                                Ratings and comments submitted after each finished service.

                            </p>

                        </div>

                    </div>


                    {{-- Recorded count --}}
                    <div class="inline-flex items-center gap-2
                                px-3 py-2 rounded-xl
                                bg-amber-50 border border-amber-100
                                w-fit">

                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>

                        <span class="text-xs font-bold text-amber-700">

                            {{ $total }} {{ $total === 1 ? 'rating' : 'ratings' }} recorded

                        </span>

                    </div>

                </div>

                {{-- Blue Line --}}
                <div class="h-1 bg-[#063b91]"></div>

            </div>


            {{-- =========================================================
                SUMMARY — the numbers an admin reads first
            ========================================================== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">


                {{-- Total ratings --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm p-5">

                    <p class="text-[10px] uppercase tracking-widest
                              text-gray-400 font-bold">

                        Total Ratings

                    </p>

                    <p class="mt-2 text-3xl font-extrabold text-[#102d5b]">

                        {{ $total }}

                    </p>

                    <p class="text-xs text-gray-500 mt-1">

                        {{ $byType[\App\Models\Feedback::TYPE_APPOINTMENT]['total'] ?? 0 }} appointments &middot;
                        {{ $byType[\App\Models\Feedback::TYPE_DOCUMENT_REQUEST]['total'] ?? 0 }} document requests

                    </p>

                </div>


                {{-- Overall average --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm p-5">

                    <p class="text-[10px] uppercase tracking-widest
                              text-gray-400 font-bold">

                        Average Rating

                    </p>

                    <div class="mt-2 flex items-baseline gap-2">

                        <span class="text-3xl font-extrabold text-[#102d5b]">

                            {{ $average !== null ? number_format($average, 1) : '—' }}

                        </span>

                        <span class="text-sm font-bold text-gray-400">/ 5</span>

                    </div>

                    <div class="flex gap-0.5 mt-1.5">

                        @for ($star = 1; $star <= 5; $star++)

                            <svg class="w-4 h-4
                                        {{ $average !== null && $star <= round($average)
                                            ? 'text-yellow-400'
                                            : 'text-gray-200' }}"
                                 fill="currentColor"
                                 viewBox="0 0 24 24">

                                <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"/>

                            </svg>

                        @endfor

                    </div>

                    <p class="text-xs text-gray-500 mt-1">

                        {{ $average !== null
                            ? 'across all submitted ratings'
                            : 'no ratings yet' }}

                    </p>

                </div>


                {{-- Per-transaction averages: which service needs work --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm p-5">

                    <p class="text-[10px] uppercase tracking-widest
                              text-gray-400 font-bold">

                        By Transaction

                    </p>

                    @foreach ($types as $typeKey => $typeLabel)

                        <div class="mt-2 flex items-center justify-between gap-3">

                            <span class="text-sm font-semibold text-gray-700">

                                {{ $typeLabel }}

                            </span>

                            @if (isset($byType[$typeKey]))

                                <span class="text-sm font-bold text-[#102d5b]">

                                    <span class="text-yellow-500">&#9733;</span>
                                    {{ number_format($byType[$typeKey]['average'], 1) }}

                                    <span class="text-xs font-medium text-gray-400">

                                        ({{ $byType[$typeKey]['total'] }})

                                    </span>

                                </span>

                            @else

                                <span class="text-xs font-medium text-gray-400">

                                    no ratings

                                </span>

                            @endif

                        </div>

                    @endforeach

                </div>


            </div>


            {{-- =========================================================
                RATING DISTRIBUTION — where the score is being dragged down
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200
                        shadow-sm mb-6">

                <div class="px-5 py-4 border-b border-gray-100">

                    <div class="flex items-center gap-2">

                        <svg class="w-5 h-5 text-amber-500"
                             fill="currentColor"
                             viewBox="0 0 24 24">

                            <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"/>

                        </svg>

                        <h2 class="text-sm font-bold text-gray-800">

                            Rating Distribution

                        </h2>

                    </div>

                </div>

                <div class="p-5 space-y-3">

                    @for ($stars = 5; $stars >= 1; $stars--)

                        @php
                            $count = $distribution[$stars] ?? 0;
                            $percent = $total > 0 ? (int) round($count * 100 / $total) : 0;
                        @endphp

                        <div class="flex items-center gap-3">

                            <span class="w-12 text-xs font-bold text-gray-600">

                                {{ $stars }} &#9733;

                            </span>

                            <div class="flex-1 h-2.5 bg-gray-100
                                        rounded-full overflow-hidden">

                                <div class="h-full rounded-full
                                            {{ $stars >= 4
                                                ? 'bg-yellow-400'
                                                : ($stars === 3 ? 'bg-amber-400' : 'bg-red-400') }}"
                                     style="width: {{ $percent }}%"></div>

                            </div>

                            <span class="w-24 text-right text-xs
                                         font-semibold text-gray-500">

                                {{ $count }} ({{ $percent }}%)

                            </span>

                        </div>

                    @endfor

                </div>

            </div>


            {{-- =========================================================
                FILTERS
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200
                        shadow-sm mb-6">

                <div class="px-5 py-4 border-b border-gray-100">

                    <div class="flex items-center gap-2">

                        <svg class="w-5 h-5 text-blue-700"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L3.293 6.707A1 1 0 013 6V4z"/>

                        </svg>

                        <h2 class="text-sm font-bold text-gray-800">

                            Filter Feedback

                        </h2>

                    </div>

                </div>

                <form method="GET"
                      action="{{ route('admin.feedback.index') }}"
                      class="p-5 flex flex-col sm:flex-row
                             sm:items-end gap-4">

                    <div class="sm:w-56">

                        <label class="block text-xs font-bold
                                      uppercase tracking-wide
                                      text-gray-500 mb-1.5">

                            Transaction

                        </label>

                        <select name="type"
                                onchange="this.form.submit()"

                                class="w-full rounded-xl
                                       border-gray-300
                                       text-sm
                                       focus:border-blue-500
                                       focus:ring-blue-500">

                            <option value="">All Transactions</option>

                            @foreach ($types as $typeKey => $typeLabel)

                                <option value="{{ $typeKey }}"
                                        @selected($typeFilter === $typeKey)>

                                    {{ $typeLabel }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="sm:w-40">

                        <label class="block text-xs font-bold
                                      uppercase tracking-wide
                                      text-gray-500 mb-1.5">

                            Rating

                        </label>

                        <select name="rating"
                                onchange="this.form.submit()"

                                class="w-full rounded-xl
                                       border-gray-300
                                       text-sm
                                       focus:border-blue-500
                                       focus:ring-blue-500">

                            <option value="">All Ratings</option>

                            @for ($stars = 5; $stars >= 1; $stars--)

                                <option value="{{ $stars }}"
                                        @selected($ratingFilter === $stars)>

                                    {{ $stars }} star{{ $stars > 1 ? 's' : '' }}

                                </option>

                            @endfor

                        </select>

                    </div>

                    <div class="flex items-center gap-2">

                        <button type="submit"
                                class="inline-flex items-center gap-2
                                       px-5 py-2.5
                                       bg-[#063b91]
                                       hover:bg-[#052f73]
                                       text-white text-sm font-bold
                                       rounded-xl transition">

                            Apply Filters

                        </button>

                        @if ($typeFilter || $ratingFilter)

                            <a href="{{ route('admin.feedback.index') }}"
                               class="px-5 py-2.5
                                      bg-gray-100
                                      hover:bg-gray-200
                                      text-gray-700
                                      text-sm font-bold
                                      rounded-xl transition">

                                Reset

                            </a>

                        @endif

                    </div>

                </form>

            </div>


            {{-- =========================================================
                FEEDBACK LIST — the comments themselves
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200
                        shadow-sm overflow-hidden">


                {{-- Table Header --}}
                <div class="px-5 py-4
                            bg-gray-50
                            border-b border-gray-200">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center
                                sm:justify-between gap-2">

                        <div>

                            <h2 class="text-sm font-extrabold
                                       text-[#102d5b]">

                                Submitted Feedback

                            </h2>

                            <p class="text-xs text-gray-500 mt-0.5">

                                Read what users said about each finished
                                transaction &mdash; newest first.

                            </p>

                        </div>

                        <div class="text-xs font-semibold text-gray-400">

                            {{ $feedbacks->total() }} {{ $feedbacks->total() === 1 ? 'entry' : 'entries' }}

                        </div>

                    </div>

                </div>


                {{-- Rows --}}
                @forelse ($feedbacks as $feedback)

                    <div class="px-5 py-4
                                border-b border-gray-100
                                last:border-0
                                hover:bg-gray-50/70
                                transition">

                        <div class="flex flex-col lg:flex-row
                                    lg:items-start
                                    gap-4">


                            {{-- Who & when --}}
                            <div class="flex items-start gap-3 flex-1 min-w-0">

                                <div class="w-10 h-10 shrink-0
                                            rounded-xl
                                            bg-blue-50
                                            flex items-center
                                            justify-center">

                                    <span class="text-sm font-black text-blue-700">

                                        {{ mb_strtoupper(mb_substr($feedback->user?->name ?? '?', 0, 1)) }}

                                    </span>

                                </div>

                                <div class="min-w-0">

                                    <p class="text-sm font-bold text-gray-900">

                                        {{ $feedback->user?->name ?? 'Deleted account' }}

                                    </p>

                                    <p class="text-xs text-gray-400 mt-0.5">

                                        {{ $feedback->created_at->format('M d, Y') }}
                                        &middot;
                                        {{ $feedback->created_at->format('h:i A') }}

                                    </p>

                                </div>

                            </div>


                            {{-- What was rated --}}
                            <div class="lg:w-64 min-w-0">

                                <p class="text-[10px] font-bold
                                          uppercase tracking-wide
                                          text-gray-400 mb-1">

                                    Transaction

                                </p>

                                <span class="inline-flex items-center gap-1.5
                                             px-2.5 py-1
                                             rounded-lg
                                             bg-blue-50
                                             text-blue-700
                                             border border-blue-100
                                             text-[11px]
                                             font-bold">

                                    {{ $feedback->transaction_type === \App\Models\Feedback::TYPE_APPOINTMENT
                                        ? 'Appointment'
                                        : 'Document Request' }}

                                </span>

                                @if ($feedback->service_area)

                                    <p class="text-[11px] text-gray-500 mt-1
                                              truncate">

                                        {{ $feedback->service_area }}

                                    </p>

                                @endif

                            </div>


                            {{-- Stars --}}
                            <div class="lg:w-32">

                                <p class="text-[10px] font-bold
                                          uppercase tracking-wide
                                          text-gray-400 mb-1">

                                    Rating

                                </p>

                                <div class="flex gap-0.5">

                                    @for ($star = 1; $star <= 5; $star++)

                                        <svg class="w-4 h-4
                                                    {{ $star <= $feedback->rating
                                                        ? 'text-yellow-400'
                                                        : 'text-gray-200' }}"
                                             fill="currentColor"
                                             viewBox="0 0 24 24">

                                            <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"/>

                                        </svg>

                                    @endfor

                                </div>

                            </div>


                            {{-- The comment itself --}}
                            <div class="lg:flex-1 min-w-0">

                                <p class="text-[10px] font-bold
                                          uppercase tracking-wide
                                          text-gray-400 mb-1">

                                    Comment

                                </p>

                                @if ($feedback->comment)

                                    <p class="text-sm text-gray-700">

                                        {{ $feedback->comment }}

                                    </p>

                                @else

                                    <p class="text-xs italic text-gray-400">

                                        No comment provided.

                                    </p>

                                @endif

                            </div>


                        </div>

                    </div>

                @empty

                    {{-- Empty State --}}
                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto w-14 h-14
                                    rounded-2xl
                                    bg-amber-50
                                    flex items-center justify-center">

                            <svg class="w-7 h-7 text-amber-400"
                                 fill="currentColor"
                                 viewBox="0 0 24 24">

                                <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"/>

                            </svg>

                        </div>

                        <h3 class="mt-4 text-sm font-bold
                                   text-gray-800">

                            No Feedback Yet

                        </h3>

                        <p class="mt-1 text-xs text-gray-400">

                            Ratings and comments will appear here once
                            users submit feedback for finished services.

                        </p>

                    </div>

                @endforelse

            </div>


            {{-- =========================================================
                PAGINATION
            ========================================================== --}}
            @if ($feedbacks->hasPages())

                <div class="mt-5">

                    {{ $feedbacks->links() }}

                </div>

            @endif


            {{-- =========================================================
                FOOTER INFORMATION
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
                             fill="currentColor"
                             viewBox="0 0 24 24">

                            <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"/>

                        </svg>

                    </div>

                    <div>

                        <p class="text-sm font-bold text-blue-900">

                            Ratings straight from the people served

                        </p>

                        <p class="text-xs text-blue-700 mt-1">

                            Every user may rate a finished appointment or
                            document request once, from 1 to 5 stars, with an
                            optional comment. Averages here are recalculated
                            from those submissions each time this page opens.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
