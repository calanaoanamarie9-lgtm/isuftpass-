<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@isufst.edu.ph'],
            [
                'name' => 'System Administrator',
                'role' => 'admin',
                'password' => 'password123',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'registrardingle@isufst.edu.ph'],
            [
                'name' => 'Registrar Staff',
                'role' => 'registrar',
                'password' => 'password123',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier@isufst.edu.ph'],
            [
                'name' => 'Cashier Staff',
                'role' => 'cashier',
                'password' => 'password123',
                'email_verified_at' => now(),
            ]
        );

        $student = User::firstOrCreate(
            ['email' => 'student@isufst.edu.ph'],
            [
                'name' => 'Student User',
                'role' => 'student',
                'password' => 'password123',
            ]
        );

        $student->studentProfile()->firstOrCreate([], [
            'student_id' => 'ISUFST-2024-0001',
            'course' => 'BS Information Technology',
            'year_level' => '3rd Year',
            'contact_number' => '09171234567',
            'address' => 'Barotac Nuevo, Iloilo',
            'pass_token' => (string) Str::uuid(),
        ]);
    }
}