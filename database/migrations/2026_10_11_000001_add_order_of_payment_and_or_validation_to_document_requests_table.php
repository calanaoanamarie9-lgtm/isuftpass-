<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Release of Credentials flow needs two artifacts the old pipeline
 * never kept: the Order of Payment the registrar issues at approval, and
 * the Official Receipt the student presents back for validation.
 *
 * Requests paid before this migration existed passed straight from
 * "Approved" to "Paid" with no OR presentation step, so their payment is
 * treated as already validated — otherwise every historic request would
 * come back demanding an OR the student handed over in person.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            // Step 2: the billing statement issued on approval.
            $table->string('op_number', 50)->nullable()->unique()->after('or_number');

            // Step 4: the OR details the student presents back to the
            // Registrar's Office, kept beside the cashier's own record so
            // the registrar can validate the two against each other.
            $table->string('submitted_or_number', 50)->nullable()->after('op_number');
            $table->date('submitted_or_date')->nullable()->after('submitted_or_number');
            $table->decimal('submitted_or_amount', 10, 2)->nullable()->after('submitted_or_date');
            $table->timestamp('or_submitted_at')->nullable()->after('submitted_or_amount');
            $table->timestamp('or_validated_at')->nullable()->after('or_submitted_at');
            $table->string('or_validation_note', 500)->nullable()->after('or_validated_at');
        });

        DB::table('document_requests')
            ->whereNotNull('paid_at')
            ->update(['or_validated_at' => DB::raw('paid_at')]);
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropUnique(['op_number']);
            $table->dropColumn([
                'op_number',
                'submitted_or_number',
                'submitted_or_date',
                'submitted_or_amount',
                'or_submitted_at',
                'or_validated_at',
                'or_validation_note',
            ]);
        });
    }
};
