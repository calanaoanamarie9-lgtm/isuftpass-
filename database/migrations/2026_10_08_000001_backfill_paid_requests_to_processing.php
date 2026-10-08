<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Payment used to be recorded without touching `status`, so the registrar's
 * pipeline showed a request as "Submitted" long after the cashier had taken
 * the money — the "Paid" label in registrar/document-requests/show is simply
 * the `processing` status, and reaching it took a separate approval click.
 *
 * PaymentController now moves submitted -> processing in the same write. This
 * migration does the same for rows that were already paid before that change:
 * without it, a request paid last week would still read "Submitted" forever,
 * because record() refuses to run a second time on a paid request.
 *
 * Requests the registrar has already advanced past processing (for_signature,
 * ready_for_pickup, completed) are deliberately left alone — they are further
 * along, not behind.
 *
 * Running this in preDeployCommand fixes the data before the new code goes
 * live, so the deploy cannot show a half-updated pipeline.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('document_requests')
            ->whereNotNull('paid_at')
            ->whereIn('status', ['submitted', 'payment_pending'])
            ->update([
                'status' => 'processing',
                'processing_at' => DB::raw('COALESCE(processing_at, paid_at)'),
            ]);
    }

    public function down(): void
    {
        // Deliberately irreversible. Reverting these rows to "submitted"
        // would put a paid request behind the payment gate again, which is
        // the mismatch this corrects.
    }
};
