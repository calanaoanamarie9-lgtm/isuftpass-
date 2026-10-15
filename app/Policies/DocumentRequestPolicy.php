<?php

namespace App\Policies;

use App\Enums\DocumentRequestStatus;
use App\Models\DocumentRequest;
use App\Models\User;

class DocumentRequestPolicy
{
    /**
     * Staff dashboards list document requests; students list their own
     * through the user relationship, never through this ability.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'
            || $user->role === 'registrar'
            || $user->role === 'cashier';
    }

    /**
     * Document requests carry no office column: every request runs through
     * the Registrar pipeline (the Cashier takes payment), so staff access is
     * decided by role, not by office. The System Admin has master access.
     */
    public function view(User $user, DocumentRequest $documentRequest): bool
    {
        return $user->id === $documentRequest->user_id
            || $this->isStaff($user);
    }

    private function isStaff(User $user): bool
    {
        return in_array($user->role, ['admin', 'registrar', 'cashier'], true);
    }

    public function update(User $user, DocumentRequest $documentRequest): bool
    {
        return $user->id === $documentRequest->user_id
            && $documentRequest->status === DocumentRequestStatus::SUBMITTED->value;
    }

    public function cancel(User $user, DocumentRequest $documentRequest): bool
    {
        return $user->id === $documentRequest->user_id && $documentRequest->isActive();
    }

    public function delete(User $user, DocumentRequest $documentRequest): bool
    {
        return $user->id === $documentRequest->user_id && ! $documentRequest->isActive();
    }
}
