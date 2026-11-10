<?php

namespace App\Http\Controllers\Student;

use App\Enums\AppointmentStatus;
use App\Enums\DocumentRequestStatus;
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

    /**
     * Nothing is rated before it is over.
     *
     * An appointment is over when the visit is marked completed, and a
     * document request when the desk records the claim - the "Completed"
     * status the registrar's screen calls "Claimed". A request that is merely
     * ready for pick-up has not been served yet: the student has not stood at
     * the window, so there is nothing to rate.
     */
    private function ensureCompleted(Appointment|DocumentRequest $feedbackable): void
    {
        $completed = $feedbackable instanceof Appointment
            ? $feedbackable->status === AppointmentStatus::COMPLETED->value
            : $feedbackable->status === DocumentRequestStatus::COMPLETED->value;

        abort_unless($completed, 403, 'Feedback is only available for completed services.');
    }
}
