<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_services', function (Blueprint $table) {
            $table->id();

            // Office enum value (CICI, CBMSD, COAG, COED, OSAS, Accounting, Library, Guidance)
            $table->string('office');

            $table->string('name');
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['office', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_services');
    }
};
