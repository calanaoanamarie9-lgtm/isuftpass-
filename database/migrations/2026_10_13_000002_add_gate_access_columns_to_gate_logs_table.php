<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The gate desk writes to gate_logs — it is already the table every scan
 * records to — but a guard log has to answer questions the original three
 * columns cannot: which pass was scanned, whose name was on it, and when
 * the person entered and left.
 *
 * user_id becomes nullable because visitors have no account. The existing
 * cascade stays: deleting a student still removes their gate history, and
 * NULL rows are untouched by it.
 *
 * The change and the additions run in separate calls because the SQLite
 * driver rewrites the table to alter a column, and a rewritten table must
 * not be asked to carry columns from the same pass.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gate_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('gate_logs', function (Blueprint $table) {
            $table->string('pass_type')->nullable();
            $table->unsignedBigInteger('pass_id')->nullable();
            $table->string('person_name')->nullable();
            $table->string('reference_code')->nullable();
            $table->string('destination')->nullable();
            $table->string('purpose')->nullable();
            $table->unsignedBigInteger('guard_id')->nullable();
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();

            $table->index('reference_code');
            $table->index(['scanned_at', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::table('gate_logs', function (Blueprint $table) {
            $table->dropIndex(['reference_code']);
            $table->dropIndex(['scanned_at', 'direction']);

            $table->dropColumn([
                'pass_type',
                'pass_id',
                'person_name',
                'reference_code',
                'destination',
                'purpose',
                'guard_id',
                'time_in',
                'time_out',
            ]);
        });

        Schema::table('gate_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
