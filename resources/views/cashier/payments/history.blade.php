<x-app-layout>
    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Payment History</h1>
                    <p class="text-sm text-gray-500 mt-1">Records of completed and paid transactions.</p>
                </div>
            </div>

            {{-- Period filter --}}
            <form method="GET" action="{{ route('cashier.payments.history') }}"
                  class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 flex flex-wrap items-end gap-3">
                <input type="hidden" name="q" value="{{ $search }}">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-1.5">Period</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['all' => 'All', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $key => $label)
                            <button type="submit" name="period" value="{{ $key }}"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ ($period ?? 'all') === $key ? 'bg-blue-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- flex-wrap: the two date inputs + Apply/Reset buttons exceed 375px side by side --}}
                <div class="flex flex-wrap items-end gap-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-1.5">From</label>
                        <input type="date" name="from" value="{{ $from }}"
                               class="rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-gray-400 mb-1.5">To</label>
                        <input type="date" name="to" value="{{ $to }}"
                               class="rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <button type="submit" name="period" value="custom"
                            class="{{ $period === 'custom' ? 'bg-blue-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} px-4 py-2.5 rounded-xl text-sm font-semibold transition">
                        Apply Range
                    </button>
                    @if ($search || $period || $from || $to)
                        <a href="{{ route('cashier.payments.history') }}"
                           class="px-4 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-50 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- Search --}}
            <form method="GET" action="{{ route('cashier.payments.history') }}"
                  class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="hidden" name="from" value="{{ $from }}">
                <input type="hidden" name="to" value="{{ $to }}">
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
                </div>
            </form>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Total Collected</p>
                    <p class="mt-1 text-2xl font-extrabold text-green-700">₱{{ number_format($totalCollected, 2) }}</p>
                </div>
                <p class="text-xs text-gray-400">
                    @if ($period === 'custom' && $from && $to)
                        From {{ \Illuminate\Support\Carbon::parse($from)->format('M d, Y') }} to {{ \Illuminate\Support\Carbon::parse($to)->format('M d, Y') }}
                    @elseif ($period === 'custom' && $from)
                        From {{ \Illuminate\Support\Carbon::parse($from)->format('M d, Y') }}
                    @elseif ($period === 'custom' && $to)
                        Until {{ \Illuminate\Support\Carbon::parse($to)->format('M d, Y') }}
                    @elseif ($period === 'custom')
                        Custom range
                    @elseif ($period)
                        {{ ucfirst($period) }} to date
                    @else
                        All time
                    @endif
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                @forelse ($requests as $request)
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-gray-50 last:border-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-bold text-gray-900 text-sm">{{ $request->student_name }}</p>
                                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $request->request_number }}</span>
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-green-50 text-green-700 ring-1 ring-green-200">Paid</span>
                                @if ($request->or_number)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-50 text-gray-600 ring-1 ring-gray-200">OR #{{ $request->or_number }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $request->documentsSummary() }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Paid {{ $request->paid_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <span class="font-bold text-green-700 self-start sm:self-center">₱{{ number_format($request->totalFee(), 2) }}</span>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <p class="font-semibold text-gray-700">{{ $search ? 'No paid transactions match your search' : 'No payment records yet' }}</p>
                        <p class="text-sm text-gray-400 mt-1">Recorded payments will appear here once students pay at the Cashier Office.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</x-app-layout>