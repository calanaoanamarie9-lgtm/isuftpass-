<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    /**
     * Confirm the address and show the success screen.
     *
     * There is no Auth::login() here on purpose. The route sits behind the
     * `auth` middleware, and the pending / rejected / deactivated checks only
     * exist in the login form — logging the holder of a link in from this
     * controller would let an unapproved office account walk straight past
     * the approval gate.
     */
    public function __invoke(string $id, string $hash): RedirectResponse|View
    {
        $user = User::findOrFail($id);

        // The hash is only sha1(email), so it is guessable on its own; the
        // signed middleware is what actually authenticates this request.
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return view('auth.email-verified');
    }
}
