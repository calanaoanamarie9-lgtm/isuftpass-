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
                    <a href="{{ route($prefix . '.appointments.index') }}"
                       class="inline-flex items-center justify-center px-5 py-3 bg-yellow-400 text-blue-950 text-sm font-bold rounded-xl hover:bg-yellow-300 transition shadow-lg">
                        Manage Appointments
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route($prefix . '.appointments.index') }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-blue-300 hover:shadow-md transition">
                <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="mt-4 text-2xl font-black text-gray-900">{{ $stats['total'] }}</p>
                <p class="text-sm text-gray-500">Total Appointments</p>
            </a>

            <a href="{{ route($prefix . '.appointments.index') }}"
               class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-red-300 hover:shadow-md transition">
                <div class="w-11 h-11 bg-red-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="mt-4 text-2xl font-black text-gray-900">{{ $stats['pending'] }}</p>
                <p class="text-sm text-gray-500">Awaiting Action</p>
            </a>

            <a href="{{ route($prefix . '.appointments.index') }}"
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
                    <a href="{{ route($prefix . '.appointments.index') }}"
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
                        <a href="{{ route($prefix . '.appointments.index') }}"
                           class="rounded-xl bg-blue-50 hover:bg-blue-100 transition p-3 text-center">
                            <svg class="w-5 h-5 mx-auto text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                            <p class="mt-2 text-xs font-bold text-blue-800">Appointments</p>
                        </a>
                        <a href="{{ route($prefix . '.qr.index') }}"
                           class="rounded-xl bg-green-50 hover:bg-green-100 transition p-3 text-center">
                            <svg class="w-5 h-5 mx-auto text-green-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"/></svg>
                            <p class="mt-2 text-xs font-bold text-green-800">QR Scanner</p>
                        </a>
                        <a href="{{ route($prefix . '.availability.index') }}"
                           class="rounded-xl bg-yellow-50 hover:bg-yellow-100 transition p-3 text-center">
                            <svg class="w-5 h-5 mx-auto text-yellow-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <p class="mt-2 text-xs font-bold text-yellow-800">Availability</p>
                        </a>
                        <a href="{{ route($prefix . '.consultations') }}"
                           class="rounded-xl bg-purple-50 hover:bg-purple-100 transition p-3 text-center">
                            <svg class="w-5 h-5 mx-auto text-purple-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Zm0 0v.75m9-9v.75m-9 3h.008M9.75 9h.008M9.75 12h.008M9.75 15h.008M14.25 9h.008M14.25 12h.008M14.25 15h.008"/></svg>
                            <p class="mt-2 text-xs font-bold text-purple-800">Consultations</p>
                        </a>
                        <a href="{{ route($prefix . '.profile') }}"
                           class="rounded-xl bg-gray-50 hover:bg-gray-100 transition p-3 text-center">
                            <svg class="w-5 h-5 mx-auto text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <p class="mt-2 text-xs font-bold text-gray-700">Profile</p>
                        </a>
                        <a href="{{ route($prefix . '.help') }}"
                           class="rounded-xl bg-red-50 hover:bg-red-100 transition p-3 text-center">
                            <svg class="w-5 h-5 mx-auto text-red-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/></svg>
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
