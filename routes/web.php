<?php

use App\Enums\AppointmentStatus;
use App\Enums\DocumentRequestStatus;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\SlotCapacityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Cashier\LedgerController;
use App\Http\Controllers\Cashier\PaymentController as CashierPaymentController;
use App\Http\Controllers\Consultation\ConsultationServiceController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\Department\CbmsdController;
use App\Http\Controllers\Department\CiciController;
use App\Http\Controllers\Department\CoagController;
use App\Http\Controllers\Department\CoedController;
use App\Http\Controllers\Offices\AccountingController;
use App\Http\Controllers\Offices\AlumniController;
use App\Http\Controllers\Offices\GuidanceController;
use App\Http\Controllers\Offices\LibraryController;
use App\Http\Controllers\Offices\OsasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Registrar\AppointmentController as RegistrarAppointmentController;
use App\Http\Controllers\Registrar\AvailabilityController;
use App\Http\Controllers\Registrar\DocumentController as RegistrarDocumentController;
use App\Http\Controllers\Registrar\DocumentRequestController as RegistrarDocumentRequestController;
use App\Http\Controllers\Registrar\QrVerifyController;
use App\Http\Controllers\Registrar\StudentLookupController;
use App\Http\Controllers\Student\AnnouncementController;
use App\Http\Controllers\Student\AppointmentController;
use App\Http\Controllers\Student\ConsultationCatalogController;
use App\Http\Controllers\Student\DocumentRequestController;
use App\Http\Controllers\Student\FeedbackController;
use App\Http\Controllers\Student\HelpController;
use App\Http\Controllers\Student\NotificationController;
use App\Http\Controllers\Student\PassController;
use App\Http\Controllers\Student\RequestController;
use App\Http\Controllers\VerifyController;
use App\Http\Controllers\Workspace\OfficeWorkspaceController;
use App\Http\Controllers\Workspace\QrCheckInController;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\GateLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/cron/reminders', [CronController::class, 'reminders']);

