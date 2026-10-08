<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Opening and closing time per office, in 24-hour H:i.
     *
     * The number of bookable slots in a day is derived from these two
     * values, so an office that stays open longer simply offers more
     * slots — the schedule is no longer a fixed list in the code.
     */
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->string('open_time', 5)->default('08:00');
            $table->string('close_time', 5)->default('17:00');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn(['open_time', 'close_time']);
        });
    }
};
