<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * Email delivery is off: Render's free tier blocks outbound SMTP and no
     * HTTPS mail API is configured, so a link could never arrive. Rather than
     * creating a token nobody can receive, point the user at a human who can
     * reset the account. The route stays alive so a direct POST cannot 500.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Password reset by email is turned off. Please contact the system administrator to have your password reset.',
            ]);
    }
}
