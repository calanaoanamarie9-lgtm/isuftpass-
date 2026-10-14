<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Split the applicant's name into its parts so the requisition form and other
 * official print-outs can render it as "[Last Name], [First Name] [Middle
 * Name]" without guessing. The existing `name` column stays as the display
 * name used across the app; these columns are left NULL for accounts created
 * before this migration (the User::formattedName() accessor falls back to
 * `name` for them until they fill the parts in via their profile).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
            $table->string('first_name')->nullable()->after('last_name');
            $table->string('middle_name')->nullable()->after('first_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'first_name', 'middle_name']);
        });
    }
};
