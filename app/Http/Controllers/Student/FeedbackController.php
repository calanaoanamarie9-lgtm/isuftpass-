<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Show the rating form for a completed appointment or document request.
     */
    public function create(Request $request): View
    {
        $validated = $request->validate([
            'kind' => ['required', 'in:appointment,document'],
            'id' => ['required', 'integer'],
        ]);

        $feedbackable = $this->resolveFeedbackable($validated['kind'], (int) $validated['id']);

        Gate::authorize('view', $feedbackable);

        $this->ensureCompleted($feedbackable);

        $already = $this->alreadyRated($feedbackable);

        return view('student.feedback.create', [
            'feedbackable' => $feedbackable,
            'kind' => $validated['kind'],
            'already' => $already,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kind' => ['required', 'in:appointment,document'],
            'id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $feedbackable = $this->resolveFeedbackable($validated['kind'], (int) $validated['id']);

        Gate::authorize('view', $feedbackable);
        $this->ensureCompleted($feedbackable);

        if ($this->alreadyRated($feedbackable)) {
            return back()->with('error', 'You have already submitted feedback for this service.');
        }

        Feedback::recordFor(
            Auth::user(),
            $feedbackable,
            (int) $validated['rating'],
            $validated['comment'] ?? null,
        );

        return redirect()->route('dashboard')
            ->with('status', 'Thank you! Your feedback has been recorded.');
    }

    private function resolveFeedbackable(string $kind, int $id): Appointment|DocumentRequest
    {
        return $kind === 'appointment'
            ? Appointment::findOrFail($id)
            : DocumentRequest::findOrFail($id);
    }

    private function alreadyRated(Appointment|DocumentRequest $feedbackable): bool
    {
        return Feedback::where('user_id', Auth::id())
            ->where('feedbackable_type', $feedbackable->getMorphClass())
            ->where('feedbackable_id', $feedbackable->id)
            ->exists();
    }

    private function ensureCompleted(Appointment|DocumentRequest $feedbackable): void
    {
        $completed = $feedbackable instanceof Appointment
            ? $feedbackable->status === 'completed'
            : in_array($feedbackable->status, ['completed', 'ready_for_pickup'], true);

        abort_unless($completed, 403, 'Feedback is only available for completed services.');
    }
}
