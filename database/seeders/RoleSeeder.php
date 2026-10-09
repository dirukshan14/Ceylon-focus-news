<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin role exists mainly for display; actual super-admin bypass
        // logic lives on User::isSuperAdmin() via the is_super_admin flag, so
        // no permissions need to be attached here - it always passes every check.
        $superAdmin = Role::updateOrCreate(
            ['slug' => Role::SUPER_ADMIN],
            ['name' => 'Super Admin', 'description' => 'Full unrestricted access to everything.']
        );
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        $administrator = Role::updateOrCreate(
            ['slug' => Role::ADMINISTRATOR],
            ['name' => 'Administrator', 'description' => 'Manages assigned modules per granted permissions.']
        );
        $administrator->permissions()->sync(Permission::whereIn('slug', [
            Permission::MANAGE_NEWS, Permission::PUBLISH_NEWS, Permission::MANAGE_CATEGORIES,
            Permission::MANAGE_TAGS, Permission::MANAGE_VIDEOS, Permission::MANAGE_ADVERTISEMENTS,
            Permission::MANAGE_SOCIAL_LINKS, Permission::MANAGE_SETTINGS, Permission::MANAGE_MEDIA,
            Permission::MANAGE_DEATH_NOTICES, Permission::VIEW_REPORTS,
        ])->pluck('id'));

        $editor = Role::updateOrCreate(
            ['slug' => Role::EDITOR],
            ['name' => 'Editor', 'description' => 'Creates, edits and publishes news; manages categories/tags.']
        );
        $editor->permissions()->sync(Permission::whereIn('slug', [
            Permission::MANAGE_NEWS, Permission::PUBLISH_NEWS,
            Permission::MANAGE_CATEGORIES, Permission::MANAGE_TAGS, Permission::MANAGE_MEDIA,
            Permission::MANAGE_DEATH_NOTICES,
        ])->pluck('id'));

        $author = Role::updateOrCreate(
            ['slug' => Role::AUTHOR],
            ['name' => 'Author / Reporter', 'description' => 'Creates articles and submits for review; edits own drafts only.']
        );
        $author->permissions()->sync(Permission::whereIn('slug', [
            Permission::MANAGE_NEWS, Permission::MANAGE_MEDIA,
        ])->pluck('id'));
    }
}
