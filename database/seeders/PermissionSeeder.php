<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Manage News', 'slug' => Permission::MANAGE_NEWS, 'group' => 'news'],
            ['name' => 'Publish News', 'slug' => Permission::PUBLISH_NEWS, 'group' => 'news'],
            ['name' => 'Manage Categories', 'slug' => Permission::MANAGE_CATEGORIES, 'group' => 'news'],
            ['name' => 'Manage Tags', 'slug' => Permission::MANAGE_TAGS, 'group' => 'news'],
            ['name' => 'Manage Videos', 'slug' => Permission::MANAGE_VIDEOS, 'group' => 'videos'],
            ['name' => 'Manage Death Notices', 'slug' => Permission::MANAGE_DEATH_NOTICES, 'group' => 'death_notices'],
            ['name' => 'Manage Advertisements', 'slug' => Permission::MANAGE_ADVERTISEMENTS, 'group' => 'ads'],
            ['name' => 'Manage Social Links', 'slug' => Permission::MANAGE_SOCIAL_LINKS, 'group' => 'social'],
            ['name' => 'Manage Settings', 'slug' => Permission::MANAGE_SETTINGS, 'group' => 'system'],
            ['name' => 'Manage Media Library', 'slug' => Permission::MANAGE_MEDIA, 'group' => 'system'],
            ['name' => 'Manage Users', 'slug' => Permission::MANAGE_USERS, 'group' => 'users'],
            ['name' => 'Manage Administrators', 'slug' => Permission::MANAGE_ADMINISTRATORS, 'group' => 'users'],
            ['name' => 'Manage Roles & Permissions', 'slug' => Permission::MANAGE_ROLES, 'group' => 'users'],
            ['name' => 'View Reports', 'slug' => Permission::VIEW_REPORTS, 'group' => 'system'],
            ['name' => 'View Audit Logs', 'slug' => Permission::VIEW_AUDIT_LOGS, 'group' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }
    }
}
