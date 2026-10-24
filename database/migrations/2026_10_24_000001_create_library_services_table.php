<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The library's auxiliary services — the ones that are not a consultation.
 *
 * The catalogue is data rather than markup so the library can describe a
 * service once and have the page, the search and the request panel read the
 * same words. Rows are seeded from App\Lib...
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_services', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            // Grouping chip on the page: research, access, awareness,
            // reproduction. See LibraryService::CATEGORIES.
            $table->string('category', 40);

            // One line for the card, and the whole thing for the panel opened
            // from it.
            $table->string('summary');
            $table->text('description');

            // How the student gets it, and what to bring. Requirements are
            // stored one per line so the panel can list them.
            $table->text('how_to_avail')->nullable();
            $table->text('requirements')->nullable();

            // Key into the icon map in the page — the page owns the drawings,
            // the row owns which one to use.
            $table->string('icon', 40)->default('book');

            // Whether the service is asked for (referral letter, scan, index)
            // or simply walked into (interactive zone, photocopying).
            $table->boolean('is_requestable')->default(false);

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_services');
    }
};
