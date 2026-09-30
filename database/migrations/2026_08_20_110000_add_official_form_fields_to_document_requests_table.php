<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This schema was imported from a SQL dump created outside the
        // migration system, so the table may not be in the state the original
        // migration assumed. Check before dropping anything, using the
        // Schema builder so the migration stays portable across drivers.
        $hadPurpose = Schema::hasColumn('document_requests', 'purpose');

        if (Schema::hasColumn('document_requests', 'document_id')) {
            // The FK was never created by the import, so dropping it
            // unconditionally fails with "constraint does not exist".
            try {
                Schema::table('document_requests', function (Blueprint $table) {
                    $table->dropForeign(['document_id']);
                });
            } catch (\Throwable $e) {
                // No such constraint — the column can still be dropped below.
            }
        }

        Schema::table('document_requests', function (Blueprint $table) {
            if (Schema::hasColumn('document_requests', 'document_id')) {
                $table->dropColumn('document_id');
            }

            foreach ($this->newColumns() as $name => $definition) {
                if (! Schema::hasColumn('document_requests', $name)) {
                    $table->{$definition}($name)->nullable();
                }
            }
        });

        // purpose_type now exists, so the legacy free-text purpose can be
        // carried over instead of silently discarded when purpose drops.
        if ($hadPurpose) {
            DB::statement(
                'UPDATE document_requests SET purpose_type = purpose
                 WHERE purpose_type IS NULL AND purpose IS NOT NULL'
            );

            Schema::table('document_requests', function (Blueprint $table) {
                $table->dropColumn('purpose');
            });
        }
    }

    /**
     * Columns this migration introduces, keyed by column name.
     *
     * @return array<string, string>
     */
    private function newColumns(): array
    {
        return [
            'purpose_type' => 'string',
            'transfer_to' => 'text',
            'educational_status' => 'string',
            'educational_level' => 'string',
            'claim_mode' => 'string',
            'representative_name' => 'string',
            'others_specification' => 'string',
            'student_name' => 'string',
            'student_address' => 'text',
            'student_contact' => 'string',
            'student_course_year' => 'string',
        ];
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->foreignId('document_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('purpose')->nullable();

            $table->dropColumn([
                'purpose_type',
                'transfer_to',
                'educational_status',
                'educational_level',
                'claim_mode',
                'representative_name',
                'others_specification',
                'student_name',
                'student_address',
                'student_contact',
                'student_course_year',
            ]);
        });
    }
};