Route::get('/', function () {
    $settings = [
        'name' => 'Iloilo State University of Fisheries Science and Technology',
        'address' => 'San Matias, Dingle, Iloilo',
        'academic_term' => 'Academic Year 2026-2027',
        'email' => 'dingleisufst@isufst.edu.ph',
        'phone' => '(033) 555-1234',
    ];

    try {
        $settings = [
            'name' => Setting::get('institution_name') ?? $settings['name'],
            'address' => Setting::get('institution_address') ?? $settings['address'],
            'academic_term' => Setting::get('academic_term') ?? $settings['academic_term'],
            'email' => Setting::get('support_email') ?? $settings['email'],
            'phone' => Setting::get('support_phone') ?? $settings['phone'],
        ];
    } catch (Throwable) {
    }

    return view('welcome', ['institution' => $settings]);
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    // The personal-details step is not optional for anyone who signed up
    // through the registration flow: until they submit it they loop back to
    // the form, whatever the typed registration. Staff accounts are seeded
    // or admin-created and never saw a registration form — the step does
    // not exist for them, so they go straight through.
    if ($user->requiresProfileCompletion()) {
        return redirect()->route('complete-profile');
    }

    if ($user->registration_type === 'alumni') {
        $recentActivity = collect()
            ->merge(
                $user->appointments()->latest()->limit(3)->get()->map(fn (Appointment $a) => [
                    'type' => 'appointment',
                    'title' => 'Appointment with '.$a->office,
                    'date' => $a->created_at,
                    'badge' => match ($a->status) {
                        'confirmed' => ['label' => 'Confirmed', 'class' => 'bg-green-50 text-green-700'],
                        'completed' => ['label' => 'Completed', 'class' => 'bg-blue-50 text-blue-700'],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-red-50 text-red-600'],
                        default => ['label' => 'Pending', 'class' => 'bg-yellow-50 text-yellow-700'],
                    },
                ])
            )
            ->merge(
                $user->documentRequests()->latest()->limit(3)->get()->map(fn (DocumentRequest $d) => [
                    'type' => 'document',
                    'title' => $d->documentsSummary(),
                    'date' => $d->created_at,
                    'badge' => ['label' => ucwords(str_replace('_', ' ', $d->status)), 'class' => 'bg-blue-50 text-blue-700'],
                ])
            )
            ->filter(fn ($activity) => $activity['date'])
            ->sortByDesc('date')
            ->take(5)
            ->values();

        return view('alumni.dashboard', [
            'activeDocumentRequests' => $user->documentRequests()->active()->count(),
            'upcomingAppointments' => $user->appointments()->upcoming()->count(),
            'completedTransactions' => $user->documentRequests()
                ->whereIn('status', ['completed'])
                ->count() + $user->appointments()->where('status', AppointmentStatus::COMPLETED->value)->count(),
            'hasPass' => (bool) $user->studentProfile?->pass_token,
            'recentActivity' => $recentActivity,
            'bannerEnabled' => Setting::get('banner_enabled') === 'true',
            'bannerText' => Setting::get('banner_text'),
        ]);
    }

    if ($user->registration_type === 'parent' || $user->registration_type === 'guardian') {
        $recentActivity = $user->appointments()->latest()->limit(3)->get()
            ->map(fn (Appointment $a) => [
                'type' => 'appointment',
                'title' => 'Visit with '.$a->office,
                'date' => $a->created_at,
                'badge' => match ($a->status) {
                    'confirmed' => ['label' => 'Confirmed', 'class' => 'bg-green-50 text-green-700'],
                    'completed' => ['label' => 'Completed', 'class' => 'bg-blue-50 text-blue-700'],
                    'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-red-50 text-red-600'],
                    default => ['label' => 'Pending', 'class' => 'bg-yellow-50 text-yellow-700'],
                },
            ]);

        return view('parent.dashboard', [
            'upcomingAppointments' => $user->appointments()->upcoming()->count(),
            'completedAppointments' => $user->appointments()->where('status', 'completed')->count(),
            'recentActivity' => $recentActivity,
            'hasPass' => false,
        ]);
    }

    if ($user->registration_type === 'guest') {
        $recentActivity = $user->appointments()->latest()->limit(3)->get()
            ->map(fn (Appointment $a) => [
                'type' => 'appointment',
                'title' => 'Visit with '.$a->office,
                'date' => $a->created_at,
                'badge' => match ($a->status) {
                    'confirmed' => ['label' => 'Confirmed', 'class' => 'bg-green-50 text-green-700'],
                    'completed' => ['label' => 'Completed', 'class' => 'bg-blue-50 text-blue-700'],
                    'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-red-50 text-red-600'],
                    default => ['label' => 'Pending', 'class' => 'bg-yellow-50 text-yellow-700'],
                },
            ]);

        return view('guest.dashboard', [
            'upcomingAppointments' => $user->appointments()->upcoming()->count(),
            'completedAppointments' => $user->appointments()->where('status', 'completed')->count(),
            'recentActivity' => $recentActivity,
            'hasPass' => false,
        ]);
    }

    if ($user->isStudent()) {
        $profile = $user->studentProfile ?? $user->studentProfile()->create();

        $recentActivity = collect()
            ->merge(
                $user->appointments()->latest()->limit(3)->get()->map(fn (Appointment $a) => [
                    'type' => 'appointment',
                    'title' => 'Appointment with '.$a->office,
                    'date' => $a->created_at,
                    'badge' => match ($a->status) {
                        'confirmed' => ['label' => 'Confirmed', 'class' => 'bg-green-50 text-green-700'],
                        'completed' => ['label' => 'Completed', 'class' => 'bg-blue-50 text-blue-700'],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-red-50 text-red-600'],
                        default => ['label' => 'Pending', 'class' => 'bg-yellow-50 text-yellow-700'],
                    },
                ])
            )
            ->merge(
                $user->documentRequests()->latest()->limit(3)->get()->map(fn (DocumentRequest $d) => [
                    'type' => 'document',
                    'title' => $d->documentsSummary(),
                    'date' => $d->created_at,
                    'badge' => ['label' => ucwords(str_replace('_', ' ', $d->status)), 'class' => 'bg-blue-50 text-blue-700'],
                ])
            )
            ->filter(fn ($activity) => $activity['date'])
            ->sortByDesc('date')
            ->take(5)
            ->values();

        return view('student.dashboard', [
            'activeDocumentRequests' => $user->documentRequests()->active()->count(),
            'upcomingAppointments' => $user->appointments()->upcoming()->count(),
            'completedTransactions' => $user->documentRequests()
                ->whereIn('status', ['completed'])
                ->count() + $user->appointments()->where('status', AppointmentStatus::COMPLETED->value)->count(),
            'hasPass' => (bool) $profile->pass_token,
            'recentActivity' => $recentActivity,
            'bannerEnabled' => Setting::get('banner_enabled') === 'true',
            'bannerText' => Setting::get('banner_text'),
        ]);
    }

    if ($user->role === 'admin') {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'roleCounts' => [
                'students' => User::where('role', 'student')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'registrars' => User::where('role', 'registrar')->count(),
                'cashiers' => User::where('role', 'cashier')->count(),
                'departments' => User::where('role', 'department')->count(),
            ],
            'departmentOffices' => User::where('role', 'department')
                ->selectRaw('office, count(*) as total')
                ->groupBy('office')
                ->orderBy('office')
                ->get()
                ->pluck('total', 'office'),
            'totalTransactions' => DocumentRequest::count() + Appointment::count(),
            'activeRequests' => DocumentRequest::query()->active()->count(),
            'qrVerifications' => GateLog::count(),
            'totalCollections' => (float) DocumentRequest::query()
                ->whereNotNull('paid_at')
                ->with('documents')
                ->get()
                ->sum(fn (DocumentRequest $d) => $d->documents->sum('fee')),
            'recentTransactions' => DocumentRequest::with(['user', 'documents'])
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }

    $view = match ($user->role) {
        'admin' => 'admin.dashboard',
        'registrar' => 'registrar.dashboard',
        'cashier' => 'cashier.dashboard',
        default => 'student.dashboard',
    };

    // Self-registered offices (role 'office') get their own generic
    // workspace — appointments only, no document requests.
    if ($user->role === 'office') {
        return redirect()->route('workspace.dashboard');
    }

    if (in_array($user->role, ['department', 'osas', 'accounting', 'library', 'guidance'], true)) {
        // Office is typed at registration, not picked, so match it the way the
        // sidebar already does - case must not decide which workspace a
        // staff account lands in. A legacy department account whose office is
        // none of the eight built-ins falls through to the generic workspace.
        $dept = match (strtoupper($user->office ?? '')) {
            'OSAS' => 'osas',
            'ACCOUNTING' => 'accounting',
            'LIBRARY' => 'library',
            'GUIDANCE' => 'guidance',
            'CICI' => 'cici',
            'CBMSD' => 'cbmsd',
            'COAG' => 'coag',
            'COED' => 'coed',
            default => 'workspace',
        };

        return redirect()->route($dept.'.dashboard');
    }

    if ($user->role === 'cashier') {
        return view($view, [
            'todayCollections' => (float) DocumentRequest::query()
                ->whereDate('paid_at', today())
                ->get()
                ->sum(fn (DocumentRequest $d) => $d->documents->sum('fee')),
            'todayPayments' => DocumentRequest::query()->whereDate('paid_at', today())->count(),
            'monthCollections' => (float) DocumentRequest::query()
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->get()
                ->sum(fn (DocumentRequest $d) => $d->documents->sum('fee')),
            'monthPayments' => DocumentRequest::query()
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->count(),
            'pendingPayments' => DocumentRequest::query()->active()->unpaid()->count(),
            'readyForPickup' => DocumentRequest::query()
                ->where('status', 'ready_for_pickup')
                ->count(),
        ]);
    }

    if ($user->role === 'registrar') {
        return view($view, [
            'pendingCount' => DocumentRequest::query()
                ->where('status', DocumentRequestStatus::SUBMITTED->value)
                ->count(),
            'approvedCount' => DocumentRequest::query()
                ->where('status', DocumentRequestStatus::FOR_SIGNATURE->value)
                ->count(),
            'issuedCount' => DocumentRequest::query()
                ->where('status', DocumentRequestStatus::COMPLETED->value)
                ->count(),
            'servedCount' => DocumentRequest::query()
                ->where('status', DocumentRequestStatus::COMPLETED->value)
                ->distinct()
                ->count('user_id'),
            'pendingRequests' => DocumentRequest::query()
                ->where('status', DocumentRequestStatus::SUBMITTED->value)
                ->with(['documents', 'user'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    return view($view);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {

    // 0. New Request Hub
    Route::get('/requests/new', [RequestController::class, 'newRequest'])->name('requests.new');

    // 1. Profile & Account Settings — the same shared page every role uses.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.show');
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');

    // 2. Appointment Booking
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::get('/appointments/slots', [AppointmentController::class, 'slots'])->name('appointments.slots');
    Route::get('/appointments/availability', [AppointmentController::class, 'availability'])->name('appointments.availability');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}/qr', [AppointmentController::class, 'qr'])->name('appointments.qr');
    Route::get('/appointments/{appointment}/qr/download', [AppointmentController::class, 'downloadQr'])->name('appointments.qr.download');
    Route::patch('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // 2b. Consultation Services Catalogue (pick a service → booking form pre-filled)
    Route::get('/consultations', [ConsultationCatalogController::class, 'index'])->name('consultations.index');

    // 3. Document Requests
    Route::get('/document-requests', [DocumentRequestController::class, 'index'])->name('documents.index');
    Route::get('/document-requests/create', [DocumentRequestController::class, 'create'])->name('documents.create');
    Route::post('/document-requests', [DocumentRequestController::class, 'store'])->name('documents.store');
    Route::get('/document-requests/{documentRequest}', [DocumentRequestController::class, 'show'])->name('documents.show');
    Route::get('/document-requests/{documentRequest}/qr/download', [DocumentRequestController::class, 'downloadQr'])->name('documents.qr.download');
    Route::get('/document-requests/{documentRequest}/requisition', [DocumentRequestController::class, 'requisition'])->name('documents.requisition');
    Route::get('/document-requests/{documentRequest}/order-of-payment', [DocumentRequestController::class, 'orderOfPayment'])->name('documents.order-of-payment');
    Route::post('/document-requests/{documentRequest}/official-receipt', [DocumentRequestController::class, 'submitOr'])->name('documents.submit-or');
    Route::get('/document-requests/{documentRequest}/edit', [DocumentRequestController::class, 'edit'])->name('documents.edit');
    Route::put('/document-requests/{documentRequest}', [DocumentRequestController::class, 'update'])->name('documents.update');
    Route::post('/document-requests/{documentRequest}/cancel', [DocumentRequestController::class, 'cancel'])->name('documents.cancel');
    Route::delete('/document-requests/{documentRequest}', [DocumentRequestController::class, 'destroy'])->name('documents.destroy');

    // 4. Digital Pass & Gate Access
    Route::get('/pass', [PassController::class, 'show'])->name('pass.show');

    // 5. Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');

    // 6. Feedback & Ratings
    Route::get('/feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // 7. Notifications (Email & System Alerts)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // 8. Help & FAQs (Guidelines & Office Directory)
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
});

Route::middleware(['auth', 'verified', 'role:registrar,department'])->prefix('registrar')->name('registrar.')->group(function () {

    // Appointment slot availability (calendar colors consumed by student/reschedule pages)
    Route::get('/availability/month', [AvailabilityController::class, 'month'])->name('availability.month');

    // Availability Management (configure open days / time slots)
    Route::get('/availability/schedule', [AvailabilityController::class, 'schedule'])->name('availability.schedule');
    Route::get('/availability/settings/{date}', [AvailabilityController::class, 'settings'])
        ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')->name('availability.settings');
    Route::post('/availability/save', [AvailabilityController::class, 'save'])->name('availability.save');
    Route::post('/availability/hours', [AvailabilityController::class, 'saveHours'])->name('availability.hours');
    Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');

    // Appointments Management (slot availability checker + rescheduling)
    Route::get('/appointments/slots', [RegistrarAppointmentController::class, 'slots'])->name('appointments.slots');
    Route::get('/appointments/print', [RegistrarAppointmentController::class, 'printAll'])->name('appointments.print');
    Route::get('/appointments', [RegistrarAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/{appointment}', [RegistrarAppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/reschedule', [RegistrarAppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::put('/appointments/{appointment}/reschedule', [RegistrarAppointmentController::class, 'updateReschedule'])->name('appointments.reschedule.update');
    Route::post('/appointments/{appointment}/confirm', [RegistrarAppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{appointment}/cancel', [RegistrarAppointmentController::class, 'cancel'])->name('appointments.cancel');

    // Document Requests (pipeline management)
    Route::get('/document-requests', [RegistrarDocumentRequestController::class, 'index'])->name('document-requests.index');
    Route::get('/document-requests/{documentRequest}', [RegistrarDocumentRequestController::class, 'show'])->name('document-requests.show');
    Route::get('/document-requests/{documentRequest}/order-of-payment', [RegistrarDocumentRequestController::class, 'orderOfPayment'])->name('document-requests.order-of-payment');
    Route::post('/document-requests/{documentRequest}/next', [RegistrarDocumentRequestController::class, 'next'])->name('document-requests.next');
    Route::post('/document-requests/{documentRequest}/validate-or', [RegistrarDocumentRequestController::class, 'validateOr'])->name('document-requests.validate-or');
    Route::post('/document-requests/{documentRequest}/or-correction', [RegistrarDocumentRequestController::class, 'requestOrCorrection'])->name('document-requests.or-correction');
    Route::patch('/document-requests/{documentRequest}/status', [RegistrarDocumentRequestController::class, 'updateStatus'])->name('document-requests.status');
    Route::post('/document-requests/{documentRequest}/cancel', [RegistrarDocumentRequestController::class, 'cancel'])->name('document-requests.cancel');
    Route::delete('/document-requests/{documentRequest}', [RegistrarDocumentRequestController::class, 'destroy'])->name('document-requests.destroy');

    // Student Record Lookup
    Route::get('/students', [StudentLookupController::class, 'index'])->name('students.index');
    Route::get('/students/{user}', [StudentLookupController::class, 'show'])->name('students.show');

    // QR Pass Verification
    Route::get('/qr-verification', [QrVerifyController::class, 'index'])->name('qr.index');

    // Document Fees & Services (document pricing & fees)
    Route::get('/documents', [RegistrarDocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [RegistrarDocumentController::class, 'store'])->name('documents.store');
    Route::put('/documents/{document}', [RegistrarDocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [RegistrarDocumentController::class, 'destroy'])->name('documents.destroy');

    // Notifications & Help (shared with the student pages, role-agnostic)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // User Management (accounts, roles, activation)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
    Route::put('/users/{user}/password', [AdminUserController::class, 'resetPassword'])->name('users.password');
    Route::put('/users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
    Route::put('/users/{user}/reject', [AdminUserController::class, 'reject'])->name('users.reject');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Department Settings (offices & operational parameters)
    Route::get('/offices', [DepartmentController::class, 'index'])->name('offices.index');
    Route::post('/offices', [DepartmentController::class, 'store'])->name('offices.store');
    Route::put('/offices/{office}', [DepartmentController::class, 'update'])->name('offices.update');
    Route::delete('/offices/{office}', [DepartmentController::class, 'destroy'])->name('offices.destroy');

    // Slot Availability & Capacity Management (daily per-slot limits + blocks)
    Route::get('/slot-availabilities', [SlotCapacityController::class, 'index'])->name('slots.index');
    Route::post('/slot-availabilities', [SlotCapacityController::class, 'store'])->name('slots.store');
    Route::put('/offices/{office}/capacity', [SlotCapacityController::class, 'updateCapacity'])->name('slots.capacity');
    Route::delete('/slot-availabilities/{slotAvailability}', [SlotCapacityController::class, 'destroy'])->name('slots.destroy');

    // System Configuration (global settings, academic terms, banners)
    Route::get('/settings', [ConfigController::class, 'index'])->name('settings.index');
    Route::put('/settings', [ConfigController::class, 'update'])->name('settings.update');

    // System Notifications & Broadcasts (announcements)
    Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}/toggle', [AdminAnnouncementController::class, 'toggle'])->name('announcements.toggle');
    Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Master Logs & Audits
    Route::get('/audits', [AdminAuditLogController::class, 'index'])->name('audits.index');

    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
});

Route::middleware(['auth', 'verified', 'role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {

    // Pending Payments & Payment History
    Route::get('/payments', [CashierPaymentController::class, 'pending'])->name('payments.pending');
    Route::post('/payments/{documentRequest}/record', [CashierPaymentController::class, 'record'])->name('payments.record');
    Route::get('/payments/{documentRequest}/receipt', [CashierPaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/history', [CashierPaymentController::class, 'history'])->name('payments.history');

    // Student Ledger (financial records)
    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger.index');
    Route::get('/ledger/{user}', [LedgerController::class, 'show'])->name('ledger.show');

    // Notifications (shared controller, role-agnostic)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

/*
|--------------------------------------------------------------------------
| Workspace endpoints, shared by all eight offices and departments
|--------------------------------------------------------------------------
| These handlers resolve everything from auth()->user()->officeScope(): the
| office being written to, the appointments in scope, the slots being read.
| So one implementation serves every workspace, registered once here and then
| declared inside each group below — which is what makes it work.
|
| The registrar owns the originals, but they sit behind role:registrar,department,
| and an OSAS or Accounting account is neither, so borrowing them would 403 a
| workspace out of its own page. Letting each group declare them also means each
| group's own role middleware guards them.
|
| AvailabilityController and Registrar\AppointmentController additionally pin
| the office to officeScope() unless the caller is the registrar, so exposing
| them here cannot turn one workspace into another workspace's data.
*/
$workspaceRoutes = function (): void {
    // One handler serves all eight desks: it reads the office from
    // officeScope(), so /cici/qr/check-in and /osas/qr/check-in are the same
    // scan endpoint wearing each group's role middleware.
    Route::post('/qr/check-in', [QrCheckInController::class, 'checkIn'])
        ->name('qr.check-in');

    Route::post('/availability/save', [AvailabilityController::class, 'save'])
        ->name('availability.save');

    // The office's own opening and closing time — the slot list of the whole
    // system is built from these two values.
    Route::post('/availability/hours', [AvailabilityController::class, 'saveHours'])
        ->name('availability.hours');

    Route::get('/availability/schedule', [AvailabilityController::class, 'schedule'])
        ->name('availability.schedule');

    Route::get('/availability/settings/{date}', [AvailabilityController::class, 'settings'])
        ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
        ->name('availability.settings');

    Route::get('/appointments/slots', [RegistrarAppointmentController::class, 'slots'])
        ->name('appointments.slots');

    Route::put('/appointments/{appointment}/reschedule', [RegistrarAppointmentController::class, 'updateReschedule'])
        ->name('appointments.reschedule.update');

    // Approve (confirm) a pending / for-reschedule appointment of this office.
    Route::post('/appointments/{appointment}/confirm', [RegistrarAppointmentController::class, 'confirm'])
        ->name('appointments.confirm');
};

Route::middleware(['auth', 'verified', 'role:department'])->prefix('cici')->name('cici.')->group(function () use ($workspaceRoutes) {
    Route::get('/', [CiciController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [CiciController::class, 'appointments'])->name('appointments');
    Route::get('/availability', [CiciController::class, 'availability'])->name('availability');
    Route::get('/qr', [CiciController::class, 'qrScanner'])->name('qr.index');
    Route::get('/consultations', [CiciController::class, 'consultations'])->name('consultations');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/help', [CiciController::class, 'help'])->name('help');

    $workspaceRoutes();
});

Route::middleware(['auth', 'verified', 'role:department'])->prefix('cbmsd')->name('cbmsd.')->group(function () use ($workspaceRoutes) {
    Route::get('/', [CbmsdController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [CbmsdController::class, 'appointments'])->name('appointments');
    Route::get('/availability', [CbmsdController::class, 'availability'])->name('availability');
    Route::get('/qr', [CbmsdController::class, 'qrScanner'])->name('qr.index');
    Route::get('/consultations', [CbmsdController::class, 'consultations'])->name('consultations');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/help', [CbmsdController::class, 'help'])->name('help');

    $workspaceRoutes();
});

Route::middleware(['auth', 'verified', 'role:department'])->prefix('coag')->name('coag.')->group(function () use ($workspaceRoutes) {
    Route::get('/', [CoagController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [CoagController::class, 'appointments'])->name('appointments');
    Route::get('/availability', [CoagController::class, 'availability'])->name('availability');
    Route::get('/qr', [CoagController::class, 'qrScanner'])->name('qr.index');
    Route::get('/consultations', [CoagController::class, 'consultations'])->name('consultations');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/help', [CoagController::class, 'help'])->name('help');

    $workspaceRoutes();
});

Route::middleware(['auth', 'verified', 'role:department'])->prefix('coed')->name('coed.')->group(function () use ($workspaceRoutes) {
    Route::get('/', [CoedController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [CoedController::class, 'appointments'])->name('appointments');
    Route::get('/availability', [CoedController::class, 'availability'])->name('availability');
    Route::get('/qr', [CoedController::class, 'qrScanner'])->name('qr.index');
    Route::get('/consultations', [CoedController::class, 'consultations'])->name('consultations');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/help', [CoedController::class, 'help'])->name('help');

    $workspaceRoutes();
});

Route::middleware(['auth', 'verified', 'role:department,osas'])->prefix('osas')->name('osas.')->group(function () use ($workspaceRoutes) {
    Route::get('/', [OsasController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [OsasController::class, 'appointments'])->name('appointments');
    Route::get('/availability', [OsasController::class, 'availability'])->name('availability');
    Route::get('/qr', [OsasController::class, 'qrScanner'])->name('qr.index');
    Route::get('/consultations', [OsasController::class, 'consultations'])->name('consultations');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/help', [OsasController::class, 'help'])->name('help');

    $workspaceRoutes();
});

Route::middleware(['auth', 'verified', 'role:department,accounting'])->prefix('accounting')->name('accounting.')->group(function () use ($workspaceRoutes) {
    Route::get('/', [AccountingController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [AccountingController::class, 'appointments'])->name('appointments');
    Route::get('/availability', [AccountingController::class, 'availability'])->name('availability');
    Route::get('/qr', [AccountingController::class, 'qrScanner'])->name('qr.index');
    Route::get('/consultations', [AccountingController::class, 'consultations'])->name('consultations');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/help', [AccountingController::class, 'help'])->name('help');

    $workspaceRoutes();
});

Route::middleware(['auth', 'verified', 'role:department,library'])->prefix('library')->name('library.')->group(function () use ($workspaceRoutes) {
    Route::get('/', [LibraryController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [LibraryController::class, 'appointments'])->name('appointments');
    Route::get('/availability', [LibraryController::class, 'availability'])->name('availability');
    Route::get('/qr', [LibraryController::class, 'qrScanner'])->name('qr.index');
    Route::get('/consultations', [LibraryController::class, 'consultations'])->name('consultations');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/help', [LibraryController::class, 'help'])->name('help');

    $workspaceRoutes();
});

Route::middleware(['auth', 'verified', 'role:department,guidance'])->prefix('guidance')->name('guidance.')->group(function () use ($workspaceRoutes) {
    Route::get('/', [GuidanceController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [GuidanceController::class, 'appointments'])->name('appointments');
    Route::get('/availability', [GuidanceController::class, 'availability'])->name('availability');
    Route::get('/qr', [GuidanceController::class, 'qrScanner'])->name('qr.index');
    Route::get('/consultations', [GuidanceController::class, 'consultations'])->name('consultations');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/help', [GuidanceController::class, 'help'])->name('help');

    $workspaceRoutes();
});

/*
|--------------------------------------------------------------------------
| Generic office workspace (self-registered offices)
|--------------------------------------------------------------------------
| An office account whose typed office name is none of the eight built-in
| workspaces lands here after admin approval + email verification: the same
| dashboard, appointments, availability, QR check-in and consultation
| tooling the named offices get — appointments only, no document requests.
| Everything is scoped server-side to auth()->user()->officeScope().
*/
Route::middleware(['auth', 'verified', 'role:department,office'])->prefix('workspace')->name('workspace.')->group(function () use ($workspaceRoutes) {
    Route::get('/', [OfficeWorkspaceController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [OfficeWorkspaceController::class, 'appointments'])->name('appointments');
    Route::get('/availability', [OfficeWorkspaceController::class, 'availability'])->name('availability');
    Route::get('/qr', [OfficeWorkspaceController::class, 'qrScanner'])->name('qr.index');
    Route::get('/consultations', [OfficeWorkspaceController::class, 'consultations'])->name('consultations');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::get('/help', [OfficeWorkspaceController::class, 'help'])->name('help');

    $workspaceRoutes();
});

// Consultation services — every office / department manages only its own rows.
// Ownership is resolved server-side from auth()->user()->office, never from the
// URL, so the route prefix is just a namespace here.
foreach ([
    'cici' => 'department',
    'cbmsd' => 'department',
    'coag' => 'department',
    'coed' => 'department',
    'osas' => 'department,osas',
    'accounting' => 'department,accounting',
    'library' => 'department,library',
    'guidance' => 'department,guidance',
    'workspace' => 'department,office',
] as $consultationPrefix => $consultationRoles) {
    Route::middleware(['auth', 'verified', 'role:'.$consultationRoles])
        ->prefix($consultationPrefix)
        ->name($consultationPrefix.'.')
        ->group(function () {
            Route::get('/consultations/create', [ConsultationServiceController::class, 'create'])
                ->name('consultations.create');
            Route::post('/consultations', [ConsultationServiceController::class, 'store'])
                ->name('consultations.store');
            Route::get('/consultations/{consultationService}/edit', [ConsultationServiceController::class, 'edit'])
                ->name('consultations.edit');
            Route::put('/consultations/{consultationService}', [ConsultationServiceController::class, 'update'])
                ->name('consultations.update');
            Route::delete('/consultations/{consultationService}', [ConsultationServiceController::class, 'destroy'])
                ->name('consultations.destroy');
        });
}

// Alumni Office — alumni have registration_type = 'alumni' but role stays 'student'
// (mirrors the web.php dashboard dispatch at "/dashboard":61 which returns alumni.dashboard).
// Alum slots here so `route('alumni.*')` resolves for the alumni sidebar branch + dashboard quick links.
Route::middleware(['auth', 'verified', 'role:student'])->prefix('alumni')->name('alumni.')->group(function () {

    // Alumni Dashboard
    Route::get('/dashboard', [AlumniController::class, 'dashboard'])->name('dashboard');

    // Document Requests (reuse student document routes; alumni store in studentProfile)
    Route::get('/documents', [AlumniController::class, 'documents'])->name('documents.index');
    // "New Request" must open the creation hub rather than the list. AlumniController has
    // no newRequest() method, so this route used to point at documents() and the sidebar's
    // New Request link silently reloaded the request list — same destination as My Requests.
    // Reuse the student hub, which is already reachable behind the role:student middleware
    // that alumni sit behind (their role stays 'student'; only registration_type differs).
    Route::get('/documents/new', [RequestController::class, 'newRequest'])->name('documents.new');

    // Appointments (reuse appointments; alumni book against offices)
    Route::get('/appointments', [AlumniController::class, 'appointments'])->name('appointments.index');

    // QR Pass
    Route::get('/pass', [AlumniController::class, 'pass'])->name('pass.show');

    // Profile & Account Settings — the same shared page every role uses.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.show');
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Help & FAQs
    Route::get('/help', [AlumniController::class, 'help'])->name('help');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public QR scan targets (opened directly by phone cameras / gate scanners)
Route::get('/verify/pass/{token}', [VerifyController::class, 'pass'])->name('verify.pass');
Route::get('/verify/document/{token}', [VerifyController::class, 'document'])->name('verify.document');
Route::get('/verify/appointment/{token}', [VerifyController::class, 'appointment'])->name('verify.appointment');

require __DIR__.'/auth.php';
