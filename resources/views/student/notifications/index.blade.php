@php
    $notifRoute = match (true) {
        Auth::user()->isStudent() => 'student.notifications.',
        Auth::user()->isCashier() => 'cashier.notifications.',
        default => 'registrar.notifications.',
    };
@endphp

<x-app-layout>
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Notifications</h1>
                    <p class="text-sm text-gray-500 mt-1">Email &amp; system alerts for your requests and appointments.</p>
                </div>

                @if ($notifications->total() > 0)
                    <form method="POST" action="{{ route($notifRoute . 'readAll') }}">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                class="text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 ring-1 ring-blue-100 px-3 py-2 rounded-lg transition">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>

            <div class="mt-8 space-y-3">
                @forelse ($notifications as $notification)
                    @php $icon = str_contains($notification->type, 'Appointment') ? '📅' : '📂' @endphp

                    <a href="{{ route($notifRoute . 'open', $notification) }}"
                       class="block bg-white rounded-2xl border shadow-sm p-5 transition hover:border-blue-300 hover:shadow-md
                              {{ $notification->read_at ? 'border-gray-100 opacity-75' : 'border-blue-200 bg-blue-50/40' }}">

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-700 flex items-center justify-center text-lg shrink-0">
                                {{ $icon }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-gray-900 text-sm">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                    @if (! $notification->read_at)
                                        <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mt-0.5">{{ $notification->data['message'] ?? '' }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>

                            <span class="shrink-0 text-xs font-semibold text-blue-700">
                                View →
                            </span>
                        </div>

                    </a>
                @empty
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                        <p class="text-3xl">🔔</p>
                        <p class="mt-2 font-semibold text-gray-700">No notifications yet</p>
                        <p class="text-sm text-gray-400 mt-1">Alerts for your appointments and document requests will appear here.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>