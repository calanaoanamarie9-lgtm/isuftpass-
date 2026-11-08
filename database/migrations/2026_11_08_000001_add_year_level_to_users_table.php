<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alumni now fill in the same personal details as students. Students keep
     * `year_level` on their student profile; everyone else who goes through
     * the complete-details step stores their details on the user row itself,
     * so alumni need the column here as well.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'year_level')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('year_level')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'year_level')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('year_level');
        });
    }
};
