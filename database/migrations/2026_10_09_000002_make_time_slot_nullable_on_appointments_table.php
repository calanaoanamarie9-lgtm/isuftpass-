<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Registrar does not ask students for a time — it answers with one when
 * it approves — so a Registrar booking has no requested slot to store. Other
 * offices still require a slot at booking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('time_slot')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('time_slot')->nullable(false)->change();
        });
    }
};
