<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove the time-slot system.
 *
 * A booking now names a day and nothing else; the office answers with the
 * window it wants the student there when it approves, so the requested-slot
 * columns, the per-office slot definitions, the per-slot capacity, and the
 * slot_availabilities rules table all lose their reason to exist. Office
 * hours (open_time / close_time) stay: they still bound what an approval may
 * promise.
 *
 * The old (office, date, time_slot) index has to go before the column does —
 * SQLite refuses to drop a column that an index still points at — and is
 * replaced by an (office, date) index, which is the prefix the listings now
 * actually filter on.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite cannot DROP COLUMN while an index still references it.
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_office_date_time_slot_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['time_slot', 'original_time_slot']);
        });

        // Re-index on the key the app still queries: office scoped to a day.
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['office', 'date']);
        });

        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn(['slot_definitions', 'default_slot_capacity']);
        });

        Schema::dropIfExists('slot_availabilities');
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_office_date_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('time_slot')->nullable()->after('date');
            $table->string('original_time_slot')->nullable()->after('original_date');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['office', 'date', 'time_slot']);
        });

        Schema::table('offices', function (Blueprint $table) {
            $table->integer('default_slot_capacity')->nullable()->after('is_active');
            $table->json('slot_definitions')->nullable()->after('default_slot_capacity');
        });

        // The rules table is recreated rather than restored — its rows are
        // per-date overrides that the down migration cannot know.
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
};
