<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restore the time-slot system.
 *
 * This is the exact inverse of 2026_10_19_000000_remove_time_slot_system,
 * written as a forward migration so the schema is restored by the normal
 * deploy `migrate` rather than a manual rollback against production.
 *
 * It is idempotent: on a database where the drop migration never ran (a
 * fresh test database, where the drop migration file is absent), every
 * column / index / table already exists and this is a no-op. On a database
 * where the drop already ran (production), it recreates exactly what was
 * removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- appointments: requested-slot columns + its index -------------
        if (! Schema::hasColumn('appointments', 'time_slot')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('time_slot')->nullable()->after('date');
            });
        }

        if (! Schema::hasColumn('appointments', 'original_time_slot')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('original_time_slot')->nullable()->after('original_date');
            });
        }

        if (! Schema::hasIndex('appointments', 'appointments_office_date_time_slot_index')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->index(['office', 'date', 'time_slot']);
            });
        }

        // The drop migration left a narrower (office, date) index behind;
        // remove it now that the three-column index is back.
        if (Schema::hasIndex('appointments', 'appointments_office_date_index')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex('appointments_office_date_index');
            });
        }

        // ---- offices: slot definitions + per-slot capacity ----------------
        if (! Schema::hasColumn('offices', 'default_slot_capacity')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->integer('default_slot_capacity')->nullable()->after('is_active');
            });
        }

        if (! Schema::hasColumn('offices', 'slot_definitions')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->json('slot_definitions')->nullable()->after('default_slot_capacity');
            });
        }

        // ---- per-slot capacity rules table --------------------------------
        if (! Schema::hasTable('slot_availabilities')) {
            Schema::create('slot_availabilities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('office_id')->constrained()->cascadeOnDelete();
                $table->date('date')->nullable()->index();
                $table->string('time_slot', 30);
                $table->integer('max_capacity')->default(1);
                $table->integer('booked_slots')->default(0);
                $table->string('status')->default('available');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Inverse of up(), kept idempotent for the same reason.
        if (Schema::hasIndex('appointments', 'appointments_office_date_time_slot_index')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex('appointments_office_date_time_slot_index');
            });
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['time_slot', 'original_time_slot']);
        });

        // Re-point the index at the columns that remain.
        if (! Schema::hasIndex('appointments', 'appointments_office_date_index')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->index(['office', 'date']);
            });
        }

        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn(['slot_definitions', 'default_slot_capacity']);
        });

        Schema::dropIfExists('slot_availabilities');
    }
};
