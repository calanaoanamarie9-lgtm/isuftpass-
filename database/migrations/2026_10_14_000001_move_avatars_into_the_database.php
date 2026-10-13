<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Profile photos used to live as files on whatever disk AVATAR_DISK named.
     * On Render that disk is ephemeral (wiped on every deploy) and, once it was
     * pointed at Supabase, the S3 credentials were never configured — so the
     * upload silently failed, the path was still written to the database, and
     * the rendered URL was dead. The photo "disappeared" every time.
     *
     * Storing the photo bytes directly in the shared Postgres database makes it
     * permanent: no ephemeral filesystem, no external bucket, no credential to
     * forget. avatar_data holds a full data URI ("data:image/jpeg;base64,...")
     * which the views render straight into <img src>.
     *
     * The old `avatar` path column is kept for externally-hosted (http) photos.
     * Any leftover *relative* path points at a file that no longer exists on any
     * backend, so it is cleared here — those accounts fall back to the clean
     * initial-letter placeholder instead of a broken image, and re-upload once.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->longText('avatar_data')->nullable();
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->longText('avatar_data')->nullable();
        });

        // Clear dead relative paths (files confirmed gone); keep http(s) URLs.
        DB::table('users')
            ->whereNotNull('avatar')
            ->where('avatar', 'not like', 'http%')
            ->update(['avatar' => null]);

        DB::table('student_profiles')
            ->whereNotNull('avatar')
            ->where('avatar', 'not like', 'http%')
            ->update(['avatar' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn('avatar_data');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_data');
        });
    }
};
