<x-app-layout>

    <div class="min-h-screen bg-[#f5f7fb] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                WELCOME HEADER
            ========================================================== --}}
            <div class="bg-[#102d5b] rounded-2xl overflow-hidden mb-7">

                <div class="px-6 py-7 sm:px-8">

                    <div class="flex flex-col sm:flex-row
                                sm:items-center sm:justify-between gap-5">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-widest text-yellow-400">
                                ISUFSTPASS
                            </p>

                            <h1 class="mt-2 text-2xl sm:text-3xl
                                       font-bold text-white">
                                Welcome, {{ Auth::user()->name }}!
                            </h1>

                            <p class="mt-2 text-sm text-blue-100 max-w-2xl">
                                Manage user accounts, monitor transactions,
                                and oversee system activities from the
                                administrator dashboard.
                            </p>

                        </div>


                        <div class="shrink-0">

                            <div class="px-4 py-3 rounded-xl
                                        bg-white/10 border border-white/10">

                                <p class="text-[11px] uppercase
                                          tracking-wide text-blue-200">
                                    Account Role
                                </p>

                                <p class="mt-1 text-sm font-bold text-white">
                                    Administrator
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- =========================================================
                OFFICE FILTER DROPDOWN
            ========================================================== --}}
            <div class="bg-white rounded-xl p-4 mb-6 shadow-sm border border-gray-200">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row gap-3 max-w-2xl">
                    <label for="officeFilter" class="text-sm text-gray-700">
                        Office:
                    </label>
                    <select name="office" id="officeFilter"
                            onchange="this.form.submit()"
                            class="pl-3 pr-4 py-2 rounded border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Offices</option>
                        @foreach ($knownOfficeKeys as $key)
                            @continue ($key === 'All Offices')
                            <option value="{{ $key }}"
                                {{ $officeFilter === $key ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- =========================================================
                STATISTICS
            ========================================================== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">


                {{-- Total Accounts --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm p-5">

                    <div class="flex items-start justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wide text-gray-400">
                                Total Accounts
                            </p>

                            <p class="mt-2 text-2xl font-black
                                      text-[#102d5b]">
                                {{ $totalUsers }}
                            </p>

                        </div>

                        <div class="w-10 h-10 rounded-xl
                                    bg-blue-50 text-blue-700
                                    flex items-center justify-center">

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857
                                           M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                                           M7 20H2v-2a3 3 0 015.356-1.857
                                           M7 20v-2c0-.656.126-1.283.356-1.857
                                           m0 0a5.002 5.002 0 019.288 0
                                           M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>

                            </svg>

                        </div>

                    </div>

                    <p class="mt-3 text-xs text-gray-400">
                        Registered system users
                    </p>

                </div>


                {{-- Total Transactions --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm p-5">

                    <div class="flex items-start justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wide text-gray-400">
                                Total Transactions
                            </p>

                            <p class="mt-2 text-2xl font-black
                                      text-[#102d5b]">
                                {{ $totalTransactions }}
                            </p>

                        </div>

                        <div class="w-10 h-10 rounded-xl
                                    bg-green-50 text-green-700
                                    flex items-center justify-center">

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7
                                       a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                       a1 1 0 01.707.293l5.414 5.414
                                       A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>

                            </svg>

                        </div>

                    </div>

                    <p class="mt-3 text-xs text-gray-400">
                        Document transactions
                    </p>

                </div>


                {{-- QR Verifications --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm p-5">

                    <div class="flex items-start justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wide text-gray-400">
                                QR Verifications
                            </p>

                            <p class="mt-2 text-2xl font-black
                                      text-[#102d5b]">
                                {{ $qrVerifications }}
                            </p>

                        </div>

                        <div class="w-10 h-10 rounded-xl
                                    bg-yellow-50 text-yellow-600
                                    flex items-center justify-center">

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 4V2m0 20v-2m8-8h2M2 12h2
                                       m13.657-5.657l1.414-1.414
                                       M4.343 19.657l-1.414 1.414
                                       m0-17.414l1.414 1.414
                                       m14.829 14.829l1.414 1.414
                                       M12 8a4 4 0 100 8 4 4 0 000-8z"/>

                            </svg>

                        </div>

                    </div>

                    <p class="mt-3 text-xs text-gray-400">
                        Verified QR transactions
                    </p>

                </div>


                {{-- Collections --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm p-5">

                    <div class="flex items-start justify-between">

                        <div>

                            <p class="text-xs font-bold uppercase
                                      tracking-wide text-gray-400">
                                Total Collections
                            </p>

                            <p class="mt-2 text-2xl font-black
                                      text-[#102d5b]">
                                ₱{{ number_format($totalCollections, 2) }}
                            </p>

                        </div>

                        <div class="w-10 h-10 rounded-xl
                                    bg-indigo-50 text-indigo-700
                                    flex items-center justify-center">

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 10h18M7 15h1m4 0h1
                                       m-7 4h12a3 3 0 003-3V8
                                       a3 3 0 00-3-3H6a3 3 0 00-3 3v8
                                       a3 3 0 003 3z"/>

                            </svg>

                        </div>

                    </div>

                    <p class="mt-3 text-xs text-gray-400">
                        Recorded payments
                    </p>

                </div>

            </div>


            {{-- =========================================================
                MAIN CONTENT
            ========================================================== --}}
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">


                {{-- =====================================================
                    RECENT TRANSACTIONS
                ====================================================== --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-5 border-b border-gray-200
                                flex items-center justify-between">

                        <div>

                            <h2 class="text-base font-bold text-[#102d5b]">
                                Recent Transactions
                            </h2>

                            <p class="text-xs text-gray-400 mt-1">
                                Latest document requests in the system
                            </p>

                        </div>

                        <span class="hidden sm:inline-flex
                                     px-3 py-1.5 rounded-lg
                                     bg-blue-50 text-blue-700
                                     text-xs font-semibold">

                            Recent

                        </span>

                    </div>


                    {{-- Table --}}
                    <div class="overflow-x-auto">

                        <table class="min-w-full">

                            <thead class="bg-[#f8fafc] border-b border-gray-200">

                                <tr>

                                    <th class="px-6 py-3.5 text-left
                                               text-xs font-bold uppercase
                                               tracking-wide text-gray-500">
                                        Transaction
                                    </th>

                                    <th class="px-6 py-3.5 text-left
                                               text-xs font-bold uppercase
                                               tracking-wide text-gray-500">
                                        Student
                                    </th>

                                    <th class="px-6 py-3.5 text-left
                                               text-xs font-bold uppercase
                                               tracking-wide text-gray-500">
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-100">

                                @forelse ($recentTransactions as $tx)

                                    <tr class="hover:bg-blue-50/30 transition">

                                        {{-- Transaction --}}
                                        <td class="px-6 py-4">

                                            <p class="text-sm font-semibold
                                                      text-gray-800">

                                                {{ $tx->documentsSummary() }}

                                            </p>

                                        </td>


                                        {{-- Student --}}
                                        <td class="px-6 py-4">

                                            <p class="text-sm text-gray-600">
                                                {{ $tx->student_name }}
                                            </p>

                                        </td>


                                        {{-- Status --}}
                                        <td class="px-6 py-4">

                                            <span class="inline-flex items-center
                                                          px-2.5 py-1 rounded-lg
                                                          bg-blue-50 text-blue-700
                                                          border border-blue-100
                                                          text-xs font-semibold">

                                                <span class="w-1.5 h-1.5
                                                                  rounded-full
                                                                  bg-blue-600 mr-1.5">
                                                </span>

                                                {{ ucwords(str_replace('_', ' ', $tx->status)) }}

                                            </span>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="3"
                                            class="px-6 py-14 text-center">

                                            <div class="max-w-sm mx-auto">

                                                <div class="w-11 h-11 mx-auto
                                                            rounded-xl bg-gray-100
                                                            flex items-center
                                                            justify-center
                                                            text-gray-400">
                                                    —
                                                </div>

                                                <p class="mt-3 text-sm
                                                          font-semibold
                                                          text-gray-700">
                                                    No transactions yet
                                                </p>

                                                <p class="mt-1 text-xs
                                                          text-gray-400">
                                                    Recent document transactions
                                                    will appear here.
                                                </p>

                                            </div>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>


                {{-- =====================================================
                    APPOINTMENTS
                ====================================================== --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-5 border-b border-gray-200
                                flex items-center justify-between">

                        <div>

                            <h2 class="text-base font-bold text-[#102d5b]">
                                Appointments
                            </h2>

                            <p class="text-xs text-gray-400 mt-1">
                                Recent appointments across offices
                            </p>

                        </div>

                    </div>


                    {{-- List --}}
                    <div class="p-4">

                        @forelse ($appointments as $appointment)

                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">

                                <div class="flex items-center gap-3">

                                    <span class="text-sm text-gray-600">
                                        {{ $appointment->reference_code }}
                                    </span>

                                    <span class="text-sm font-medium text-gray-900">
                                        {{ $appointment->user->name }}
                                    </span>

                                </div>

                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg
                                             bg-blue-50 text-blue-700
                                             border border-blue-100
                                             text-xs font-semibold">

                                    <span class="w-1.5 h-1.5
                                      rounded-full
                                      bg-blue-600 mr-1.5">
                                    </span>

                                    {{ ucwords(str_replace('_', ' ', $appointment->status)) }}

                                </span>

                            </div>

                        @empty

                            <p class="text-center text-gray-500 py-4">
                                No appointments yet
                            </p>

                        @endforelse

                    </div>

                </div>


                {{-- =====================================================
                    CONSULTATION SERVICES
                ====================================================== --}}
                <div class="bg-white rounded-2xl border border-gray-200
                            shadow-sm overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-5 border-b border-gray-200
                                flex items-center justify-between">

                        <div>

                            <h2 class="text-base font-bold text-[#102d5b]">
                                Consultation Services
                            </h2>

                            <p class="text-xs text-gray-400 mt-1">
                                Office-managed consultation services
                            </p>

                        </div>

                    </div>


                    {{-- List --}}
                    <div class="p-4">

                        @forelse ($services as $service)

                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">

                                <div class="flex items-center gap-3">

                                    <span class="text-sm text-gray-600">
                                        {{ $service->name }}
                                    </span>

                                    @if ($service->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-800 text-xs">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-800 text-xs">
                                            Hidden
                                        </span>
                                    @endif

                                </div>

                                <form method="POST"
                                      action="{{ route('admin.consultations.toggle', $service) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="text-sm underline
                                                   {{ $service->is_active ? 'text-blue-600' : 'text-gray-500 opacity-60' }}">
                                        {{ $service->is_active ? 'Deactivate' : 'Reactivate' }}
                                    </button>

                                </form>

                            </div>

                        @empty

                            <p class="text-center text-gray-500 py-4">
                                No consultation services
                            </p>

                        @endforelse

                    </div>

                </div>

            </div>


            {{-- =========================================================
                ACCOUNT OVERVIEW
            ========================================================== --}}
            <div class="bg-white rounded-2xl border border-gray-200
                        shadow-sm overflow-hidden">

                {{-- Header --}}
                <div class="px-6 py-5 border-b border-gray-200">

                    <h2 class="text-base font-bold text-[#102d5b]">
                        Accounts Overview
                    </h2>

                    <p class="text-xs text-gray-400 mt-1">
                        User distribution by account type
                    </p>

                </div>


                <div class="p-6">


                    {{-- Students --}}
                    <div class="flex items-center justify-between py-3">

                        <div class="flex items-center gap-3">

                            <span class="w-2.5 h-2.5 rounded-full
                                     bg-green-500"></span>

                            <span class="text-sm font-medium text-gray-700">
                                Students
                            </span>

                        </div>

                        <span class="text-sm font-bold text-gray-900">
                            {{ $roleCounts['students'] }}
                        </span>

                    </div>


                    {{-- Administrators --}}
                    <div class="flex items-center justify-between py-3">

                        <div class="flex items-center gap-3">

                            <span class="w-2.5 h-2.5 rounded-full
                                     bg-blue-600"></span>

                            <span class="text-sm font-medium text-gray-700">
                                Administrators
                            </span>

                        </div>

                        <span class="text-sm font-bold text-gray-900">
                            {{ $roleCounts['admins'] }}
                        </span>

                    </div>


                    {{-- Registrar --}}
                    <div class="flex items-center justify-between py-3">

                        <div class="flex items-center gap-3">

                            <span class="w-2.5 h-2.5 rounded-full
                                     bg-yellow-500"></span>

                            <span class="text-sm font-medium text-gray-700">
                                Registrar Staff
                            </span>

                        </div>

                        <span class="text-sm font-bold text-gray-900">
                            {{ $roleCounts['registrars'] }}
                        </span>

                    </div>


                    {{-- Cashier --}}
                    <div class="flex items-center justify-between py-3">

                        <div class="flex items-center gap-3">

                            <span class="w-2.5 h-2.5 rounded-full
                                     bg-indigo-600"></span>

                            <span class="text-sm font-medium text-gray-700">
                                Cashier Staff
                            </span>

                        </div>

                        <span class="text-sm font-bold text-gray-900">
                            {{ $roleCounts['cashiers'] }}
                        </span>

                    </div>


                    {{-- Department --}}
                    <div class="flex items-center justify-between py-3">

                        <div class="flex items-center gap-3">

                            <span class="w-2.5 h-2.5 rounded-full
                                     bg-cyan-500"></span>

                            <span class="text-sm font-medium text-gray-700">
                                Department Staff
                            </span>

                        </div>

                        <span class="text-sm font-bold text-gray-900">
                            {{ $roleCounts['departments'] }}
                        </span>

                    </div>


                    {{-- Department Offices --}}
                    @if ($departmentOffices->isNotEmpty())

                        <div class="pt-5 mt-2 border-t border-gray-100">

                            <p class="text-xs font-bold uppercase
                                      tracking-wide text-gray-400 mb-3">
                                Offices
                            </p>

                            <div class="space-y-2">

                                @foreach ($departmentOffices as $office => $count)

                                    <div class="flex items-center
                                                justify-between
                                                px-3 py-2 rounded-lg
                                                bg-gray-50">

                                        <span class="text-xs font-medium
                                                  text-gray-600">
                                            {{ $office ?: 'General' }}
                                        </span>

                                        <span class="text-xs font-bold
                                                  text-[#102d5b]">
                                            {{ $count }}
                                        </span>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                    @endif


                    {{-- Active Requests --}}
                    <div class="mt-6 p-4 rounded-xl
                                bg-blue-50 border border-blue-100">

                        <p class="text-xs font-bold uppercase
                                  tracking-wide text-blue-600">
                            Active Requests
                        </p>

                        <p class="mt-1 text-lg font-black text-[#102d5b]">
                            {{ $activeRequests }}
                        </p>

                        <p class="text-xs text-blue-700 mt-1">
                            Active document request(s) across offices.
                        </p>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                SYSTEM FOOTER NOTE
            ========================================================== --}}
            <div class="mt-6 px-5 py-4 bg-white
                        border border-gray-200 rounded-xl">

                <div class="flex items-center gap-3">

                    <div class="w-8 h-8 rounded-lg
                                bg-blue-50 text-blue-700
                                flex items-center justify-center
                                font-bold shrink-0">
                        i
                    </div>

                    <div>

                        <p class="text-sm font-semibold text-[#102d5b]">
                            ISUFSTPASS Administration
                        </p>

                        <p class="text-xs text-gray-500 mt-0.5">
                            Monitor accounts, document transactions,
                            QR verification activity, and system collections
                            from this dashboard.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>