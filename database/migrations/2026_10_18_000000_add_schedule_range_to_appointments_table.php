<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The office's approved answer used to be a single "come at" time
     * (confirmed_time). Approval now names a whole window the student is
     * expected inside, plus who set it — so the range and the approver are
     * stored beside the legacy column, which is kept as the display string
     * every pass card, email and notification already reads.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('confirmed_time');
            $table->time('end_time')->nullable()->after('start_time');
            $table->foreignId('approved_by')->nullable()->after('confirmed_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
