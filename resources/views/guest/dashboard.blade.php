<x-app-layout>
    @php
        $user = Auth::user();
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Welcome Header --}}
            <div class="bg-gradient-to-br from-[#12347d] via-[#1d4ed8] to-[#3b82f6] rounded-2xl p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative">
                    <p class="text-xs font-semibold uppercase tracking-widest text-yellow-400">ISUFSTPASS Guest Portal</p>
                    <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-white">Welcome, {{ $user->name }}!</h1>
                    <p class="mt-2 text-sm text-blue-200 max-w-lg">
                        Book office visits and manage your campus appointments.
                    </p>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="mt-8 grid grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-black text-gray-900">{{ $upcomingAppointments }}</p>
                    <p class="text-sm text-gray-500 mt-1">Upcoming Visits</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-black text-gray-900">{{ $completedAppointments }}</p>
                    <p class="text-sm text-gray-500 mt-1">Completed Visits</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-black text-gray-900">{{ $hasPass ? 'Active' : '—' }}</p>
                    <p class="text-sm text-gray-500 mt-1">Campus Pass</p>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mt-8 grid sm:grid-cols-2 gap-4">
                <a href="{{ route('student.appointments.create') ?? '#' }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                    <p class="font-bold text-gray-900">Book Appointment</p>
                    <p class="text-xs text-gray-500 mt-0.5">Schedule an office appointment</p>
                </a>
                <a href="{{ route('student.appointments.index') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-blue-300 hover:shadow-md transition">
                    <p class="font-bold text-gray-900">My Appointments</p>
                    <p class="text-xs text-gray-500 mt-0.5">View and manage campus visits</p>
                </a>
            </div>

            {{-- Upcoming Appointments --}}
            <div class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900">Upcoming Appointments</h2>
                    <a href="{{ route('student.appointments.index') }}"
                       class="text-sm font-semibold text-blue-700 hover:text-blue-900">View all</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($upcomingAppointmentList ?? [] as $appointment)
                        <div class="px-6 py-4 flex items-center gap-4">
                            <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </path>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-800">{{ $appointment['office'] ?? 'Office Visit' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $appointment['date'] ?? '' }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $appointment['badge']['class'] ?? 'bg-green-50 text-green-700' }}">{{ $appointment['badge']['label'] ?? 'Confirmed' }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-gray-400">No upcoming appointments yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
