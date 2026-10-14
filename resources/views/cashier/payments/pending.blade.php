<x-app-layout>
    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">Pending Payments</h1>
                <p class="text-sm text-gray-500 mt-1">Approved document requests that are still awaiting payment. Record a payment to print the Official Receipt — the request is then marked Paid so the Registrar can start processing it.</p>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    <p class="font-bold mb-1">Payment not recorded:</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Search --}}
            <form method="GET" action="{{ route('cashier.payments.pending') }}"
                  class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-1.5">Search</label>
                    <input type="text" name="q" value="{{ $search }}"
                           placeholder="Student name, request number, or email"
                           class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div class="flex gap-2 items-end">
                    <button type="submit"
                            class="flex-1 sm:flex-none px-5 py-2.5 bg-blue-800 text-white text-sm font-semibold rounded-xl hover:bg-blue-900 transition">
                        Search
                    </button>
                    @if ($search)
                        <a href="{{ route('cashier.payments.pending') }}"
                           class="px-4 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-50 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- List --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                @forelse ($requests as $request)
                    <div class="flex flex-col lg:flex-row lg:items-center gap-3 px-5 py-4 border-b border-gray-50 last:border-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-bold text-gray-900 text-sm">{{ $request->student_name }}</p>
                                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $request->request_number }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $request->documentsSummary() }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $request->claim_mode === 'representative' ? 'Claimed by: ' . $request->representative_name . ' · ' : '' }}{{ $request->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 self-start lg:self-center">
                            <span class="font-bold text-blue-900">₱{{ number_format($request->totalFee(), 2) }}</span>
                            <form method="POST" action="{{ route('cashier.payments.record', $request) }}"
                                  class="flex flex-wrap items-center gap-2"
                                  data-confirm="Mark this request as paid with the OR number provided?"
                                  data-confirm-title="Record this payment?" data-confirm-ok="Yes, record payment" data-confirm-icon="question">
                                @csrf
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-1">Official Receipt No.</label>
                                    <input type="text" name="or_number" required maxlength="50"
                                           placeholder="e.g. OR-2025-00123"
                                           class="w-40 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>
                                <button type="submit"
                                        class="mt-4 sm:mt-0 px-4 py-2 bg-green-600 text-white text-xs font-bold rounded-xl hover:bg-green-700 transition">
                                    Record Payment
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <p class="font-semibold text-gray-700">{{ $search ? 'No pending payments match your search' : 'No pending payments' }}</p>
                        <p class="text-sm text-gray-400 mt-1">All student document payments are recorded. New requests awaiting payment will appear here.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</x-app-layout>