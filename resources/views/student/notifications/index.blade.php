@php
    /*
    |--------------------------------------------------------------------------
    | Which route prefix this account's portal uses
    |--------------------------------------------------------------------------
    | The page is shared by every role, but the forms on it post to
    | route names, so the prefix must match the sidebar entry that led
    | here: students (and alumni/parent/guest), cashiers, registrars,
    | admins, guards, the standalone ssc/accounting/library roles, and
    | the department / office workspaces.
    */
    $user = Auth::user();
    $role = $user->roleRule();

    $notifRoute = match (true) {
        $user->isStudent() => 'student.notifications.',
        $user->isCashier() => 'cashier.notifications.',
        $user->isRegistrar() => 'registrar.notifications.',
        $role === 'admin' => 'admin.notifications.',
        $role === 'guard' => 'guard.notifications.',
        $role === 'ssc' => 'ssc.notifications.',
        $role === 'accounting' => 'accounting.notifications.',
        $role === 'library' => 'library.notifications.',
        // Department / office workspaces reuse the sidebar's own prefix
        // choice, so the link here and the link there agree.
        default => match (true) {
            $role === 'office' => 'workspace',
            strtoupper($user->office ?? '') === 'CBMSD' => 'cbmsd',
            strtoupper($user->office ?? '') === 'COAG' => 'coag',
            strtoupper($user->office ?? '') === 'COED' => 'coed',
            strtoupper($user->office ?? '') === 'CICI' => 'cici',
            default => 'workspace',
        } . '.notifications.',
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
                    @php $isAppointment = str_contains($notification->type, 'Appointment'); @endphp

                    <a href="{{ route($notifRoute . 'open', $notification) }}"
                       class="block bg-white rounded-2xl border shadow-sm p-5 transition hover:border-blue-300 hover:shadow-md
                              {{ $notification->read_at ? 'border-gray-100 opacity-75' : 'border-blue-200 bg-blue-50/40' }}">

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-700 flex items-center justify-center shrink-0">
                                @if ($isAppointment)
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                @else
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                                @endif
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
                        <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
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