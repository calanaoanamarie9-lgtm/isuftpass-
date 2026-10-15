<?php

namespace App\Enums;

/**
 * The full role catalog.
 *
 * Staff roles (Admin, Registrar, Cashier, Department, SSC, Accounting,
 * Library, Office, Guard) are stored directly on users.role. The four public
 * user types (Student, Alumni, Parent, Guest) are stored on users as
 * role = 'student' plus a registration_type; User::effectiveRole() resolves
 * which of them an account actually is, and User::rbacRole() turns that into
 * one of these cases.
 */
enum Role: string
{
    case Admin = 'admin';

    case Registrar = 'registrar';

    case Cashier = 'cashier';

    case Student = 'student';

    case Alumni = 'alumni';

    case Parent = 'parent';

    case Guest = 'guest';

    case Department = 'department';

    case Ssc = 'ssc';

    case Accounting = 'accounting';

    case Library = 'library';

    case Office = 'office';

    case Guard = 'guard';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Registrar => 'Registrar Staff',
            self::Cashier => 'Cashier',
            self::Student => 'Student',
            self::Alumni => 'Alumni',
            self::Parent => 'Parent / Guardian',
            self::Guest => 'Guest',
            self::Department => 'Department Staff',
            self::Ssc => 'SSC Staff',
            self::Accounting => 'Accounting Staff',
            self::Library => 'Library Staff',
            self::Office => 'Office Staff',
            self::Guard => 'Security Guard',
        };
    }

    /**
     * True for accounts that run an office or the system rather than request
     * from it.
     */
    public function isStaff(): bool
    {
        return ! $this->isUserType();
    }

    public function isUserType(): bool
    {
        return in_array($this, [self::Student, self::Alumni, self::Parent, self::Guest], true);
    }

    /**
     * The permissions this role grants.
     *
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Student => [
                Permission::RequestDocuments,
                Permission::RequestAcademicDocuments,
                Permission::BookAppointment,
                Permission::BookRegistrar,
                Permission::BookSsc,
                Permission::BookLibrary,
                Permission::ViewStudentProfile,
            ],
            self::Alumni => [
                Permission::RequestDocuments,
                Permission::RequestAlumniDocuments,
                Permission::BookAppointment,
                Permission::BookRegistrar,
                Permission::BookSsc,
                Permission::BookLibrary,
                Permission::BookGeneralOffice,
                Permission::ViewStudentProfile,
            ],
            self::Parent => [
                Permission::BookAppointment,
                Permission::BookSsc,
                Permission::BookCashier,
                Permission::BookDepartment,
            ],
            self::Guest => [
                Permission::BookAppointment,
                Permission::BookDepartment,
                Permission::BookGeneralOffice,
                Permission::RequestVisitorPass,
            ],
            self::Admin => [
                Permission::ManageSystem,
                Permission::ManageUsers,
                Permission::ManageAppointments,
                Permission::ManageDocuments,
                Permission::ManagePayments,
                Permission::ManageOffice,
            ],
            self::Registrar => [
                Permission::ManageAppointments,
                Permission::ManageDocuments,
                Permission::ManageOffice,
            ],
            self::Cashier => [
                Permission::ManagePayments,
                Permission::ManageDocuments,
            ],
            self::Department,
            self::Ssc,
            self::Accounting,
            self::Library,
            self::Office => [
                Permission::ManageAppointments,
                Permission::ManageOffice,
            ],
            self::Guard => [
                Permission::ScanGate,
            ],
        };
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        return array_map(fn (Permission $permission) => $permission->value, $this->permissions());
    }

    public function hasPermission(Permission|string $permission): bool
    {
        $name = $permission instanceof Permission ? $permission->value : $permission;

        return in_array($name, $this->permissionNames(), true);
    }

    /**
     * Resolve an effective role value (User::effectiveRole()) to a case.
     * Unknown values fall back to Student, mirroring the app's default.
     */
    public static function fromEffective(?string $effective): self
    {
        return self::tryFrom((string) $effective) ?? self::Student;
    }

    /**
     * The four public account types, in display order.
     *
     * @return list<self>
     */
    public static function userTypes(): array
    {
        return [self::Student, self::Alumni, self::Parent, self::Guest];
    }

    /**
     * The staff roles, in display order.
     *
     * @return list<self>
     */
    public static function staff(): array
    {
        return [self::Admin, self::Registrar, self::Cashier, self::Department, self::Ssc, self::Accounting, self::Library, self::Office, self::Guard];
    }

    /**
     * @return array<string, string>
     */
    public static function toSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role) => [$role->value => $role->label()])
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }
}
