<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Announcements are gone from the system: no board, no admin broadcast page,
 * no dashboard card. The table stays only long enough to be dropped — and a
 * rollback puts it back, empty, with the same shape it had before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('announcements');
    }

    public function down(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('office')->nullable();
            $table->text('body');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }
};
