<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The imported schema already had some of these columns, so add only
        // the ones still missing rather than failing on a duplicate.
        $missing = array_values(array_filter([
            'contact_number',
            'student_id',
            'course',
            'year_graduated',
            'organization',
            'address',
            'purpose',
            'relationship_to_student',
            'student_full_name',
        ], fn ($column) => ! Schema::hasColumn('users', $column)));

        if ($missing === []) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($missing) {
            foreach ($missing as $column) {
                $table->string($column)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'contact_number',
                'student_id',
                'course',
                'year_graduated',
                'organization',
                'address',
                'purpose',
                'relationship_to_student',
                'student_full_name',
            ]);
        });
    }
};