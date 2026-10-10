<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Office;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Display the registration form for the selected account type.
     */
    public function form(Request $request): View
    {
        $type = $request->query('type');

        abort_unless(in_array($type, ['student', 'office', 'other'], true), 404);

        return view('auth.register-form', [
            'userType' => $type,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'string', 'in:student,office,other'],
        ];

        if ($request->user_type === 'other') {
            $rules['registration_type'] = ['required', 'string', Rule::in(['alumni', 'guest', 'parent'])];
        }

        if ($request->user_type === 'office') {
            // The applicant types their own office instead of choosing one:
            // an office may apply before it exists in App\Enums\Office, and
            // all the account needs is a name to show and to scope by.
            $rules['office'] = ['required', 'string', 'max:150'];
            $rules['position'] = ['required', 'string', 'max:120'];
            $rules['contact_number'] = ['required', 'string', 'max:20'];
        }

        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'registration_type' => $request->user_type === 'other' ? $request->registration_type : null,
            // email_verified_at is deliberately absent: a fresh signup starts
            // unverified and stays that way until the signed link is clicked.
        ]);

        if ($request->user_type === 'student') {
            $user->studentProfile()->create([
                'pass_token' => (string) Str::uuid(),
            ]);
        }

        // Laravel's EventServiceProvider listens for Registered and mails the
        // verification link the instant it fires, and that stock listener is
        // unguarded. The row above is already committed, so a provider outage
        // must cost the email rather than the signup — report it instead and
        // let the user press "Resend Verification Email" later.
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            report(new \RuntimeException(
                'Verification link not delivered: '.$user->email.' ('.$e->getMessage().')',
                0,
                $e,
            ));
        }

        // Office / staff applicants are held at the door until an admin signs
        // off the account, so don't hand them a session - and keep the row
        // inactive on top of the pending status, so no single gate ever has
        // to be the only thing standing between a fresh applicant and a
        // login. approve() is what flips is_active back on.
        if ($request->user_type === 'office') {
            $user->forceFill([
                'role' => User::ROLE_OFFICE,
                // Typed, then resolved: "University Library" is stored as
                // Library so the account sees that office's records, while a
                // name that fits no office is kept exactly as typed.
                'office' => Office::fromTyped($request->office),
                'position' => $request->position,
                'contact_number' => $request->contact_number,
                'approval_status' => User::APPROVAL_PENDING,
                'is_active' => false,
            ])->save();

            return redirect(route('register.pending', absolute: false));
        }

        Auth::login($user);

        return redirect(route('complete-profile', absolute: false));
    }

    /**
     * Confirmation screen shown right after an office / staff application is
     * submitted. Reachable without a session because the account cannot log in
     * until an admin approves it.
     */
    public function pending(): View
    {
        return view('auth.registration-pending');
    }
}
