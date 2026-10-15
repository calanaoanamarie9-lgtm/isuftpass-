<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Who may see and act on an appointment.
 *
 * Three tiers, enforced here rather than in controllers:
 *  - the student who booked it,
 *  - the office it belongs to (staff dashboards are scoped to their own
 *    office and may confirm / reschedule only their own bookings),
 *  - the System Admin, who has master access across every office.
 */
class AppointmentPolicy
{
    /**
     * Staff dashboards list appointments; students list their own through
     * the user relationship, never through this ability.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'
            || $user->role === 'registrar'
            || $user->role === 'cashier'
            || $user->role === 'guard'
            || $this->isOfficeStaff($user);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin'
            || $user->role === 'registrar'
            || $user->id === $appointment->user_id
            || $this->officeStaffFor($user, $appointment);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin'
            || $user->id === $appointment->user_id;
    }

    /**
     * Staff action on someone's booking: confirm, cancel or reschedule from
     * a staff dashboard. The registrar and the System Admin act across
     * offices; every other staff account is pinned to its own office.
     */
    public function manage(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin'
            || $user->role === 'registrar'
            || $this->officeStaffFor($user, $appointment);
    }

    /**
     * Cross-office capacity reads (the JSON availability endpoints). The
     * registrar and the System Admin read any office; staff read their own.
     */
    public function readOffice(User $user, string $office): bool
    {
        return $user->role === 'admin'
            || $user->role === 'registrar'
            || ($this->isOfficeStaff($user) && $user->officeScope() === $office);
    }

    private function officeStaffFor(User $user, Appointment $appointment): bool
    {
        return $this->isOfficeStaff($user)
            && $user->officeScope() === $appointment->office;
    }

    private function isOfficeStaff(User $user): bool
    {
        return in_array($user->role, ['department', 'ssc', 'accounting', 'library', 'office'], true);
    }
}
