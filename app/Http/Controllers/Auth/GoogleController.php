<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * "Continue with Google" — Laravel Socialite against the OAuth client
 * configured in services.google (GOOGLE_CLIENT_ID / SECRET / REDIRECT_URI).
 *
 * Google has already proven the address on its side, so the emailed-link
 * step this flow replaces is not repeated: a sign-in here marks the
 * account verified outright. What it never replaces is the admin's
 * decision and the onboarding that follows — the pending / rejected /
 * deactivated gates the login form applies are re-applied here (an OAuth
 * round trip must not become a second front door into an unapproved
 * account), and the personal-details step still stands between a fresh
 * signup and its dashboard.
 */
class GoogleController extends Controller
{
    /**
     * Send the visitor to Google's consent screen.
     */
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return $this->failed('Google sign-in is not configured on this deployment.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Land back from Google: link to the account with that address or
     * create a student account the first time, then continue the session.
     */
    public function callback(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return $this->failed('Google sign-in is not configured on this deployment.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            // Cancelled consent, expired code, unreachable Google — none
            // of them deserve an error page; say so on the login form.
            report($e);

            return $this->failed('Google sign-in did not complete. Please try again or sign in with your email and password.');
        }

        $email = $google->getEmail();

        if (! $email) {
            return $this->failed('Your Google account has no email address and cannot be used to sign in.');
        }

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            // The address belongs to an existing account — student, other
            // registration type, or staff. Link into it, never duplicate
            // it. Google just proved the address, so a stale unverified
            // row is verified now; the password is untouched and keeps
            // working for the email/password form.
            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        } else {
            $user = User::create([
                // Google's display name; accounts that hide it fall back
                // to the part before the @ in the address.
                'name' => $google->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'email_verified_at' => now(),
                // Unusable but valid: this account signs in through Google.
                // "Forgot password" can still mint a real one if needed.
                'password' => Hash::make(Str::random(40)),
                'role' => User::ROLE_STUDENT,
                // Written explicitly although the columns carry DB defaults:
                // create() returns the model without refreshing them, and
                // the deactivated gate below reads is_active off this very
                // instance - a NULL here would refuse the signup it just made.
                'approval_status' => User::APPROVAL_APPROVED,
                'is_active' => true,
            ]);

            // The same shape a student signup creates — pass token
            // included, because the personal-details form and the QR pass
            // both expect the profile row to exist.
            $user->studentProfile()->create([
                'pass_token' => (string) Str::uuid(),
            ]);
        }

        // The login form's gates, applied before a session exists. Checked
        // in the same order and with the same messages, so the two entry
        // points can never disagree about who gets in.
        if ($user->isPendingApproval()) {
            return $this->failed('Your office account is still pending administrator approval. Please check back later.');
        }

        if ($user->isRejected()) {
            return $this->failed('Your office account application was declined.'
                .($user->rejection_reason ? ' Reason: '.$user->rejection_reason : ' Contact the administrator for details.'));
        }

        if (! $user->is_active) {
            return $this->failed('This account has been deactivated. Contact the administrator.');
        }

        Auth::login($user);

        $request->session()->regenerate();

        // The personal-details step still applies — a Google signup has
        // the account but none of the details yet, and a returning user
        // who never finished onboarding picks it up right here.
        if ($user->requiresProfileCompletion()) {
            return redirect()->route('complete-profile');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Every refusal lands on the login form: it is the page with the
     * error slot this message renders in, on both devices and both pages
     * of the auth flow.
     */
    private function failed(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
