<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rename OSAS → SSC everywhere, deactivate the Admin and Guidance office rows,
 * and sync the related appointments, consultation services, and user accounts.
 *
 * All changes are idempotent (wrapped in WHERE conditions) so re-running
 * this migration after a fresh seed is safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── offices table ───────────────────────────────────────────────
        // Rename the OSAS office row to SSC.
        DB::table('offices')
            ->where('name', 'OSAS')
            ->update([
                'name' => 'SSC',
                'description' => 'Student services, activities, and welfare concerns.',
                'location' => 'Student Center, 2nd Floor',
                'updated_at' => now(),
            ]);

        // Deactivate Admin (no appointments; kept for historical data integrity).
        DB::table('offices')
            ->where('name', 'Admin')
            ->update(['is_active' => false, 'updated_at' => now()]);

        // Deactivate Guidance (historical appointment retained, not deleted).
        DB::table('offices')
            ->where('name', 'Guidance')
            ->update(['is_active' => false, 'updated_at' => now()]);

        // ── users table ─────────────────────────────────────────────────
        // Update the SSC office account (was osas@isufst.edu.ph, office = OSAS).
        DB::table('users')
            ->where('office', 'OSAS')
            ->update([
                'office' => 'SSC',
                'name' => 'Student Support Center',
                'updated_at' => now(),
            ]);

        // ── appointments table ───────────────────────────────────────────
        // Re-point the two OSAS appointments to SSC so they remain accessible
        // from the SSC workspace without any orphaned office name.
        DB::table('appointments')
            ->where('office', 'OSAS')
            ->update(['office' => 'SSC', 'updated_at' => now()]);

        // ── consultation_services table ──────────────────────────────────
        // Re-key existing SSC consultation services from the old OSAS name.
        DB::table('consultation_services')
            ->where('office', 'OSAS')
            ->update(['office' => 'SSC', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Reverse: restore OSAS name and reactivate Admin/Guidance.
        DB::table('offices')
            ->where('name', 'SSC')
            ->update(['name' => 'OSAS', 'updated_at' => now()]);

        DB::table('offices')
            ->where('name', 'Admin')
            ->update(['is_active' => true, 'updated_at' => now()]);

        DB::table('offices')
            ->where('name', 'Guidance')
            ->update(['is_active' => true, 'updated_at' => now()]);

        DB::table('users')
            ->where('office', 'SSC')
            ->where('email', 'osas@isufst.edu.ph')
            ->update(['office' => 'OSAS', 'name' => 'Office of Student Affairs & Services', 'updated_at' => now()]);

        DB::table('appointments')
            ->where('office', 'SSC')
            ->update(['office' => 'OSAS', 'updated_at' => now()]);

        DB::table('consultation_services')
            ->where('office', 'SSC')
            ->update(['office' => 'OSAS', 'updated_at' => now()]);
    }
};
