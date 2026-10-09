<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Site Administrator',
                'password' => Hash::make('Admin@12345'), // change this after first login!
                'role'     => 'admin',
            ]
        );
    }
}
