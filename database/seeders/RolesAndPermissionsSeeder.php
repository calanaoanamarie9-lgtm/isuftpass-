<?php

namespace Database\Seeders;

use App\Support\RolePermissionCatalog;
use Illuminate\Database\Seeder;

/**
 * Mirrors the Role / Permission enums into the roles, permissions and
 * permission_role tables. Idempotent: re-running it re-syncs the catalog.
 *
 * The same sync also runs from the create_roles_and_permissions_tables
 * migration, so a production deploy that only runs `migrate --force` still
 * gets a populated catalog.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        RolePermissionCatalog::sync();
    }
}
