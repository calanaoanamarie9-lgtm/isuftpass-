<?php

namespace App\Policies;

use App\Models\ConsultationService;
use App\Models\User;

/**
 * A consultation service belongs to one office. That office (and the System
 * Admin, with master access) may edit, toggle and remove it; nobody else may
 * even see it in management screens.
 */
class ConsultationServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'
            || $user->role === 'registrar'
            || $this->isOfficeStaff($user);
    }

    public function update(User $user, ConsultationService $consultationService): bool
    {
        return $user->role === 'admin'
            || $this->owns($user, $consultationService);
    }

    /**
     * Quick activate / deactivate from the office's service list.
     */
    public function toggle(User $user, ConsultationService $consultationService): bool
    {
        return $this->update($user, $consultationService);
    }

    public function delete(User $user, ConsultationService $consultationService): bool
    {
        return $this->update($user, $consultationService);
    }

    private function owns(User $user, ConsultationService $consultationService): bool
    {
        return $this->isOfficeStaff($user)
            && $user->officeScope() === $consultationService->office;
    }

    private function isOfficeStaff(User $user): bool
    {
        return in_array($user->role, ['department', 'ssc', 'accounting', 'library', 'office'], true);
    }
}
