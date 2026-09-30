<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Office / staff accounts stay locked until an admin decides. Checked
        // before is_active because a rejected account is also deactivated, and
        // the applicant deserves to see why it was turned down.
        if ($user->isPendingApproval()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Your office account is still pending administrator approval. Please check back later.',
            ]);
        }

        if ($user->isRejected()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Your office account application was declined.'
                    .($user->rejection_reason ? ' Reason: '.$user->rejection_reason : ' Contact the administrator for details.'),
            ]);
        }

        if (! $user->is_active) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Contact the administrator.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
