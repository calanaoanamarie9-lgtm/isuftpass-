<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who a document may be requested by: 'student', 'alumni', or 'both'.
 * Defaults to 'both' so documents created before this column (and by the
 * Registrar UI without a choice) stay available to every requester.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('audience')->default('both')->after('fee');
        });

        // The deploy runs `migrate --force` but not the seeders, so the
        // standard catalogue's intended audiences are applied here. Only the
        // well-known seeded names are touched; everything else keeps the
        // 'both' default. Mirrors DocumentsSeeder.
        $audiences = [
            'Diploma' => 'alumni',
            'Permanent Record (F-137-A)' => 'student',
            'Good Moral Character' => 'student',
        ];

        foreach ($audiences as $name => $audience) {
            DB::table('documents')->where('name', $name)->update(['audience' => $audience]);
        }
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('audience');
        });
    }
};
