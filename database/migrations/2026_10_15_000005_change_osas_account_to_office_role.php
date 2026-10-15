<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Move the OSAS office account off the bespoke 'osas' role and onto the
 * 'office' role the catalog already knows.
 *
 * 'osas' is not a case in App\Enums\Role, so Role::fromEffective('osas')
 * fell back to Student: the office account was served the student dashboard
 * and sidebar with student document-request permissions, while being shut
 * out of the office workspace, which is guarded by role:department,office.
 *
 * 'office' is the role every self-registered office already uses. It resolves
 * to Role::Office (staff permissions, no document requests), sends
 * /dashboard to the office workspace scoped by officeScope() — which reads
 * office = 'OSAS' — and lets Office::registeredOffices() offer OSAS for
 * booking. OsasOfficeSeeder creates the account the same way from here on.
 *
 * Runs as a migration because the deploy runs `migrate --force` and not the
 * seeders, so this is the only thing that will correct the existing account.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'osas')
            ->update(['role' => 'office']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'office')
            ->where('office', 'OSAS')
            ->update(['role' => 'osas']);
    }
};
