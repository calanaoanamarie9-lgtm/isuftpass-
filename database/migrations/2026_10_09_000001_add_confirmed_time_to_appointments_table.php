<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The time this office told the student to come.
     *
     * A student asks for a slot when they book; the office answers with the
     * time it actually wants them there, chosen when the appointment is
     * approved. Both are kept: the request says what was wanted, the
     * confirmed time says what was granted.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('confirmed_time')->nullable()->after('time_slot');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('confirmed_time');
        });
    }
};
