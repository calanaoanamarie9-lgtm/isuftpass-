<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Office / staff self-registration: the applicant fills in the office form and
     * the account stays "pending" until an admin approves or rejects it.
     *
     * Existing accounts default to 'approved' so nothing that already works is
     * locked out by the new gate.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('approval_status')->default('approved');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            // Office profile captured by the applicant, verified by the admin.
            $table->string('position')->nullable();
            $table->string('employee_id')->nullable();

            $table->index(['role', 'approval_status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['role', 'approval_status']);
            $table->dropColumn([
                'approval_status',
                'approved_at',
                'approved_by',
                'rejection_reason',
                'position',
                'employee_id',
            ]);
        });
    }
};
