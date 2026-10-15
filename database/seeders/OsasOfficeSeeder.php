<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OsasOfficeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrNew(['email' => 'osas@isufst.edu.ph']);
        $user->fill([
            'name' => 'Office of Student Affairs & Services',
            'password' => Hash::make('password123'),
            // 'office', not a bespoke 'osas' role: that value is outside the
            // Role catalog, so Role::fromEffective() fell back to Student and
            // the account landed on the student portal with student
            // permissions instead of the office workspace.
            'role' => 'office',
            'office' => 'OSAS',
            'email_verified_at' => now(),
        ]);
        $user->save();

        if ($this->command) {
            $this->command->info('OSAS office account ready - osas@isufst.edu.ph / password123');
        }
    }
}
