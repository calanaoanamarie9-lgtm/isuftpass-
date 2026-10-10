{{--
    Header notification bell for every role with an in-app inbox
    (student, registrar/department, cashier).

    Shows the unread count on the icon and drops down the latest alerts;
    each row opens that notification's own target, and the footer links
    to the full notifications page. Renders on both the dark mobile bar
    (currentColor inherits) and the light desktop bar.
--}}
@auth
    @php
        $bellUser = Auth::user();
        $bellRoute = match (true) {
            $bellUser->isStudent() => 'student.notifications.',
            $bellUser->isCashier() => 'cashier.notifications.',
            default => 'registrar.notifications.',
        };
        $bellUnread = $bellUser->unreadNotifications()->count();
        $bellLatest = $bellUser->notifications()->latest()->limit(6)->get();
    @endphp

    @if (in_array($bellUser->role, ['student', 'registrar', 'department', 'cashier'], true))

        <div class="relative"
             x-data="{ bellOpen: false }"
             @click.away="bellOpen = false">

            <button type="button"
                    @click="bellOpen = ! bellOpen"
                    class="relative p-2 rounded-full transition
                           hover:opacity-70 focus:outline-none
                           focus-visible:ring-2 focus-visible:ring-blue-400"
                    aria-label="Notifications">

                <svg class="w-6 h-6"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>

                </svg>

                @if ($bellUnread > 0)

                    <span class="absolute -top-0.5 -right-0.5
                                 min-w-[18px] h-[18px] px-1
                                 rounded-full bg-red-500 text-white
                                 text-[10px] font-bold
                                 flex items-center justify-center">
                        {{ $bellUnread > 9 ? '9+' : $bellUnread }}
                    </span>

                @endif

            </button>


            <div x-show="bellOpen"
                 x-cloak
                 class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)]
                        bg-white rounded-xl shadow-lg ring-1 ring-black/5
                        z-50 overflow-hidden">

                <div class="px-4 py-3 border-b border-gray-100
                            flex items-center justify-between">

                    <p class="text-sm font-extrabold text-gray-900">
                        Notifications
                    </p>

                    @if ($bellUnread > 0)

                        <span class="text-[10px] font-bold px-2 py-0.5
                                     rounded-full bg-blue-50 text-blue-700">
                            {{ $bellUnread }} new
                        </span>

                    @endif

                </div>

                <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">

                    @forelse ($bellLatest as $bellItem)

                        <a href="{{ route($bellRoute . 'open', $bellItem) }}"
                           class="block px-4 py-3 transition hover:bg-gray-50">

                            <p class="text-xs font-bold text-gray-900">
                                {{ $bellItem->data['title'] ?? 'Notification' }}
                            </p>

                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                                {{ $bellItem->data['message'] ?? '' }}
                            </p>

                            <p class="text-[10px] text-gray-400 mt-1">
                                {{ $bellItem->created_at->diffForHumans() }}
                            </p>

                        </a>

                    @empty

                        <p class="px-4 py-6 text-center text-xs text-gray-400">
                            No notifications yet.
                        </p>

                    @endforelse

                </div>

                <a href="{{ route($bellRoute . 'index') }}"
                   class="block px-4 py-2.5 text-center text-xs font-bold
                          text-blue-700 border-t border-gray-100
                          transition hover:bg-blue-50">
                    View all notifications
                </a>

            </div>

        </div>

    @endif
@endauth
