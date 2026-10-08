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
                    <p class="mt-1 text-2xl font-extrabold text-yellow-600">{{ $pendingPayments }}</p>
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
                    <p class="text-lg">💳</p>
                    <p class="mt-2 font-bold text-gray-900 text-sm">Pending Payments</p>
                    <p class="mt-1 text-xs text-gray-400">Students waiting for payment</p>
                </a>
                <a href="{{ route('cashier.payments.history') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                    <p class="text-lg">🧾</p>
                    <p class="mt-2 font-bold text-gray-900 text-sm">Payment History</p>
                    <p class="mt-1 text-xs text-gray-400">Records of paid transactions</p>
                </a>
                <a href="{{ route('cashier.ledger.index') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                    <p class="text-lg">🔍</p>
                    <p class="mt-2 font-bold text-gray-900 text-sm">Student Ledger</p>
                    <p class="mt-1 text-xs text-gray-400">Search financial records</p>
                </a>
                <a href="{{ route('profile.edit') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                    <p class="text-lg">⚙️</p>
                    <p class="mt-2 font-bold text-gray-900 text-sm">Profile Settings</p>
                    <p class="mt-1 text-xs text-gray-400">Update your account info</p>
                </a>
            </div>


        </div>
    </div>
</x-app-layout>