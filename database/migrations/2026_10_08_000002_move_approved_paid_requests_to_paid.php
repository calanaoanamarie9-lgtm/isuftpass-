<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The pipeline order changed: the registrar now approves FIRST and the
 * cashier records the payment afterwards, so `for_signature` (Approved)
 * sits before `processing` (Paid) instead of after it.
 *
 * Every row sitting at for_signature was approved while payment was still
 * a prerequisite — it is paid by definition, which under the new order
 * means it belongs one step further along, at "Paid". Leaving it behind
 * would strand a paid request: it would sit in the cashier's "waiting for
 * payment" queue forever while the registrar's release button stayed
 * hidden behind the approval hand-off.
 *
 * An unpaid row at for_signature cannot be produced by the new code (the
 * cashier only accepts payment once the request is approved), so this
 * only ever touches the rows the old flow left behind.
 *
 * Running this in preDeployCommand fixes the data before the new code
 * goes live, so the deploy cannot show a half-updated pipeline.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('document_requests')
            ->whereNotNull('paid_at')
            ->where('status', 'for_signature')
            ->update([
                'status' => 'processing',
                'processing_at' => DB::raw('COALESCE(processing_at, paid_at)'),
            ]);
    }

    public function down(): void
    {
        // Deliberately irreversible. Moving a paid request back to
        // "Approved" would park it in front of the cashier again, which is
        // exactly the dead end this corrects.
    }
};
