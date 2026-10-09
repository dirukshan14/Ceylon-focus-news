<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            CategorySeeder::class,
        ]);

        $this->createSuperAdmin();

        // SECURITY: demo accounts use the well-known password "password" - never
        // create them on a production server.
        if (app()->environment('production')) {
            $this->command?->warn('Production environment: demo users and demo content were NOT seeded.');

            return;
        }

        // Demo/sample content (local/dev only).
        $this->call([
            DemoUsersSeeder::class,
            DemoContentSeeder::class,
        ]);
    }

    /**
     * Create the initial Super Admin using credentials from .env.
     * These are for LOCAL DEVELOPMENT ONLY - see README for production guidance.
     */
    private function createSuperAdmin(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@ceylonfocus.test');
        $password = env('SUPER_ADMIN_PASSWORD', 'ChangeMe!12345');
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');

        // SECURITY: refuse to create a production super admin with the published default/weak password.
        if (app()->environment('production')
            && ($password === 'ChangeMe!12345' || strlen((string) $password) < 12)) {
            throw new \RuntimeException('Set a strong SUPER_ADMIN_PASSWORD (12+ chars, not the example value) in .env before seeding in production.');
        }

        $superAdmin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'status' => 'active',
                'email_verified_at' => now(),
                'must_change_password' => true,
            ]
        );
        // is_super_admin is intentionally not mass-assignable; set it explicitly.
        $superAdmin->forceFill(['is_super_admin' => true])->save();

        $superAdminRole = Role::where('slug', Role::SUPER_ADMIN)->first();
        if ($superAdminRole) {
            $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }
}
