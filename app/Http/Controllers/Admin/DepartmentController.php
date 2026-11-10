<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Office as OfficeEnum;
use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $availableOffices = OfficeEnum::toSelect();
        $fromTable = Office::query()->where('is_active', true)->pluck('name', 'name')->all();
        foreach ($fromTable as $name) {
            if (! isset($availableOffices[$name])) {
                $availableOffices[$name] = $name;
            }
        }

        // Grouped by the office value the account actually holds, so the card
        // for "Registrar" lists the account whose office resolves to it. The
        // typed name on the row and the enum value are not always the same
        // string ("Registrar's Office" vs "Registrar"), and fromTyped() is the
        // rule every other page already resolves an office with.
        $officeUsers = User::query()
            ->whereIn('role', [User::ROLE_OFFICE, User::ROLE_DEPARTMENT])
            ->whereNotNull('office')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (User $u) => strtoupper(OfficeEnum::fromTyped($u->office)));

        return view('admin.departments.index', [
            'offices' => Office::query()->orderBy('name')->get(),
            'availableOffices' => $availableOffices,
            'officeUsers' => $officeUsers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('offices', 'name')],
            'location' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'hours' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'create_account' => ['nullable', 'boolean'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['nullable', Rule::in([User::ROLE_OFFICE, User::ROLE_DEPARTMENT])],
            'position' => ['nullable', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        if ($request->boolean('create_account')) {
            $rules['account_name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $data = $request->validate($rules);

        $office = Office::create([
            'name' => $data['name'],
            'location' => $data['location'] ?? null,
            'contact' => $data['contact'] ?? null,
            'hours' => $data['hours'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        if ($request->boolean('create_account')) {
            $this->createAccount($office, $data, $request->user());

            AuditLogger::log('office.created', 'Created department '.$data['name'].' with login account for '.$data['email'].'.', $request->user());

            return back()->with('status', 'Department '.$data['name'].' and office account for '.$data['email'].' created successfully.');
        }

        AuditLogger::log('office.created', 'Created department ' . $data['name'] . '.', $request->user());

        return back()->with('status', 'Department ' . $data['name'] . ' added.');
    }

    /**
     * The login the office signs in with, created by the administrator from
     * the same form that registers the office. Both rows are written from one
     * place so the account can never carry an office the office card does not
     * show: the office value comes from the row just created, not from what
     * was typed.
     *
     * The account lands approved and verified - an administrator created it,
     * so there is nobody left to approve and no link to click.
     */
    private function createAccount(Office $office, array $data, User $admin): User
    {
        return User::create([
            'name' => $data['account_name'],
            'email' => $data['email'],
            'role' => $data['role'] ?? User::ROLE_OFFICE,
            'office' => OfficeEnum::fromTyped($office->name),
            'position' => $data['position'] ?? 'Office Head / Staff',
            'contact_number' => $office->contact,
            'password' => $data['password'],
            'is_active' => true,
            'approval_status' => User::APPROVAL_APPROVED,
            'approved_at' => now(),
            'approved_by' => $admin->id,
            'email_verified_at' => now(),
        ]);
    }

    public function update(Request $request, Office $office): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('offices', 'name')->ignore($office->id)],
            'location' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'hours' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $office->update($data + ['is_active' => $request->boolean('is_active')]);

        AuditLogger::log('office.updated', 'Updated department ' . $office->name . '.', $request->user());

        return back()->with('status', 'Department updated.');
    }

    public function destroy(Office $office, Request $request): RedirectResponse
    {
        $name = $office->name;
        $office->delete();

        AuditLogger::log('office.deleted', 'Deleted department ' . $name . '.', $request->user());

        return back()->with('status', 'Department ' . $name . ' removed.');
    }
}