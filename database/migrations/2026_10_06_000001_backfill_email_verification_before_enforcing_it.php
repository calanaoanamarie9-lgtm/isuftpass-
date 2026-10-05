<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Email verification became mandatory on the very deploy that runs this -
 * every authenticated group in routes/web.php now carries `verified`, and
 * complete-profile carries it in routes/auth.php.
 *
 * Accounts created before that day were never asked to verify, and an
 * account whose inbox cannot be reached would have no way back from the
 * prompt. Marking them here is the whole point of running this as
 * preDeployCommand: the data is fixed before the gate goes live, so
 * `php artisan migrate` can never lock an existing account out.
 *
 * Anything registering afterwards starts with email_verified_at = null, which
 * is exactly what the gate is for - this update does not run twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Deliberately irreversible. Un-verifying an account would put it
        // behind the gate again - the exact lockout this prevents.
    }
};
