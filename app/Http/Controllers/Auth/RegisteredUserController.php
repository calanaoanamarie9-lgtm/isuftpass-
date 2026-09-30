<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'offices' => \App\Enums\Office::cases(),
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
            $rules['office'] = ['required', 'string', Rule::in(\App\Enums\Office::toSelectKeys())];
            $rules['position'] = ['required', 'string', 'max:120'];
            $rules['contact_number'] = ['required', 'string', 'max:20'];
        }

        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'registration_type' => $request->user_type === 'other' ? $request->registration_type : null,
            'email_verified_at' => now(),
        ]);

        if ($request->user_type === 'student') {
            $user->studentProfile()->create([
                'pass_token' => (string) \Illuminate\Support\Str::uuid(),
            ]);
        }

        event(new Registered($user));

        // Office / staff applicants are held at the door until an admin signs
        // off the account, so don't hand them a session.
        if ($request->user_type === 'office') {
            $user->forceFill([
                'role' => User::ROLE_DEPARTMENT,
                'office' => $request->office,
                'position' => $request->position,
                'contact_number' => $request->contact_number,
                'approval_status' => User::APPROVAL_PENDING,
                'is_active' => true,
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
