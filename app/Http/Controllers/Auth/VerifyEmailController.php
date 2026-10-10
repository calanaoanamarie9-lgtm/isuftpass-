<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Confirm the address, sign the holder in, and send them straight on
     * to the next onboarding step.
     *
     * The link is usually clicked on the phone that reads the inbox — a
     * device with no session — so requiring `auth` on the route would drop
     * every fresh signup on the login page mid-flow. Logging in from here
     * is therefore deliberate, and safe only because the same pending /
     * rejected / deactivated gates the login form applies are re-checked
     * below: a signed link alone must never walk an unapproved office
     * account past the approval gate.
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        // The hash is only sha1(email), so it is guessable on its own; the
        // signed middleware is what actually authenticates this request.
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Invalid verification link.');
        }

        // Same gates as AuthenticatedSessionController::store(), in the
        // same order: pending and rejected first, because a rejected
        // account is also deactivated and the applicant deserves to see
        // why it was turned down.
        if ($block = $this->loginBlock($user)) {
            return redirect()->route('login')->withErrors(['email' => $block]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Verification hands off straight to the next step — no login page
        // in between. Details missing → the personal-details form,
        // unconditionally: verifying must never skip the step the gate
        // protects, and the dashboard would only bounce them there anyway.
        // Details present → wherever they were headed when the gate
        // recorded it (`EnsureEmailIsVerified` stores the URL it bounced
        // them from), or the dashboard by default.
        $intended = session()->pull('url.intended');

        $destination = $user->hasCompletedProfile()
            ? ($intended ?: route('dashboard'))
            : route('complete-profile');

        return redirect()->to($destination);
    }

    /**
     * The account-state gates the login form applies, as messages. Null
     * means this holder may hold a session.
     */
    private function loginBlock(User $user): ?string
    {
        if ($user->isPendingApproval()) {
            return 'Your office account is still pending administrator approval. Please check back later.';
        }

        if ($user->isRejected()) {
            return 'Your office account application was declined.'
                . ($user->rejection_reason ? ' Reason: ' . $user->rejection_reason : ' Contact the administrator for details.');
        }

        if (! $user->is_active) {
            return 'This account has been deactivated. Contact the administrator.';
        }

        return null;
    }
}
