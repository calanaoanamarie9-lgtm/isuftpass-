@php
    $user = Auth::user();
    $role = $user->roleRule();

    $roleLabel = match (true) {
        $role === 'alumni' => 'Alumni Profile',
        $role === 'parent' => 'Parent / Guardian',
        $role === 'guest' => 'Guest Profile',
        $role === 'admin' => 'Administrator',
        $role === 'registrar' => ($user->office ?? null) ? $user->office . ' Profile' : 'Registrar Profile',
        $role === 'department' => ($user->office ?? null) ? $user->office . ' Department' : 'Department Profile',
        $role === 'office' => ($user->office ?? null) ? $user->office . ' Office' : 'Office Profile',
        $role === 'guard' => ($user->office ?? null) ? $user->office . ' Security' : 'Campus Security',
        $role === 'ssc' => 'SSC Office',
        $role === 'accounting' => 'Accounting Office',
        $role === 'library' => 'Library Office',
        $role === 'cashier' => 'Cashier Profile',
        default => 'Student Profile',
    };

    // Self-registered offices (role 'office') and any department-role account
    // whose office is not one of the four colleges use the generic workspace.
    $dept = match (true) {
        $role === 'office' => 'workspace',
        strtoupper($user->office ?? '') === 'CBMSD' => 'cbmsd',
        strtoupper($user->office ?? '') === 'COAG' => 'coag',
        strtoupper($user->office ?? '') === 'COED' => 'coed',
        strtoupper($user->office ?? '') === 'CICI' => 'cici',
        default => 'workspace',
    };

    /*
    |--------------------------------------------------------------------------
    | Navigation Helpers
    |--------------------------------------------------------------------------
    | $navClass and $sectionClass are shared by every link and heading below,
    | so restyling the sidebar only takes a change right here.
    */
    $navClass = 'group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-blue-100 hover:bg-white/10 hover:text-white transition';

    $sectionClass = 'sb-section flex items-center gap-3 px-3 mb-2.5 text-[10px] font-bold uppercase tracking-wider text-blue-300/70';
@endphp


<style>
    /* Section headings get a hairline that fades out towards the right. */
    aside .sb-section::after {
        content: "";
        flex: 1;
        height: 1px;
        background: linear-gradient(to right, rgba(255, 255, 255, .2), rgba(255, 255, 255, 0));
    }

    /*
        Current page. The script at the bottom of this file marks exactly one
        link — the most specific match — so nested pages highlight their
        parent item instead of lighting up the whole menu.
    */
    aside nav a.sb-active {
        background-color: rgba(255, 255, 255, .16);
        color: #fff;
        font-weight: 600;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .14);
    }

    aside nav a.sb-active svg {
        color: #fff;
    }
</style>


