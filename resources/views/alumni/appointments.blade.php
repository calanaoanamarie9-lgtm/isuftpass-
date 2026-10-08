<x-app-layout>
    @php
        $user = Auth::user();
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-gray-900">My Appointments</h1>
                <p class="mt-2 text-sm text-gray-500">Manage your upcoming office visits and bookings.</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Upcoming Visits</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($appointments ?? [] as $appointment)
                        <div class="px-6 py-4 flex items-center gap-4">
                            <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-800">Visit with {{ $appointment['office'] ?? 'Office' }}</p>
                                <p class="text-xs text-gray-400">{{ $appointment['date'] ?? '' }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $appointment['badge']['class'] ?? 'bg-green-50 text-green-700' }}">{{ $appointment['status'] ?? 'Confirmed' }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-gray-400">No upcoming appointments.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
