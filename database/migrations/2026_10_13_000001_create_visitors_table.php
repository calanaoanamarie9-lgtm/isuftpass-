<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visitor Gate Passes.
 *
 * A walk-in guest has no student account and no appointment, so the gate
 * has nothing to scan for them. This table is the record the guard desk
 * issues at the entrance and scans again at the exit.
 *
 * The QR carries `token`, the printed pass shows `reference_code` — kept
 * apart so the guard can search by the code without ever handling a token.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();

            $table->string('reference_code')->unique();
            $table->string('token')->unique();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('id_number')->nullable();
            $table->string('purpose');
            $table->string('destination_office');
            $table->string('host_name')->nullable();
            $table->string('photo')->nullable();

            $table->string('status')->default('active'); // active | revoked
            $table->date('valid_until')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