<aside
    :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-[#12347d] text-white flex flex-col
           transition-transform duration-200 shadow-xl"
>

    {{-- =========================================================
        BRAND
    ========================================================== --}}
    <div class="relative h-16 flex items-center px-5 border-b border-white/10 shrink-0 overflow-hidden">

        {{-- soft glow so the bar does not read as a flat block --}}
        <div class="absolute -top-12 -left-10 w-36 h-36
                    bg-blue-400/25
                    rounded-full blur-3xl
                    pointer-events-none"></div>

        <div class="relative flex items-center gap-3">
            <x-brand-logo size="sm" />

            <div class="leading-tight min-w-0">
                <p class="text-sm font-bold text-white tracking-wide truncate">
                    ISUFSTPASS
                </p>
                <p class="text-[10px] text-blue-300 truncate">
                    {{ $role === 'admin' ? 'Admin Panel' : 'Digital ID & Access Portal' }}
                </p>
            </div>
        </div>

    </div>


    {{-- =========================================================
        NAVIGATION
    ========================================================== --}}
    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-6
                [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">


        {{-- =====================================================
            ADMIN
        ====================================================== --}}
        @if ($role === 'admin')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Appointments Management</span>
                    </a>

                    <a href="{{ route('admin.document-requests.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <span>Document Requests</span>
                    </a>

                    <a href="{{ route('admin.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>QR Scanner &amp; Verification</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Student &amp; User Accounts</span>
                    </a>

                    <a href="{{ route('admin.audits.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Transaction History &amp; Reports</span>
                    </a>

                    <a href="{{ route('admin.settings.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>System Settings</span>
                    </a>

                </div>
            </div>

        @endif


        {{-- =====================================================
            REGISTRAR
        ====================================================== --}}
        @if ($role === 'registrar')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('registrar.document-requests.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <span>Document Requests</span>
                    </a>

                    <a href="{{ route('registrar.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Appointments</span>
                    </a>

                    <a href="{{ route('registrar.availability.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Availability</span>
                    </a>

                    <a href="{{ route('registrar.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>QR Verification</span>
                    </a>

                    <a href="{{ route('registrar.documents.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Document Fees</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('registrar.notifications.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">

                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>

                        <span class="flex-1">
                            Notifications
                        </span>

                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full
                                         bg-red-500 text-white text-[10px]
                                         font-bold flex items-center justify-center">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif

                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            STUDENT SUPPORT CENTER (SSC)
        ====================================================== --}}
        @if ($role === 'ssc')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('ssc.dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>{{ $user->officeScope() }} Dashboard</span>
                    </a>

                    <a href="{{ route('ssc.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>QR Scanner & Check-in</span>
                    </a>

                    <a href="{{ route('ssc.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Manage Appointments</span>
                    </a>

                    <a href="{{ route('ssc.consultations') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Consultation Services</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Availability</p>

                <div class="space-y-1">

                    <a href="{{ route('ssc.availability.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Availability</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('ssc.help') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Help & FAQs</span>
                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            ACCOUNTING
        ====================================================== --}}
        @if ($role === 'accounting')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('accounting.dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>{{ $user->officeScope() }} Dashboard</span>
                    </a>

                    <a href="{{ route('accounting.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>QR Scanner & Check-in</span>
                    </a>

                    <a href="{{ route('accounting.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Manage Appointments</span>
                    </a>

                    <a href="{{ route('accounting.consultations') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Consultation Services</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Availability</p>

                <div class="space-y-1">

                    <a href="{{ route('accounting.availability.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Availability</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('accounting.help') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Help & FAQs</span>
                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            LIBRARY
        ====================================================== --}}
        @if ($role === 'library')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('library.dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>{{ $user->officeScope() }} Dashboard</span>
                    </a>

                    <a href="{{ route('library.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>QR Scanner & Check-in</span>
                    </a>

                    <a href="{{ route('library.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Manage Appointments</span>
                    </a>

                    <a href="{{ route('library.consultations') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Consultation Services</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Availability</p>

                <div class="space-y-1">

                    <a href="{{ route('library.availability.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Availability</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('library.help') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Help & FAQs</span>
                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            DEPARTMENT / OFFICE WORKSPACE
        ====================================================== --}}
        @if (in_array($role, ['department', 'office'], true))

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route($dept . '.dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>{{ $user->officeScope() }} Dashboard</span>
                    </a>

                    <a href="{{ route($dept . '.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>QR Scanner & Check-in</span>
                    </a>

                    <a href="{{ route($dept . '.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Manage Appointments</span>
                    </a>

                    <a href="{{ route($dept . '.consultations') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Consultation Services</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Availability</p>

                <div class="space-y-1">

                    <a href="{{ route($dept . '.availability.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Availability</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                </div>
            </div>

        @endif



        {{-- =====================================================
            CASHIER
        ====================================================== --}}
        @if ($role === 'cashier')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('cashier.payments.pending') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Pending Payments</span>
                    </a>

                    <a href="{{ route('cashier.payments.history') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        <span>Payment History</span>
                    </a>

                    <a href="{{ route('cashier.ledger.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Student Ledger</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account &amp; Support</p>

                <div class="space-y-1">

                    <a href="{{ route('cashier.notifications.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">

                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>

                        <span class="flex-1">
                            Notifications
                        </span>

                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full
                                         bg-red-500 text-white text-[10px]
                                         font-bold flex items-center justify-center">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif

                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            GUARD / CAMPUS SECURITY
        ====================================================== --}}
        @if ($role === 'guard')

            <div>
                <p class="{{ $sectionClass }}">Gate Desk</p>

                <div class="space-y-1">

                    <a href="{{ route('guard.dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>Scanner &amp; Check-in</span>
                    </a>

                    <a href="{{ route('guard.visitors.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>Visitor Gate Passes</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account</p>

                <div class="space-y-1">

                </div>
            </div>

        @endif



        {{-- =====================================================
            ALUMNI
        ====================================================== --}}
        @if ($role === 'alumni')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('alumni.documents.new') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>New Request</span>
                    </a>

                    <a href="{{ route('alumni.documents.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span>My Requests</span>
                    </a>

                    <a href="{{ route('alumni.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>My Appointments</span>
                    </a>

                    <a href="{{ route('alumni.pass.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        <span>My Digital ID / QR Pass</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('student.notifications.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">

                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>

                        <span class="flex-1">
                            Notifications
                        </span>

                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full
                                         bg-red-500 text-white text-[10px]
                                         font-bold flex items-center justify-center">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif

                    </a>

                    <a href="{{ route('alumni.help') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Help & FAQs</span>
                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            PARENT
        ====================================================== --}}
        @if ($role === 'parent')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('student.appointments.create') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Book a Visit</span>
                    </a>

                    <a href="{{ route('student.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>My Appointments</span>
                    </a>

                    <a href="{{ route('student.pass.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        <span>My Digital ID / QR Pass</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('student.notifications.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">

                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>

                        <span class="flex-1">
                            Notifications
                        </span>

                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full
                                         bg-red-500 text-white text-[10px]
                                         font-bold flex items-center justify-center">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif

                    </a>

                    <a href="{{ route('student.help.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Help & FAQs</span>
                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            GUEST
        ====================================================== --}}
        @if ($role === 'guest')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('student.appointments.create') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Book a Visit</span>
                    </a>

                    <a href="{{ route('student.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>My Appointments</span>
                    </a>

                    <a href="{{ route('student.pass.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        <span>Visitor Pass / Entry QR</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('student.notifications.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">

                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>

                        <span class="flex-1">
                            Notifications
                        </span>

                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full
                                         bg-red-500 text-white text-[10px]
                                         font-bold flex items-center justify-center">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif

                    </a>

                    <a href="{{ route('student.help.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Help & FAQs</span>
                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            STUDENT
        ====================================================== --}}
        @if ($role === 'student')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('student.requests.new') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>New Request</span>
                    </a>

                    <a href="{{ route('student.documents.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span>My Requests</span>
                    </a>

                    <a href="{{ route('student.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>My Appointments</span>
                    </a>

                    <a href="{{ route('student.consultations.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Consultation Services</span>
                    </a>

                    <a href="{{ route('student.pass.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        <span>My Digital ID / QR Pass</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('student.notifications.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">

                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>

                        <span class="flex-1">
                            Notifications
                        </span>

                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full
                                         bg-red-500 text-white text-[10px]
                                         font-bold flex items-center justify-center">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif

                    </a>

                    <a href="{{ route('student.help.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <svg class="w-5 h-5 shrink-0 text-blue-200 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Help & FAQs</span>
                    </a>

                </div>
            </div>

        @endif

    </nav>


    {{-- =========================================================
        USER PROFILE + LOGOUT
    ========================================================== --}}
    <div class="border-t border-white/10 p-3 shrink-0">

        <a
            href="{{ route('profile.edit') }}"
            @click="open = false"
            class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-white/10 transition"
        >

            @php
                // Prefer the student-profile photo, fall back to the one
                // uploaded during registration (all user types).
                $avatar = Auth::user()->studentProfile?->avatar_url
                    ?? Auth::user()->avatar_url;
            @endphp

            <div class="w-9 h-9 rounded-full bg-blue-700
                        flex items-center justify-center
                        overflow-hidden shrink-0
                        border border-white/10">

                @if ($avatar)

                    <img
                        src="{{ $avatar }}"
                        alt="Profile Picture"
                        class="w-full h-full object-cover"
                    >

                @else

                    <span class="text-sm font-bold text-yellow-400 uppercase">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </span>

                @endif

            </div>


            <div class="min-w-0 flex-1">

                <p class="text-sm font-semibold text-white truncate">
                    {{ Auth::user()->name }}
                </p>

                <p class="text-[11px] text-blue-300 truncate">
                    {{ $roleLabel }}
                </p>

            </div>

        </a>


        {{-- Logout --}}
        <form method="POST"
              action="{{ route('logout') }}"
              class="mt-2">

            @csrf

            <button
                type="submit"
                class="w-full flex items-center justify-center gap-2
                       px-4 py-2.5 rounded-lg
                       bg-white/10 hover:bg-red-600
                       text-blue-50 hover:text-white
                       text-sm font-semibold
                       transition"
            >

                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                    />
                </svg>

                <span>Log Out</span>

            </button>

        </form>

    </div>

</aside>


{{-- =========================================================
     ACTIVE NAVIGATION MARKER
     ---------------------------------------------------------
     Server-rendered Blade cannot tell which link is "current"
     without a route name on all 86 links, so the current path
     is resolved once in the browser instead.

     The longest match wins, so /student/appointments/create
     highlights "My Appointments" rather than also lighting up
     the "Book Now" shortcut beside it.
========================================================== --}}
<script>
    (function () {
        var path = window.location.pathname.replace(/\/+$/, '') || '/';
        var links = document.querySelectorAll('aside nav a[href]');

        var best = null;
        var bestLength = -1;

        links.forEach(function (link) {
            var raw = link.getAttribute('href');
            if (!raw || raw.charAt(0) === '#') return;

            var href;
            try {
                href = new URL(raw, window.location.origin).pathname;
            } catch (e) {
                return;
            }

            href = href.replace(/\/+$/, '') || '/';
            if (href === '/') return;

            var isCurrent = path === href || path.indexOf(href + '/') === 0;

            if (isCurrent && href.length > bestLength) {
                bestLength = href.length;
                best = link;
            }
        });

        if (best) {
            best.classList.add('sb-active');
        }
    })();
</script>