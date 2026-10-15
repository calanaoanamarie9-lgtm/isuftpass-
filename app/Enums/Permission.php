<?php

namespace App\Enums;

/**
 * Every capability the application checks through a Gate.
 *
 * The permission catalog is defined here once and mirrored into the
 * `permissions` table by RolesAndPermissionsSeeder, so the database and the
 * code can never drift apart. Roles (see Role::permissions()) are what grant
 * them.
 */
enum Permission: string
{
    // --- Document requesting -------------------------------------------------
    /** May submit document requests at all. */
    case RequestDocuments = 'documents.request';

    /** May request student academic records (TOR, good moral, F-137-A…). */
    case RequestAcademicDocuments = 'documents.request.academic';

    /** May request alumni records (diploma replacement, TOR, CAV…). */
    case RequestAlumniDocuments = 'documents.request.alumni';

    // --- Appointment booking -------------------------------------------------
    /** May book a consultation appointment at all. */
    case BookAppointment = 'appointments.book';

    case BookRegistrar = 'appointments.book.registrar';

    case BookSsc = 'appointments.book.ssc';

    case BookLibrary = 'appointments.book.library';

    case BookCashier = 'appointments.book.cashier';

    /** Book the college deans / academic departments (CICI, CBMSD, COAG, COED). */
    case BookDepartment = 'appointments.book.department';

    /** Book a self-registered / administrative office (HR, Property, …). */
    case BookGeneralOffice = 'appointments.book.general';

    // --- Visitor access ------------------------------------------------------
    /** May request a visitor / entry pass. */
    case RequestVisitorPass = 'visitor-pass.request';

    // --- Profiles ------------------------------------------------------------
    case ViewStudentProfile = 'profile.student.view';

    // --- Staff oversight -----------------------------------------------------
    case ManageSystem = 'system.manage';

    case ManageUsers = 'users.manage';

    case ManageAppointments = 'appointments.manage';

    case ManageDocuments = 'documents.manage';

    case ManagePayments = 'payments.manage';

    case ManageOffice = 'office.manage';

    case ScanGate = 'gate.scan';

    /**
     * Human-readable label shown in the admin directory.
     */
    public function label(): string
    {
        return match ($this) {
            self::RequestDocuments => 'Submit document requests',
            self::RequestAcademicDocuments => 'Request student academic records',
            self::RequestAlumniDocuments => 'Request alumni records',
            self::BookAppointment => 'Book appointments',
            self::BookRegistrar => 'Book the Registrar',
            self::BookSsc => 'Book the SSC',
            self::BookLibrary => 'Book the Library',
            self::BookCashier => 'Book the Cashier',
            self::BookDepartment => 'Book a college dean / department',
            self::BookGeneralOffice => 'Book an administrative office',
            self::RequestVisitorPass => 'Request a visitor / entry pass',
            self::ViewStudentProfile => 'View a student profile',
            self::ManageSystem => 'Manage system settings',
            self::ManageUsers => 'Manage user accounts',
            self::ManageAppointments => 'Manage appointments',
            self::ManageDocuments => 'Manage document requests',
            self::ManagePayments => 'Manage payments',
            self::ManageOffice => 'Manage an office workspace',
            self::ScanGate => 'Scan gate passes',
        };
    }

    /**
     * Coarse grouping used when presenting the catalog.
     */
    public function group(): string
    {
        return match ($this) {
            self::RequestDocuments,
            self::RequestAcademicDocuments,
            self::RequestAlumniDocuments => 'Documents',
            self::BookAppointment,
            self::BookRegistrar,
            self::BookSsc,
            self::BookLibrary,
            self::BookCashier,
            self::BookDepartment,
            self::BookGeneralOffice => 'Appointments',
            self::RequestVisitorPass => 'Visitor Access',
            self::ViewStudentProfile => 'Profiles',
            default => 'Staff Oversight',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $permission) => $permission->value, self::cases());
    }

    /**
     * @return array<string, string>
     */
    public static function toSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $permission) => [$permission->value => $permission->label()])
            ->all();
    }
}
