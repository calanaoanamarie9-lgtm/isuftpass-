<x-app-layout>

    <div class="min-h-screen bg-slate-50 py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =====================================================
                HEADER
            ====================================================== --}}
            <div class="flex flex-col lg:flex-row lg:items-center
                        lg:justify-between gap-5 mb-8">

                <div>

                    <div class="flex items-center gap-3">

                        <div class="w-11 h-11 rounded-2xl bg-blue-100
                                    flex items-center justify-center">

                            <svg class="w-6 h-6 text-blue-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>

                            </svg>

                        </div>

                        <div>

                            <h1 class="text-2xl font-extrabold text-slate-900">
                                Document Requests
                            </h1>

                            <p class="text-sm text-slate-500 mt-0.5">
                                Manage and process incoming student document requests.
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                SUCCESS MESSAGE
            ====================================================== --}}
            @if (session('status'))

                <div class="mb-6 flex items-start gap-3
                            rounded-2xl bg-emerald-50
                            border border-emerald-200 px-5 py-4">

                    <div class="w-8 h-8 rounded-lg bg-emerald-100
                                flex items-center justify-center shrink-0">

                        <svg class="w-4 h-4 text-emerald-700"
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

                        <p class="text-sm font-bold text-emerald-800">
                            Success
                        </p>

                        <p class="text-xs text-emerald-700 mt-0.5">
                            {{ session('status') }}
                        </p>

                    </div>

                </div>

            @endif


            {{-- =====================================================
                STAT CARDS
            ====================================================== --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                {{-- Total --}}
                <div class="bg-white rounded-2xl border border-slate-200
                            shadow-sm p-5">

                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase
                                      tracking-wider text-slate-400">
                                Total Requests
                            </p>

                            <p class="text-2xl font-extrabold text-slate-900 mt-2">
                                {{ $requests->total() }}
                            </p>
                        </div>

                        <div class="w-10 h-10 rounded-xl bg-blue-50
                                    flex items-center justify-center">

                            <svg class="w-5 h-5 text-blue-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>

                            </svg>

                        </div>

                    </div>

                </div>


                {{-- Pending --}}
                <div class="bg-white rounded-2xl border border-slate-200
                            shadow-sm p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wider text-slate-400">
                                Pending
                            </p>

                            <p class="text-2xl font-extrabold text-red-600 mt-2">
                                {{ $requests->where('status', 'submitted')->count() }}
                            </p>

                        </div>

                        <div class="w-10 h-10 rounded-xl bg-red-50
                                    flex items-center justify-center">

                            <span class="w-3 h-3 rounded-full bg-red-500"></span>

                        </div>

                    </div>

                </div>


                {{-- Processing --}}
                <div class="bg-white rounded-2xl border border-slate-200
                            shadow-sm p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wider text-slate-400">
                                Processing
                            </p>

                            <p class="text-2xl font-extrabold text-cyan-600 mt-2">
                                {{ $requests->where('status', 'processing')->count() }}
                            </p>

                        </div>

                        <div class="w-10 h-10 rounded-xl bg-cyan-50
                                    flex items-center justify-center">

                            <svg class="w-5 h-5 text-cyan-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 8v4l3 2"/>

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>

                            </svg>

                        </div>

                    </div>

                </div>


                {{-- Ready --}}
                <div class="bg-white rounded-2xl border border-slate-200
                            shadow-sm p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wider text-slate-400">
                                Ready for Pickup
                            </p>

                            <p class="text-2xl font-extrabold text-emerald-600 mt-2">
                                {{ $requests->where('status', 'ready_for_pickup')->count() }}
                            </p>

                        </div>

                        <div class="w-10 h-10 rounded-xl bg-emerald-50
                                    flex items-center justify-center">

                            <svg class="w-5 h-5 text-emerald-600"
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

                </div>

            </div>


            {{-- =====================================================
                TABS
            ====================================================== --}}
            <div class="bg-white rounded-2xl border border-slate-200
                        shadow-sm p-2 mb-6">

                <div class="flex flex-wrap gap-2">

                    @foreach ([
                        'active' => 'Active Processing',
                        'archived' => 'History Archive'
                    ] as $key => $label)

                        <a href="{{ route('registrar.document-requests.index', ['tab' => $key]) }}"
                           class="inline-flex items-center gap-2
                                  px-5 py-2.5 rounded-xl text-sm font-bold
                                  transition

                           {{ $tab === $key
                                ? 'bg-blue-800 text-white shadow-sm'
                                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' }}">

                            @if ($key === 'active')

                                <svg class="w-4 h-4" fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 8v4l3 2"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>

                                </svg>

                            @else

                                <svg class="w-4 h-4" fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M5 8h14M5 12h14M5 16h14"/>

                                </svg>

                            @endif

                            {{ $label }}

                        </a>

                    @endforeach

                </div>

            </div>


            {{-- =====================================================
                FILTERS
            ====================================================== --}}
            @if ($tab === 'active')

                <form method="GET"
                      action="{{ route('registrar.document-requests.index') }}"
                      class="bg-white rounded-2xl border border-slate-200
                             shadow-sm p-5 mb-6">

                    <input type="hidden" name="tab" value="active">

                    <div class="flex flex-col lg:flex-row
                                lg:items-end gap-4">

                        <div class="flex-1">

                            <label class="block text-[11px] font-bold
                                          uppercase tracking-wider
                                          text-slate-400 mb-2">

                                Filter by Status

                            </label>

                            <select name="status"
                                    class="w-full rounded-xl border-slate-300
                                           shadow-sm text-sm
                                           focus:border-blue-500
                                           focus:ring-blue-500">

                                <option value="">
                                    All Active Requests
                                </option>

                                @foreach ($statuses as $value => $label)

                                    @if (! in_array($value, ['completed', 'cancelled'], true))

                                        <option value="{{ $value }}"
                                            @selected(($filters['status'] ?? null) === $value)>

                                            {{ $label }}

                                        </option>

                                    @endif

                                @endforeach

                            </select>

                        </div>


                        <div class="flex gap-2">

                            <button type="submit"
                                    class="px-6 py-2.5 bg-blue-800
                                           text-white text-sm font-bold
                                           rounded-xl hover:bg-blue-900
                                           transition shadow-sm">

                                Apply Filter

                            </button>


                            @if (count($filters))

                                <a href="{{ route('registrar.document-requests.index') }}"
                                   class="px-5 py-2.5 bg-white
                                          border border-slate-200
                                          text-slate-600 text-sm font-bold
                                          rounded-xl hover:bg-slate-50
                                          transition">

                                    Reset

                                </a>

                            @endif

                        </div>

                    </div>

                </form>

            @endif


            {{-- =====================================================
                REQUEST LIST HEADER
            ====================================================== --}}
            <div class="flex items-center justify-between mb-3">

                <div>

                    <h2 class="text-sm font-extrabold text-slate-800">
                        {{ $tab === 'archived'
                            ? 'Request History'
                            : 'Incoming Requests' }}
                    </h2>

                    <p class="text-xs text-slate-400 mt-0.5">
                        Review and manage student document transactions.
                    </p>

                </div>

            </div>


            {{-- =====================================================
                REQUEST LIST
            ====================================================== --}}
            <div class="space-y-3">

                @forelse ($requests as $request)

                    <div class="group bg-white rounded-2xl
                                border border-slate-200 shadow-sm
                                hover:shadow-md hover:border-blue-200
                                transition-all duration-200 overflow-hidden">

                        <div class="p-5">

                            <div class="flex flex-col lg:flex-row
                                        lg:items-center gap-5">


                                {{-- Request Icon --}}
                                <div class="hidden sm:flex w-12 h-12
                                            rounded-xl bg-blue-50
                                            items-center justify-center
                                            shrink-0">

                                    <svg class="w-6 h-6 text-blue-700"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>

                                    </svg>

                                </div>


                                {{-- Main Information --}}
                                <div class="flex-1 min-w-0">

                                    <div class="flex items-center gap-2
                                                flex-wrap">

                                        <h3 class="font-extrabold
                                                   text-slate-900">

                                            {{ $request->student_name }}

                                        </h3>

                                        <span class="px-2.5 py-1 rounded-lg
                                                     bg-slate-100 text-slate-500
                                                     text-[10px] font-bold
                                                     tracking-wide">

                                            {{ $request->request_number }}

                                        </span>

                                    </div>


                                    <p class="text-sm font-semibold
                                              text-slate-700 mt-2">

                                        {{ $request->documentsSummary() }}

                                    </p>


                                    <div class="flex flex-wrap items-center
                                                gap-x-4 gap-y-1 mt-2">

                                        <span class="inline-flex items-center
                                                     gap-1.5 text-xs
                                                     text-slate-400">

                                            <svg class="w-3.5 h-3.5"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>

                                            </svg>

                                            {{ $request->purpose_type
                                                ? \App\Enums\RequestPurposeType::tryFrom($request->purpose_type)?->label()
                                                : 'No purpose specified' }}

                                        </span>


                                        <span class="text-slate-300">
                                            •
                                        </span>


                                        <span class="text-xs text-slate-400">

                                            Submitted
                                            {{ $request->created_at->diffForHumans() }}

                                        </span>

                                    </div>

                                </div>


                                {{-- Status + Action --}}
                                <div class="flex items-center
                                            justify-between lg:justify-end
                                            gap-3">

                                    <span class="inline-flex items-center
                                                 gap-2 px-3 py-1.5
                                                 rounded-full text-xs
                                                 font-bold whitespace-nowrap

                                        @if ($request->status === 'submitted')
                                            bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200

                                        @elseif ($request->status === 'processing')
                                            bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200

                                        @elseif ($request->status === 'for_signature')
                                            bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200

                                        @elseif ($request->status === 'ready_for_pickup')
                                            bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200

                                        @elseif ($request->status === 'completed')
                                            bg-blue-50 text-blue-700 ring-1 ring-blue-200

                                        @else
                                            bg-red-50 text-red-600 ring-1 ring-red-200
                                        @endif
                                    ">

                                        <span class="w-1.5 h-1.5
                                                     rounded-full bg-current">
                                        </span>

                                        {{ \App\Enums\DocumentRequestStatus::tryFrom($request->status)?->label()
                                            ?? ucfirst($request->status) }}

                                    </span>


                                    <a href="{{ route('registrar.document-requests.show', $request) }}"
                                       class="inline-flex items-center gap-2
                                              px-4 py-2.5 rounded-xl
                                              bg-blue-800 text-white
                                              text-xs font-bold
                                              hover:bg-blue-900
                                              transition shadow-sm
                                              whitespace-nowrap">

                                        View Request

                                        <svg class="w-4 h-4"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 5l7 7-7 7"/>

                                        </svg>

                                    </a>

                                </div>

                            </div>

                        </div>


                        {{-- Bottom status indicator --}}
                        <div class="h-1

                            @if ($request->status === 'submitted')
                                bg-yellow-400
                            @elseif ($request->status === 'processing')
                                bg-cyan-500
                            @elseif ($request->status === 'for_signature')
                                bg-indigo-500
                            @elseif ($request->status === 'ready_for_pickup')
                                bg-emerald-500
                            @elseif ($request->status === 'completed')
                                bg-blue-500
                            @else
                                bg-red-400
                            @endif
                        "></div>

                    </div>

                @empty

                    {{-- =================================================
                        EMPTY STATE
                    ================================================== --}}
                    <div class="bg-white rounded-3xl
                                border border-dashed border-slate-300
                                p-14 text-center">

                        <div class="mx-auto w-16 h-16 rounded-2xl
                                    bg-slate-100 flex items-center
                                    justify-center">

                            <svg class="w-8 h-8 text-slate-400"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.7"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>

                            </svg>

                        </div>


                        <h3 class="mt-5 font-bold text-slate-800">

                            No
                            {{ $tab === 'archived'
                                ? 'Archived'
                                : 'Active' }}
                            Document Requests

                        </h3>


                        <p class="text-sm text-slate-400 mt-1 max-w-md mx-auto">

                            {{ $tab === 'archived'
                                ? 'Completed or cancelled document requests will appear here.'
                                : 'Student document requests will appear here once they are submitted.' }}

                        </p>

                    </div>

                @endforelse

            </div>


            {{-- =====================================================
                PAGINATION
            ====================================================== --}}
            @if ($requests->hasPages())

                <div class="mt-6">
                    {{ $requests->links() }}
                </div>

            @endif

        </div>

    </div>

</x-app-layout>