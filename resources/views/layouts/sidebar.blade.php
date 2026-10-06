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
        $role === 'osas' => 'OSAS Office',
        $role === 'accounting' => 'Accounting Office',
        $role === 'library' => 'Library Office',
        $role === 'guidance' => 'Guidance Office',
        $role === 'cashier' => 'Cashier Profile',
        default => 'Student Profile',
    };

    $dept = match (strtoupper($user->office ?? '')) {
        'CBMSD' => 'cbmsd',
        'COAG' => 'coag',
        'COED' => 'coed',
        default => 'cici',
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
            <div class="w-9 h-9 flex items-center justify-center shrink-0">
                <img
                    src="{{ asset('img/isufstpass-logo.png') }}"
                    alt="ISUFSTPASS"
                    class="w-9 h-9 object-contain"
                >
            </div>

            <div class="leading-tight min-w-0">
                <p class="text-sm font-bold text-white tracking-wide truncate">
                    ISUFSTPASS
                </p>
                <p class="text-[10px] text-blue-300 truncate">
                    Digital ID &amp; Access Portal
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
                        <span class="text-base">📊</span>
                        <span>System Dashboard</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span class="text-base">👥</span>
                        <span>User Management</span>
                    </a>

                    <a href="{{ route('admin.offices.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span class="text-base">🏢</span>
                        <span>Department Settings</span>
                    </a>

                    <a href="{{ route('admin.slots.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span class="text-base">🕐</span>
                        <span>Slot Availability</span>
                    </a>

                    <a href="{{ route('admin.settings.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span class="text-base">⚙️</span>
                        <span>System Documentation</span>
                    </a>

                    <a href="{{ route('admin.audits.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span class="text-base">📋</span>
                        <span>Master Logs & Audits</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('admin.announcements.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span class="text-base">🔔</span>
                        <span>Notifications</span>
                    </a>

                    <a href="{{ route('profile.edit') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span class="text-base">⚙️</span>
                        <span>Profile Settings</span>
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
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('registrar.document-requests.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📥</span>
                        <span>Document Requests</span>
                    </a>

                    <a href="{{ route('registrar.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>Appointments</span>
                    </a>

                    <a href="{{ route('registrar.availability.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🗓️</span>
                        <span>Availability</span>
                    </a>

                    <a href="{{ route('registrar.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🔲</span>
                        <span>QR Verification</span>
                    </a>

                    <a href="{{ route('registrar.documents.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>💰</span>
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

                        <span>🔔</span>

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

                    <a href="{{ route('profile.edit') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            OSAS
        ====================================================== --}}
        @if ($role === 'osas')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('osas.dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📊</span>
                        <span>{{ $user->officeScope() }} Dashboard</span>
                    </a>

                    <a href="{{ route('osas.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📷</span>
                        <span>QR Scanner & Check-in</span>
                    </a>

                    <a href="{{ route('osas.appointments') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>Manage Appointments</span>
                    </a>

                    <a href="{{ route('osas.consultations') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📋</span>
                        <span>Consultation Services</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Availability</p>

                <div class="space-y-1">

                    <a href="{{ route('osas.availability') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🕒</span>
                        <span>Time Slot Settings</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('osas.profile') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

                    <a href="{{ route('osas.help') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>❓</span>
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
                        <span>📊</span>
                        <span>{{ $user->officeScope() }} Dashboard</span>
                    </a>

                    <a href="{{ route('accounting.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📷</span>
                        <span>QR Scanner & Check-in</span>
                    </a>

                    <a href="{{ route('accounting.appointments') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>Manage Appointments</span>
                    </a>

                    <a href="{{ route('accounting.consultations') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📋</span>
                        <span>Consultation Services</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Availability</p>

                <div class="space-y-1">

                    <a href="{{ route('accounting.availability') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🕒</span>
                        <span>Time Slot Settings</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('accounting.profile') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

                    <a href="{{ route('accounting.help') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>❓</span>
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
                        <span>📊</span>
                        <span>{{ $user->officeScope() }} Dashboard</span>
                    </a>

                    <a href="{{ route('library.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📷</span>
                        <span>QR Scanner & Check-in</span>
                    </a>

                    <a href="{{ route('library.appointments') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>Manage Appointments</span>
                    </a>

                    <a href="{{ route('library.consultations') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📋</span>
                        <span>Consultation Services</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Availability</p>

                <div class="space-y-1">

                    <a href="{{ route('library.availability') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🕒</span>
                        <span>Time Slot Settings</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('library.profile') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

                    <a href="{{ route('library.help') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>❓</span>
                        <span>Help & FAQs</span>
                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            GUIDANCE
        ====================================================== --}}
        @if ($role === 'guidance')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route('guidance.dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📊</span>
                        <span>{{ $user->officeScope() }} Dashboard</span>
                    </a>

                    <a href="{{ route('guidance.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📷</span>
                        <span>QR Scanner & Check-in</span>
                    </a>

                    <a href="{{ route('guidance.appointments') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>Manage Appointments</span>
                    </a>

                    <a href="{{ route('guidance.consultations') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📋</span>
                        <span>Consultation Services</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Availability</p>

                <div class="space-y-1">

                    <a href="{{ route('guidance.availability') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🕒</span>
                        <span>Time Slot Settings</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route('guidance.profile') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

                    <a href="{{ route('guidance.help') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>❓</span>
                        <span>Help & FAQs</span>
                    </a>

                </div>
            </div>

        @endif



        {{-- =====================================================
            DEPARTMENT
        ====================================================== --}}
        @if ($role === 'department')

            <div>
                <p class="{{ $sectionClass }}">Main Menu</p>

                <div class="space-y-1">

                    <a href="{{ route($dept . '.dashboard') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📊</span>
                        <span>{{ $user->officeScope() }} Dashboard</span>
                    </a>

                    <a href="{{ route($dept . '.qr.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📷</span>
                        <span>QR Scanner & Check-in</span>
                    </a>

                    <a href="{{ route($dept . '.appointments') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>Manage Appointments</span>
                    </a>

                    <a href="{{ route($dept . '.consultations') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📋</span>
                        <span>Consultation Services</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Availability</p>

                <div class="space-y-1">

                    <a href="{{ route($dept . '.availability') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🕒</span>
                        <span>Time Slot Settings</span>
                    </a>

                </div>
            </div>


            <div>
                <p class="{{ $sectionClass }}">Account & Support</p>

                <div class="space-y-1">

                    <a href="{{ route($dept . '.profile') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

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
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('cashier.payments.pending') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>💳</span>
                        <span>Pending Payments</span>
                    </a>

                    <a href="{{ route('cashier.payments.history') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🧾</span>
                        <span>Payment History</span>
                    </a>

                    <a href="{{ route('cashier.ledger.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🔍</span>
                        <span>Student Ledger</span>
                    </a>

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
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('alumni.documents.new') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📄</span>
                        <span>New Request</span>
                    </a>

                    <a href="{{ route('alumni.documents.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📁</span>
                        <span>My Requests</span>
                    </a>

                    <a href="{{ route('alumni.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>My Appointments</span>
                    </a>

                    <a href="{{ route('alumni.pass.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🔲</span>
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

                        <span>🔔</span>

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

                    <a href="{{ route('alumni.profile.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

                    <a href="{{ route('alumni.help') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>❓</span>
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
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('student.appointments.create') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>Book a Visit</span>
                    </a>

                    <a href="{{ route('student.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>My Appointments</span>
                    </a>

                    <a href="{{ route('student.pass.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🔲</span>
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

                        <span>🔔</span>

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

                    <a href="{{ route('student.profile.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

                    <a href="{{ route('student.help.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>❓</span>
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
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('student.appointments.create') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>Book a Visit</span>
                    </a>

                    <a href="{{ route('student.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>My Appointments</span>
                    </a>

                    <a href="{{ route('student.pass.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🔲</span>
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

                        <span>🔔</span>

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

                    <a href="{{ route('student.profile.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

                    <a href="{{ route('student.help.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>❓</span>
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
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('student.requests.new') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📄</span>
                        <span>New Request</span>
                    </a>

                    <a href="{{ route('student.documents.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📁</span>
                        <span>My Requests</span>
                    </a>

                    <a href="{{ route('student.appointments.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>📅</span>
                        <span>My Appointments</span>
                    </a>

                    <a href="{{ route('student.consultations.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>💬</span>
                        <span>Consultation Services</span>
                    </a>

                    <a href="{{ route('student.pass.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>🔲</span>
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

                        <span>🔔</span>

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

                    <a href="{{ route('student.profile.show') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>⚙️</span>
                        <span>Profile Settings</span>
                    </a>

                    <a href="{{ route('student.help.index') }}"
                       @click="open = false"
                       class="{{ $navClass }}">
                        <span>❓</span>
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
            href="{{
                $role === 'alumni'
                    ? route('alumni.profile.show')
                    : (
                        in_array($role, ['student', 'parent', 'guest'], true)
                            ? route('student.profile.show')
                            : route('profile.edit')
                    )
            }}"
            @click="open = false"
            class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-white/10 transition"
        >

            @php
                $avatar = Auth::user()->studentProfile?->avatar;
            @endphp

            <div class="w-9 h-9 rounded-full bg-blue-700
                        flex items-center justify-center
                        overflow-hidden shrink-0
                        border border-white/10">

                @if ($avatar)

                    <img
                        src="{{ Storage::url($avatar) }}"
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