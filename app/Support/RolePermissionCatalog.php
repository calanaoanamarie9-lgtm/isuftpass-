<?php

namespace App\Support;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\Permission;
use App\Models\Role;

/**
 * The single place that mirrors the Role / Permission enums into the
 * roles, permissions and permission_role tables.
 *
 * Called both by the create_roles_and_permissions_tables migration (so a
 * deploy that only runs `migrate` still gets a populated catalog) and by
 * RolesAndPermissionsSeeder (for local `db:seed`). Idempotent: re-running it
 * re-syncs the catalog.
 */
class RolePermissionCatalog
{
    public static function sync(): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission->value],
                ['label' => $permission->label(), 'group' => $permission->group()],
            );
        }

        // Drop permissions that are no longer defined in the enum.
        Permission::whereNotIn('name', PermissionEnum::values())->delete();

        $permissionIds = Permission::pluck('id', 'name');

        foreach (RoleEnum::cases() as $role) {
            $model = Role::updateOrCreate(
                ['name' => $role->value],
                [
                    'label' => $role->label(),
                    'is_staff' => $role->isStaff(),
                    'description' => $role->isStaff() ? 'Staff role' : 'Public account type',
                ],
            );

            $ids = collect($role->permissionNames())
                ->map(fn (string $name) => $permissionIds[$name] ?? null)
                ->filter()
                ->values()
                ->all();

            $model->permissions()->sync($ids);
        }

        // Drop roles that are no longer defined in the enum.
        Role::whereNotIn('name', RoleEnum::values())->delete();
    }
}
