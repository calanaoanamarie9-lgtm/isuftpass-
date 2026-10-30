<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Welcome Banner --}}
            <div class="bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 overflow-hidden relative">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-700/40 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative">
                    <p class="text-xs font-semibold uppercase tracking-widest text-yellow-400">
                        Student Dashboard
                    </p>
                    <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-white">
                        Welcome, {{ Auth::user()->name }}!
                    </h1>
                    <p class="mt-2 text-sm text-blue-200 max-w-lg">
                        Book appointments, request documents, and access your QR pass from here.
                    </p>
                </div>
                <div class="relative shrink-0">
                    <a href="{{ route('student.requests.new') }}"
                       class="inline-flex items-center justify-center px-6 py-3 bg-yellow-400 text-blue-950 font-bold rounded-xl hover:bg-yellow-300 transition shadow-lg">
                        New Request
                    </a>
                </div>
            </div>

            @if ($bannerEnabled && $bannerText)
                <div class="mt-6 rounded-2xl border border-yellow-300 bg-yellow-50 px-5 py-4 text-sm text-yellow-800 flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 text-yellow-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46"/></svg>
                    <p>{{ $bannerText }}</p>
                </div>
            @endif

            {{-- Stats --}}
            <div class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-4">

                <a href="{{ route('student.documents.index') }}" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-blue-300 hover:shadow-md transition">
                    <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-2xl font-black text-gray-900">{{ $activeDocumentRequests }}</p>
                    <p class="text-sm text-gray-500">Active Requests</p>
                </a>

                <a href="{{ route('student.appointments.index') }}" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-blue-300 hover:shadow-md transition">
                    <div class="w-11 h-11 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-2xl font-black text-gray-900">{{ $upcomingAppointments }}</p>
                    <p class="text-sm text-gray-500">Upcoming Appointments</p>
                </a>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <div class="w-11 h-11 bg-yellow-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-2xl font-black text-gray-900">{{ $completedTransactions }}</p>
                    <p class="text-sm text-gray-500">Completed</p>
                </div>

                <a href="{{ route('student.pass.show') }}" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:border-blue-300 hover:shadow-md transition">
                    <div class="w-11 h-11 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-2xl font-black text-gray-900">{{ $hasPass ? 1 : 0 }}</p>
                    <p class="text-sm text-gray-500">QR Pass Status</p>
                </a>

            </div>

            {{-- Main Grid --}}
            <div class="mt-8 grid lg:grid-cols-3 gap-6">

                {{-- Recent Transactions --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-bold text-gray-900">Recent Activity</h2>
                        <div class="flex gap-4">
                            <a href="{{ route('student.appointments.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900 transition">Appointments</a>
                            <a href="{{ route('student.documents.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900 transition">Requests</a>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse ($recentActivity as $activity)
                            <div class="px-6 py-4 flex items-center gap-4">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center {{ $activity['type'] === 'appointment' ? 'bg-green-50' : 'bg-blue-50' }}">
                                    <svg class="w-5 h-5 {{ $activity['type'] === 'appointment' ? 'text-green-600' : 'text-blue-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($activity['type'] === 'appointment')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>
                                        @endif
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $activity['title'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $activity['date']->format('M j, Y g:i A') }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $activity['badge']['class'] }}">{{ $activity['badge']['label'] }}</span>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center text-sm text-gray-400">No recent activity yet.</div>
                        @endforelse
                    </div>
                </div>

                {{-- QR Pass Card --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="font-bold text-gray-900">My QR Pass</h2>
                    <p class="text-xs text-gray-500 mt-1">Present this code for transaction verification and gate access</p>

                    <div class="mt-5 bg-slate-50 rounded-2xl p-5 flex justify-center">
                        <div class="w-44 h-44 bg-white border-8 border-slate-100 rounded-xl flex items-center justify-center shadow-inner">
                            <svg viewBox="0 0 100 100" class="w-36 h-36 text-blue-950">
                                <path fill="currentColor"
                                      d="M5 5h35v35H5V5zm5 5v25h25V10H10zm5 5h15v15H15V15z
                                         M60 5h35v35H60V5zm5 5v25h25V10H65zm5 5h15v15H70V15z
                                         M5 60h35v35H5V60zm5 5v25h25V65H10zm5 5h15v15H15V70z
                                         M50 50h10v10H50V50zm15 0h10v10H65V50zm15 0h15v10H80V50z
                                         M50 65h10v15H50V65zm15 10h10v20H65V75zm15-10h15v10H80V65z
                                         M50 85h10v10H50V85zm30 0h15v10H80V85z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between bg-green-50 border border-green-100 rounded-xl px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
                            <span class="text-sm font-semibold text-green-700">Verified Pass</span>
                        </div>
                        <span class="text-xs text-green-600">{{ $hasPass ? 'Active' : 'Inactive' }}</span>
                    </div>

                    <a href="{{ route('student.pass.show') }}"
                       class="mt-5 block text-center px-4 py-2.5 rounded-xl text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 transition">
                        View My QR Code
                    </a>
                </div>

            </div>

            {{-- Quick Actions --}}
            <div class="mt-8 grid sm:grid-cols-3 gap-4">

                <a href="{{ route('student.requests.new') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4 hover:border-blue-300 hover:shadow-md transition">
                    <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">New Request</p>
                        <p class="text-xs text-gray-500 mt-0.5">Book an appointment or request a document</p>
                    </div>
                </a>

                <a href="{{ route('student.documents.index') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4 hover:border-blue-300 hover:shadow-md transition">
                    <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">My Requests</p>
                        <p class="text-xs text-gray-500 mt-0.5">Track document request status</p>
                    </div>
                </a>

                <a href="{{ route('student.appointments.index') }}"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4 hover:border-blue-300 hover:shadow-md transition">
                    <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">My Appointments</p>
                        <p class="text-xs text-gray-500 mt-0.5">Manage upcoming office visits</p>
                    </div>
                </a>

            </div>

        </div>
    </div>
</x-app-layout>