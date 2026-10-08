<x-app-layout>

    <div class="min-h-screen bg-[#f5f7fb] py-8">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= HEADER ================= --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 mb-7">

                <div>
                    <div class="flex items-center gap-3">

                        <div class="w-11 h-11 rounded-xl bg-blue-800
                                    flex items-center justify-center">

                            <svg class="w-6 h-6 text-white"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A2 2 0 0119 6.707V19a2 2 0 01-2 2z"/>

                            </svg>

                        </div>

                        <div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123b78]">
                                My Requests
                            </h1>

                            <p class="text-sm text-gray-500 mt-0.5">
                                Track your document requests and their status.
                            </p>
                        </div>

                    </div>
                </div>


                {{-- NEW REQUEST --}}
                <a href="{{ route('student.documents.create') }}"
                   class="inline-flex items-center justify-center gap-2
                          px-5 py-2.5 rounded-xl
                          bg-blue-800 text-white
                          text-sm font-bold
                          hover:bg-blue-900
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

                    New Request

                </a>

            </div>


            {{-- ================= SUCCESS MESSAGE ================= --}}
            @if (session('status') && ! session('swal'))

                <div class="mb-6 flex items-center gap-3
                            rounded-xl border border-green-200
                            bg-green-50 px-4 py-3">

                    <div class="w-8 h-8 rounded-full bg-green-100
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

            {{-- ================= INSTRUCTIONAL BANNER ================= --}}
            @if (session('instructions') && ($instructionsRequest = \App\Models\DocumentRequest::find(session('instructions'))))

                @include('student.documents._instructions', ['request' => $instructionsRequest])

            @endif

            {{-- ================= TABS ================= --}}
            <div class="bg-white border border-gray-200
                        rounded-xl p-1.5 mb-6
                        inline-flex items-center gap-1">

                @foreach ([
                    'active' => 'Active Requests',
                    'archived' => 'Archives'
                ] as $key => $label)

                    <a href="{{ route('student.documents.index', ['tab' => $key]) }}"
                       class="px-5 py-2.5 rounded-lg
                              text-sm font-bold transition
                              {{ $tab === $key
                                  ? 'bg-blue-800 text-white shadow-sm'
                                  : 'text-gray-600 hover:bg-gray-100' }}">

                        {{ $label }}

                    </a>

                                    
                                    


                                    
                                    

                @endforeach

            </div>


            {{-- ================= REQUEST LIST ================= --}}
            <div class="space-y-4">

                @forelse ($requests as $request)

                    <div class="bg-white rounded-2xl
                                border border-gray-200
                                shadow-sm
                                hover:border-blue-300
                                hover:shadow-md
                                transition">

                        <div class="p-5 sm:p-6">

                            <div class="flex flex-col lg:flex-row
                                        lg:items-center
                                        lg:justify-between
                                        gap-5">


                                {{-- ================= REQUEST INFO ================= --}}
                                <a href="{{ route('student.documents.show', $request) }}"
                                   class="flex items-start gap-4 min-w-0 group">

                                    {{-- ICON --}}
                                    <div class="w-12 h-12 shrink-0
                                                rounded-xl
                                                bg-blue-50
                                                flex items-center justify-center
                                                group-hover:bg-blue-100
                                                transition">

                                        <svg class="w-6 h-6 text-blue-700"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A2 2 0 0119 6.707V19a2 2 0 01-2 2z"/>

                                        </svg>

                                    </div>


                                    {{-- DETAILS --}}
                                    <div class="min-w-0">

                                        <h2 class="font-extrabold text-gray-900
                                                   group-hover:text-blue-800
                                                   transition
                                                   truncate">

                                            {{ $request->documents->pluck('name')->join(', ') }}

                                            @if ($request->others_specification)

                                                <span class="font-medium text-gray-400">

                                                    + Others:
                                                    {{ $request->others_specification }}

                                                </span>

                                            @endif

                                            

                                        </h2>


                                        <div class="flex flex-wrap items-center
                                                    gap-x-2 gap-y-1
                                                    mt-1.5">

                                            <span class="text-xs font-semibold
                                                         text-blue-700">

                                                {{ $request->request_number }}

                                            </span>

                                            <span class="text-gray-300">
                                                •
                                            </span>

                                            <span class="text-xs text-gray-500">

                                                {{ $request->created_at->format('M j, Y g:i A') }}

                                            </span>

                                        </div>

                                    </div>

                                </a>

                                    
                                    


                                    
                                    


                                {{-- ================= ACTIONS ================= --}}
                                <div class="flex flex-wrap items-center
                                            gap-2 lg:justify-end">


                                    {{-- STATUS --}}
                                    @if ($request->status === 'ready_for_pickup')

                                        <span class="inline-flex items-center gap-1.5
                                                     px-3 py-1.5
                                                     rounded-full
                                                     text-xs font-bold
                                                     bg-green-50
                                                     text-green-700
                                                     border border-green-200">

                                            <span class="w-1.5 h-1.5 rounded-full
                                                         bg-green-500"></span>

                                            Ready for Pick-up

                                        </span>

                                    @elseif ($request->status === 'cancelled')

                                        <span class="inline-flex items-center gap-1.5
                                                     px-3 py-1.5
                                                     rounded-full
                                                     text-xs font-bold
                                                     bg-red-50
                                                     text-red-600
                                                     border border-red-200">

                                            <span class="w-1.5 h-1.5 rounded-full
                                                         bg-red-500"></span>

                                            Cancelled

                                        </span>

                                    @elseif ($request->status === 'completed')

                                        <span class="inline-flex items-center gap-1.5
                                                     px-3 py-1.5
                                                     rounded-full
                                                     text-xs font-bold
                                                     bg-blue-50
                                                     text-blue-700
                                                     border border-blue-200">

                                            <span class="w-1.5 h-1.5 rounded-full
                                                         bg-blue-500"></span>

                                            Completed

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5
                                                     px-3 py-1.5
                                                     rounded-full
                                                     text-xs font-bold
                                                     bg-red-50
                                                     text-red-700
                                                     border border-red-200">

                                            <span class="w-1.5 h-1.5 rounded-full
                                                         bg-red-500"></span>

                                            {{ ucwords(str_replace('_', ' ', $request->status)) }}

                                        </span>

                                    @endif

                                    {{-- VIEW DETAILS --}}
                                    <a href="{{ route('student.documents.show', $request) }}"
                                       class="inline-flex items-center gap-1
                                              px-3.5 py-2
                                              rounded-lg
                                              bg-blue-800
                                              text-white
                                              text-xs font-bold
                                              hover:bg-blue-900
                                              transition">

                                        View Details

                                        <svg class="w-3.5 h-3.5"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 5l7 7-7 7"/>

                                        </svg>

                                    </a>

                                    {{-- REQUISITION FORM --}}
                                    <a href="{{ route('student.documents.requisition', $request) }}"
                                       class="inline-flex items-center gap-2
                                              px-3.5 py-2
                                              rounded-lg
                                              border border-blue-200
                                              bg-white
                                              text-blue-800
                                              text-xs font-bold
                                              hover:bg-blue-50
                                              transition">

                                        <svg class="w-4 h-4"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A2 2 0 0119 6.707V19a2 2 0 01-2 2z"/>

                                        </svg>

                                        Requisition Form

                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                @empty

                    {{-- ================= EMPTY STATE ================= --}}
                    <div class="bg-white rounded-2xl
                                border border-gray-200
                                shadow-sm
                                p-12 text-center">

                        <div class="w-14 h-14 mx-auto
                                    rounded-xl
                                    bg-blue-50
                                    flex items-center justify-center">

                            <svg class="w-7 h-7 text-blue-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A2 2 0 0119 6.707V19a2 2 0 01-2 2z"/>

                            </svg>

                        </div>


                        <h3 class="mt-4 text-base font-extrabold text-gray-800">

                            No {{ $tab }} document requests

                        </h3>


                        <p class="mt-1 text-sm text-gray-500">

                            Submit a document request to get started.

                        </p>


                        <a href="{{ route('student.documents.create') }}"
                           class="inline-flex items-center gap-2
                                  mt-5 px-5 py-2.5
                                  bg-blue-800
                                  text-white
                                  text-sm font-bold
                                  rounded-xl
                                  hover:bg-blue-900
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

                            New Request

                        </a>

                                    
                                    


                                    
                                    

                    </div>

                @endforelse

            </div>


            {{-- ================= PAGINATION ================= --}}
            @if ($requests->hasPages())

                <div class="mt-6">
                    {{ $requests->links() }}
                </div>

            @endif

            {{-- ================= FOOTER NOTE ================= --}}
            <div class="mt-6 flex items-center justify-center gap-2">

                <svg class="w-4 h-4 text-gray-400"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 15v2m0-8v4m0-9a9 9 0 100 18 9 9 0 000-18z"/>

                </svg>

                <p class="text-xs text-gray-400">
                    Your document requests are securely managed through ISUFSTPASS.
                </p>

            </div>

        </div>

    </div>


    {{-- ================= SWEETALERT ================= --}}
    @if (session('swal'))

        <script>

            document.addEventListener('DOMContentLoaded', function () {

                if (window.Swal) {

                    window.Swal.fire({

                        title: '{{ session('swal') }}',

                        text: 'Please print the requisition form before going to the registrar to submit it. Track its progress under My Requests & Status. A payment notice was sent to your email.',

                        icon: 'success',

                        confirmButtonText: 'OK',

                        confirmButtonColor: '#123b78',

                    });

                } else {

                    window.location.href =
                        '{{ route('student.documents.index') }}';

                }

            });

        </script>

    @endif

</x-app-layout>