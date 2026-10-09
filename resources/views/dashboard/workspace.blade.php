{{--
    Shared workspace dashboard for all eight offices and departments.

    Expected data (supplied by App\Support\WorkspaceDashboard::data()):
      $office   - display name, e.g. "Accounting" or "CICI"
      $stats    - total / pending / today / completed / services counts
      $recent   - latest six appointments for this office
      $services - published consultation services for this office
      $kind     - "Office" or "Department", passed by each thin view
--}}
@php
    $prefix = $workspacePrefix ?? strtolower($office);
@endphp

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Welcome banner --}}
        <div class="bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 rounded-2xl p-6 sm:p-8 overflow-hidden relative">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-700/40 rounded-full blur-3xl pointer-events-none"></div>

            {{-- md: rather than sm: -- the two banner buttons leave too little room
                 for the heading between 640px and 767px once they sit alongside it --}}
            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-widest text-yellow-400">
                        {{ $kind }} Workspace
                    </p>
                    <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-white">
                        {{ $office }}
                    </h1>
                    <p class="mt-2 text-sm text-blue-200 max-w-lg">
                        Manage appointments, scan student passes, and keep {{ $office }} services up to date from here.
                    </p>
                </div>

                <div class="relative shrink-0 flex flex-wrap gap-3">
                    <a href="{{ route($prefix . '.qr.index') }}"
                       class="inline-flex items-center justify-center px-5 py-3 border border-white/25 text-white text-sm font-bold rounded-xl hover:bg-white/10 transition">
                        QR Scanner
                    </a>
                    <a href="{{ route($prefix . '.appointments') }}"
                       class="inline-flex items-center justify-center px-5 py-3 bg-yellow-400 text-blue-950 text-sm font-bold rounded-xl hover:bg-yellow-300 transition shadow-lg">
                        Manage Appointments
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route($prefix . '.appointments') }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-blue-300 hover:shadow-md transition">
                <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="mt-4 text-2xl font-black text-gray-900">{{ $stats['total'] }}</p>
                <p class="text-sm text-gray-500">Total Appointments</p>
            </a>

            <a href="{{ route($prefix . '.appointments') }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-red-300 hover:shadow-md transition">
                <div class="w-11 h-11 bg-red-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="mt-4 text-2xl font-black text-gray-900">{{ $stats['pending'] }}</p>
                <p class="text-sm text-gray-500">Awaiting Action</p>
            </a>

            <a href="{{ route($prefix . '.appointments') }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-green-300 hover:shadow-md transition">
                <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="mt-4 text-2xl font-black text-gray-900">{{ $stats['today'] }}</p>
                <p class="text-sm text-gray-500">Scheduled Today</p>
            </a>

            <a href="{{ route($prefix . '.consultations') }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-purple-300 hover:shadow-md transition">
                <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="mt-4 text-2xl font-black text-gray-900">{{ $stats['services'] }}</p>
                <p class="text-sm text-gray-500">Consultation Services</p>
            </a>
        </div>

        {{-- Recent appointments + workspace tools --}}
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <h2 class="font-bold text-gray-900">Recent Appointments</h2>
                    <a href="{{ route($prefix . '.appointments') }}"
                       class="text-xs font-bold text-blue-700 hover:text-blue-900 transition">View all</a>
                </div>

                @forelse ($recent as $appointment)
                    <div class="px-5 py-4 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 shrink-0 rounded-full bg-blue-50 flex items-center justify-center text-blue-700 font-bold text-xs">
                                {{ strtoupper(substr($appointment->user?->name ?? 'ST', 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 text-sm truncate">{{ $appointment->user?->name ?? 'Student' }}</p>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $appointment->purpose }} &middot; {{ $appointment->date->format('M j, Y') }} &middot; {{ $appointment->timeToCome() }}
                                </p>
                            </div>
                            <span class="shrink-0 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full {{ match($appointment->status) {
                                'pending' => 'bg-red-50 text-red-700',
                                'confirmed' => 'bg-green-50 text-green-700',
                                'completed' => 'bg-blue-50 text-blue-700',
                                default => 'bg-gray-50 text-gray-500',
                            } }}">{{ ucfirst($appointment->status) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-gray-400">
                        No appointments yet for {{ $office }}.
                    </div>
                @endforelse
            </div>

            <div class="space-y-6">
                {{-- Quick actions --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="font-bold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-4 grid grid-cols-2 gap-3">
                        <a href="{{ route($prefix . '.appointments') }}"
                           class="rounded-xl bg-blue-50 hover:bg-blue-100 transition p-3 text-center">
                            <p class="text-lg leading-none">📅</p>
                            <p class="mt-2 text-xs font-bold text-blue-800">Appointments</p>
                        </a>
                        <a href="{{ route($prefix . '.qr.index') }}"
                           class="rounded-xl bg-green-50 hover:bg-green-100 transition p-3 text-center">
                            <p class="text-lg leading-none">📷</p>
                            <p class="mt-2 text-xs font-bold text-green-800">QR Scanner</p>
                        </a>
                        <a href="{{ route($prefix . '.availability') }}"
                           class="rounded-xl bg-yellow-50 hover:bg-yellow-100 transition p-3 text-center">
                            <p class="text-lg leading-none">🕒</p>
                            <p class="mt-2 text-xs font-bold text-yellow-800">Availability</p>
                        </a>
                        <a href="{{ route($prefix . '.consultations') }}"
                           class="rounded-xl bg-purple-50 hover:bg-purple-100 transition p-3 text-center">
                            <p class="text-lg leading-none">📋</p>
                            <p class="mt-2 text-xs font-bold text-purple-800">Consultations</p>
                        </a>
                        <a href="{{ route($prefix . '.profile') }}"
                           class="rounded-xl bg-gray-50 hover:bg-gray-100 transition p-3 text-center">
                            <p class="text-lg leading-none">⚙️</p>
                            <p class="mt-2 text-xs font-bold text-gray-700">Profile</p>
                        </a>
                        <a href="{{ route($prefix . '.help') }}"
                           class="rounded-xl bg-red-50 hover:bg-red-100 transition p-3 text-center">
                            <p class="text-lg leading-none">❓</p>
                            <p class="mt-2 text-xs font-bold text-red-700">Help</p>
                        </a>
                    </div>
                </div>

                {{-- Published services --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                        <h2 class="font-bold text-gray-900">Services Offered</h2>
                        <a href="{{ route($prefix . '.consultations') }}"
                           class="text-xs font-bold text-blue-700 hover:text-blue-900 transition">Manage</a>
                    </div>

                    @forelse ($services->take(4) as $service)
                        <div class="px-5 py-3 border-b border-gray-50 last:border-0 flex items-center gap-3">
                            <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-blue-600"></span>
                            <p class="text-sm text-gray-600 min-w-0 truncate">{{ $service->name }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-400">
                            No services published yet.
                        </div>
                    @endforelse

                    @if ($services->count() > 4)
                        <div class="px-5 py-3 border-t border-gray-100 text-center">
                            <a href="{{ route($prefix . '.consultations') }}"
                               class="text-xs font-bold text-blue-700 hover:text-blue-900 transition">
                                View all {{ $services->count() }} services
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
