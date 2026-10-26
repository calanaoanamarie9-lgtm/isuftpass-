<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The feedback table predates the rating modal: it identifies the rated
     * record through a polymorphic pair. transaction_type is the same fact
     * in the vocabulary the modal and the reports use ("appointment" /
     * "document_request"), so filtering ratings no longer needs a class
     * name comparison.
     */
    public function up(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->string('transaction_type', 32)->nullable()->index();
        });

        // Ratings captured before this column existed keep their meaning.
        DB::table('feedbacks')
            ->where('feedbackable_type', 'like', '%Appointment')
            ->whereNull('transaction_type')
            ->update(['transaction_type' => 'appointment']);

        DB::table('feedbacks')
            ->where('feedbackable_type', 'like', '%DocumentRequest')
            ->whereNull('transaction_type')
            ->update(['transaction_type' => 'document_request']);
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropIndex(['transaction_type']);
            $table->dropColumn('transaction_type');
        });
    }
};
