<?php

use App\Support\RolePermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Role-based access control catalog.
 *
 * roles / permissions are the canonical tables; permission_role links them.
 * The four public account types (student, alumni, parent, guest) and the nine
 * staff roles all live in `roles`, distinguished by is_staff.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->boolean('is_staff')->default(false);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        // Populate the catalog from the Role / Permission enums so the tables
        // are never left empty by a `migrate`-only deploy (the container runs
        // `migrate --force`, not the seeders).
        RolePermissionCatalog::sync();
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
