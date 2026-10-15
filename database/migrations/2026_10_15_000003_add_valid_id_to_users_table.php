<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Valid ID captured from guests (registration_type = 'guest') when they book
 * an appointment or request an entry pass.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'valid_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('valid_id')->nullable()->after('purpose');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'valid_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('valid_id');
        });
    }
};
