<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add "Certificate of Grades (COG)" to the Registrar's document catalogue —
 * an academic record students request alongside the TOR and Good Moral.
 * Only students may request it (audience = 'student').
 *
 * A fresh install gets it from DocumentsSeeder; this migration covers an
 * already-populated catalogue, because the deploy runs `migrate --force` and
 * not the seeders. The guard below means it only touches a catalogue that
 * already exists, so `migrate:fresh` on an empty database (as the test suite
 * runs) is left untouched.
 */
return new class extends Migration
{
    private const NAME = 'Certificate of Grades (COG)';

    public function up(): void
    {
        if (! DB::table('documents')->exists()) {
            return;
        }

        if (DB::table('documents')->where('name', self::NAME)->exists()) {
            return;
        }

        DB::table('documents')->insert([
            'name' => self::NAME,
            'description' => 'Official certification of the grades a student earned in a term or school year.',
            'fee' => 60.00,
            'audience' => 'student',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('documents')->where('name', self::NAME)->delete();
    }
};
