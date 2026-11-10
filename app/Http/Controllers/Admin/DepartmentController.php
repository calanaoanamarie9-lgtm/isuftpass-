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

    /**
     * This page owns the office directory; the login that signs in for an
     * office is created in User Management's Add Office form, which also
     * offers an office that already exists. Keeping one place means an office
     * cannot end up with two different account-creation paths.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('offices', 'name')],
            'location' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'hours' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Office::create($data + ['is_active' => true]);

        AuditLogger::log('office.created', 'Created department ' . $data['name'] . '.', $request->user());

        return back()->with('status', 'Department ' . $data['name'] . ' added. Create its login in User Management → Add Office.');
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