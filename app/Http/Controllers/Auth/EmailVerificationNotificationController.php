<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Same guard as registration: a provider outage must not 500, but the
        // button must not claim success either — say so on the page.
        $sent = SafeMailer::verifyEmail($request->user());

        return back()->with('status', $sent ? 'verification-link-sent' : 'verification-link-failed');
    }
}
