<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($approval = $request->query('approval')) {
            abort_unless(in_array($approval, [User::APPROVAL_PENDING, User::APPROVAL_APPROVED, User::APPROVAL_REJECTED], true), 404);

            $query->where('approval_status', $approval);
        }

        return view('admin.users.index', [
            'users' => $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString(),
            'search' => trim((string) $request->query('q')),
            'roleFilter' => $request->query('role'),
            'approvalFilter' => $request->query('approval'),
            'pendingCount' => User::pendingApproval()->count(),
        ]);
    }

    /**
     * Approve a pending office / staff application.
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        if ($user->isApproved()) {
            return back()->with('error', $user->email . ' is already approved.');
        }

        $user->approve($request->user());

        AuditLogger::log('user.approved', 'Approved the ' . ($user->office ?: 'office') . ' account for ' . $user->email . '.', $request->user());

        return back()->with('status', 'Approved the account for ' . $user->email . '. They can now log in.');
    }

    /**
     * Decline a pending office / staff application.
     */
    public function reject(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->reject($request->user(), $data['rejection_reason'] ?? null);

        AuditLogger::log('user.rejected', 'Rejected the ' . ($user->office ?: 'office') . ' account for ' . $user->email . '.', $request->user());

        return back()->with('status', 'Rejected the account for ' . $user->email . '.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in([User::ROLE_STUDENT, User::ROLE_REGISTRAR, User::ROLE_CASHIER, User::ROLE_ADMIN, User::ROLE_DEPARTMENT])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        AuditLogger::log('user.created', 'Created ' . ucfirst($user->role) . ' account for ' . $user->email . '.', $request->user());

        return back()->with('status', 'Account created for ' . $user->email . '.');
    }

    public function toggle(User $user, Request $request): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        AuditLogger::log('user.' . ($user->is_active ? 'activated' : 'deactivated'), ($user->is_active ? 'Activated' : 'Deactivated') . ' account for ' . $user->email . '.', $request->user());

        return back()->with('status', ($user->is_active ? 'Activated' : 'Deactivated') . ' account for ' . $user->email . '.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())
            && (($request->input('role') !== $user->role) || ($request->input('email') !== $user->email))) {
            return back()->with('error', 'You cannot change the role or email of your own account here.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in([User::ROLE_STUDENT, User::ROLE_REGISTRAR, User::ROLE_CASHIER, User::ROLE_ADMIN, User::ROLE_DEPARTMENT])],
        ]);

        $user->update($data);

        AuditLogger::log('user.updated', 'Updated account details for ' . $user->email . ' (role: ' . ucfirst($user->role) . ').', $request->user());

        return back()->with('status', 'Account updated for ' . $user->email . '.');
    }

    /**
     * Set a new password for an account (admin-side reset).
     *
     * This exists for the accounts that cannot drive the emailed reset link
     * themselves: a student at the counter, or anyone whose mailbox is not
     * reachable. It deliberately does not go through the password broker - the
     * admin hands the value over directly, so it must work with mail down.
     *
     * The value arrives in plain text and is hashed by the model's
     * 'password' => 'hashed' cast, the same way store() already relies on it.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $user->update(['password' => $data['password']]);

        AuditLogger::log('user.password_reset', 'Reset the password for ' . $user->email . '.', $request->user());

        return back()->with('status', 'Password reset for ' . $user->email . '. Give the new password to them securely and have them change it after signing in.');
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $email = $user->email;
        $user->delete();

        AuditLogger::log('user.deleted', 'Deleted account for ' . $email . '.', $request->user());

        return back()->with('status', 'Account deleted for ' . $email . '.');
    }
}