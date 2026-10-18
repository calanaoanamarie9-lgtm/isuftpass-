<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Welcome Banner --}}
            <div class="bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 rounded-2xl p-6 sm:p-8 overflow-hidden relative">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-700/40 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative">
                    <p class="text-xs font-semibold uppercase tracking-widest text-yellow-400">
                        Cashier Dashboard
                    </p>
                    <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-white">
                        Welcome, {{ Auth::user()->name }}!
                    </h1>
                    <p class="mt-2 text-sm text-blue-200 max-w-lg">
                        Today's collections, pending payments, and daily stats at a glance.
                    </p>
                </div>
            </div>

            {{-- Stats --}}
            <div class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Today's Collections</p>
                    <p class="mt-1 text-2xl font-extrabold text-green-700">₱{{ number_format($todayCollections, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $todayPayments }} payment{{ $todayPayments === 1 ? '' : 's' }} recorded</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">This Month's Collections</p>
                    <p class="mt-1 text-2xl font-extrabold text-blue-700">₱{{ number_format($monthCollections, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $monthPayments }} payment{{ $monthPayments === 1 ? '' : 's' }} this month</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Pending Payments</p>
                    <p class="mt-1 text-2xl font-extrabold text-red-600">{{ $pendingPayments }}</p>
                    <p class="mt-1 text-xs text-gray-400">Awaiting cashier recording</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Ready for Pick-up</p>
                    <p class="mt-1 text-2xl font-extrabold text-blue-800">{{ $readyForPickup }}</p>
                    <p class="mt-1 text-xs text-gray-400">Documents awaiting claiming</p>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mt-8 grid sm:grid-cols-4 gap-4">
                <a href="{{ route('cashier.payments.pending') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                    <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                    <p class="mt-2 font-bold text-gray-900 text-sm">Pending Payments</p>
                    <p class="mt-1 text-xs text-gray-400">Students waiting for payment</p>
                </a>
                <a href="{{ route('cashier.payments.history') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                    <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 14.25 6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                    <p class="mt-2 font-bold text-gray-900 text-sm">Payment History</p>
                    <p class="mt-1 text-xs text-gray-400">Records of paid transactions</p>
                </a>
                <a href="{{ route('cashier.ledger.index') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                    <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <p class="mt-2 font-bold text-gray-900 text-sm">Student Ledger</p>
                    <p class="mt-1 text-xs text-gray-400">Search financial records</p>
                </a>
                <a href="{{ route('profile.edit') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                    <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <p class="mt-2 font-bold text-gray-900 text-sm">Profile Settings</p>
                    <p class="mt-1 text-xs text-gray-400">Update your account info</p>
                </a>
            </div>


        </div>
    </div>
</x-app-layout>