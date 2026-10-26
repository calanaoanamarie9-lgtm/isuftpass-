<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    /**
     * Save the star rating collected by the post-submission modal.
     *
     * The browser sends JSON, so every outcome — saved, already rated,
     * refused — is answered as JSON the modal can render without leaving
     * the confirmation page.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // transaction_type is the modal's name for the record being
            // rated; "document" is accepted so older callers keep working.
            'transaction_type' => ['required', Rule::in([
                Feedback::TYPE_APPOINTMENT,
                Feedback::TYPE_DOCUMENT_REQUEST,
                'document',
            ])],
            'transaction_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comments' => ['nullable', 'string', 'max:1000'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $isAppointment = $validated['transaction_type'] === Feedback::TYPE_APPOINTMENT;

        $feedbackable = $isAppointment
            ? Appointment::find($validated['transaction_id'])
            : DocumentRequest::find($validated['transaction_id']);

        if ($feedbackable === null) {
            return response()->json([
                'message' => 'The transaction you are rating could not be found.',
            ], 404);
        }

        // Only the owner (or staff who may already view it) can rate a record.
        Gate::authorize('view', $feedbackable);

        $feedback = Feedback::recordFor(
            Auth::user(),
            $feedbackable,
            (int) $validated['rating'],
            $validated['comments'] ?? $validated['comment'] ?? null,
        );

        return response()->json([
            'success' => true,
            // A retry after a dropped connection lands here instead of failing.
            'duplicate' => ! $feedback->wasRecentlyCreated,
            'feedback' => [
                'id' => $feedback->id,
                'rating' => $feedback->rating,
            ],
        ], $feedback->wasRecentlyCreated ? 201 : 200);
    }
}
