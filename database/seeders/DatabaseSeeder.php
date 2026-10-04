<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            DocumentsSeeder::class,
            AnnouncementsSeeder::class,
            CiciDepartmentSeeder::class,
            CbmsdDepartmentSeeder::class,
            CoagDepartmentSeeder::class,
            CoedDepartmentSeeder::class,
            OsasOfficeSeeder::class,
            AccountingOfficeSeeder::class,
            LibraryOfficeSeeder::class,
            GuidanceOfficeSeeder::class,
            ConsultationServiceSeeder::class,
            WorkspaceSeeder::class,
        ]);
    }
}
