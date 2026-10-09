<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $demoAdmins = [
            ['name' => 'Alice Administrator', 'email' => 'administrator@ceylonfocus.test', 'role' => Role::ADMINISTRATOR],
            ['name' => 'Ethan Editor', 'email' => 'editor@ceylonfocus.test', 'role' => Role::EDITOR],
            ['name' => 'Aisha Author', 'email' => 'author@ceylonfocus.test', 'role' => Role::AUTHOR],
        ];

        foreach ($demoAdmins as $demo) {
            $user = User::updateOrCreate(
                ['email' => $demo['email']],
                [
                    'name' => $demo['name'],
                    'password' => Hash::make('password'), // local dev only - see README
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $role = Role::where('slug', $demo['role'])->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        // A handful of regular (non-admin) site users.
        User::factory()->count(5)->create();
    }
}
