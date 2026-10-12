<?php

namespace App\Policies;

use App\Enums\DocumentRequestStatus;
use App\Models\DocumentRequest;
use App\Models\User;

class DocumentRequestPolicy
{
    public function view(User $user, DocumentRequest $documentRequest): bool
    {
        return $user->id === $documentRequest->user_id;
    }

    public function update(User $user, DocumentRequest $documentRequest): bool
    {
        return $user->id === $documentRequest->user_id
            && $documentRequest->status === DocumentRequestStatus::SUBMITTED->value;
    }

    /**
     * Step 4 of the release flow: the student (or their representative)
     * presents the Official Receipt back to the Registrar's Office for
     * validation. Only open while the request actually awaits it.
     */
    public function submitOr(User $user, DocumentRequest $documentRequest): bool
    {
        return $user->id === $documentRequest->user_id
            && $documentRequest->status === DocumentRequestStatus::AWAITING_OR_VALIDATION->value;
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
