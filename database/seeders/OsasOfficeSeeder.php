<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OsasOfficeSeeder extends Seeder
{
    public function run(): void
    {
        // Supports both the legacy email (osas@) and the renamed account.
        $user = User::firstOrNew(['email' => 'osas@isufst.edu.ph']);
        $user->fill([
            'name' => 'Student Support Center',
            'password' => Hash::make('password123'),
            'role' => 'office',
            'office' => 'SSC',
            'email_verified_at' => now(),
        ]);
        $user->save();

        if ($this->command) {
            $this->command->info('SSC office account ready - osas@isufst.edu.ph / password123');
        }
    }
}
