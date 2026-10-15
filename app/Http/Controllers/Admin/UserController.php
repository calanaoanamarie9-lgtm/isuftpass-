<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Mail\OfficeAccountApproved;
use App\Models\User;
use App\Notifications\OfficeAccountApprovedNotification;
use App\Support\AuditLogger;
use App\Support\SafeMailer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role');
        $search = trim((string) $request->query('q'));
        $approval = $request->query('approval');

        if ($approval) {
            abort_unless(in_array($approval, [User::APPROVAL_PENDING, User::APPROVAL_APPROVED, User::APPROVAL_REJECTED], true), 404);
        }

        // The table and every stat card are built from the same filters, so
        // the numbers always describe the same set of accounts the list is
        // showing. (They used to be counted off the current page alone, which
        // capped each card at however many rows fit on the page.)
        $filtered = function () use ($role, $search, $approval): Builder {
            $query = User::query();

            if ($role) {
                // Student, alumni, parent and guest all share role='student'; their
                // real type lives in registration_type, so filter on that. The
                // remaining (staff) roles still filter on the role column.
                match ($role) {
                    'alumni' => $query->where('registration_type', 'alumni'),
                    'parent' => $query->whereIn('registration_type', ['parent', 'guardian']),
                    'guest' => $query->where('registration_type', 'guest'),
                    'student' => $query->where('role', User::ROLE_STUDENT)
                        ->where(fn ($q) => $q->whereNull('registration_type')
                            ->orWhereNotIn('registration_type', ['alumni', 'parent', 'guardian', 'guest'])),
                    default => $query->where('role', $role),
                };
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($approval) {
                $query->where('approval_status', $approval);
            }

            return $query;
        };

        // Public accounts all sit behind role='student'; their type is the
        // registration_type typed at signup.
        $publicTypes = ['alumni', 'parent', 'guardian', 'guest'];

        return view('admin.users.index', [
            'users' => $filtered()->orderBy('created_at', 'desc')->paginate(12)->withQueryString(),
            'counts' => [
                'active' => $filtered()->where('is_active', true)->count(),
                'students' => $filtered()->where('role', User::ROLE_STUDENT)
                    ->where(fn ($q) => $q->whereNull('registration_type')
                        ->orWhereNotIn('registration_type', $publicTypes))
                    ->count(),
                'alumni' => $filtered()->where('registration_type', 'alumni')->count(),
                'parents' => $filtered()->whereIn('registration_type', ['parent', 'guardian'])->count(),
                'guests' => $filtered()->where('registration_type', 'guest')->count(),
                // Every account that is not a public user type: departments,
                // colleges, accounting/library/office staff, registrar,
                // cashier, admin, security and any office role, so all of
                // them are accounted for instead of only four roles.
                'office' => $filtered()->whereNotIn('role', [User::ROLE_STUDENT])->count(),
            ],
            'roleOptions' => $this->roleOptions(),
            'search' => $search,
            'roleFilter' => $role,
            'approvalFilter' => $approval,
            'pendingCount' => User::pendingApproval()->count(),
        ]);
    }

    /**
     * The Role filter options: the four public account types first, then
     * every staff role in the Role catalog, followed by any role that exists
     * on a user but is not in the catalog yet — so no office or department
     * account can sit behind a filter that never offers its role.
     *
     * @return array<string, string>
     */
    private function roleOptions(): array
    {
        $options = [];

        foreach (array_merge(Role::userTypes(), Role::staff()) as $role) {
            $options[$role->value] = $role->label();
        }

        $unknown = User::query()
            ->whereNotIn('role', Role::values())
            ->distinct()
            ->orderBy('role')
            ->pluck('role');

        foreach ($unknown as $role) {
            if ($role === null || $role === '') {
                continue;
            }

            $options[$role] = ucfirst($role);
        }

        return $options;
    }

    /**
     * Approve a pending office / staff application.
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        if ($user->isApproved()) {
            return back()->with('error', $user->email.' is already approved.');
        }

        $user->approve($request->user());

        AuditLogger::log('user.approved', 'Approved the '.($user->office ?: 'office').' account for '.$user->email.'.', $request->user());

        // The registration popup promises the applicant an email at this
        // exact moment, so the decision has to send one - plus the in-app
        // bell entry for whoever is already roaming the portal. Both are
        // best-effort: SafeMailer keeps a provider outage from failing the
        // admin's action, and the approval itself is already committed.
        $user->notify(new OfficeAccountApprovedNotification);
        SafeMailer::send($user, new OfficeAccountApproved($user));

        return back()->with('status', 'Approved the account for '.$user->email.'. They can now log in.');
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

        AuditLogger::log('user.rejected', 'Rejected the '.($user->office ?: 'office').' account for '.$user->email.'.', $request->user());

        return back()->with('status', 'Rejected the account for '.$user->email.'.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in([User::ROLE_STUDENT, User::ROLE_REGISTRAR, User::ROLE_CASHIER, User::ROLE_ADMIN, User::ROLE_DEPARTMENT, User::ROLE_OFFICE, User::ROLE_GUARD])],
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

        AuditLogger::log('user.created', 'Created '.ucfirst($user->role).' account for '.$user->email.'.', $request->user());

        return back()->with('status', 'Account created for '.$user->email.'.');
    }

    public function toggle(User $user, Request $request): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        AuditLogger::log('user.'.($user->is_active ? 'activated' : 'deactivated'), ($user->is_active ? 'Activated' : 'Deactivated').' account for '.$user->email.'.', $request->user());

        return back()->with('status', ($user->is_active ? 'Activated' : 'Deactivated').' account for '.$user->email.'.');
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
            'role' => ['required', Rule::in([User::ROLE_STUDENT, User::ROLE_REGISTRAR, User::ROLE_CASHIER, User::ROLE_ADMIN, User::ROLE_DEPARTMENT, User::ROLE_OFFICE, User::ROLE_GUARD])],
        ]);

        $user->update($data);

        AuditLogger::log('user.updated', 'Updated account details for '.$user->email.' (role: '.ucfirst($user->role).').', $request->user());

        return back()->with('status', 'Account updated for '.$user->email.'.');
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

        AuditLogger::log('user.password_reset', 'Reset the password for '.$user->email.'.', $request->user());

        return back()->with('status', 'Password reset for '.$user->email.'. Give the new password to them securely and have them change it after signing in.');
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $email = $user->email;
        $user->delete();

        AuditLogger::log('user.deleted', 'Deleted account for '.$email.'.', $request->user());

        return back()->with('status', 'Account deleted for '.$email.'.');
    }
}
