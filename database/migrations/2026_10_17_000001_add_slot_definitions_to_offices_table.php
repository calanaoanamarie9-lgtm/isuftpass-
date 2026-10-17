<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The slot times an office typed itself, as [{start, end}] rows in H:i.
     *
     * Null — every office until it retimes a slot on its Availability page —
     * means the day is built hour by hour from open_time and close_time as
     * before. Rows present mean the office's own times are the slot list
     * every date, booking form, and validation rule in the system reads.
     */
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->json('slot_definitions')->nullable()->after('close_time');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('slot_definitions');
        });
    }
};
