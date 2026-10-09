<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'group'];

    // Canonical permission slugs. Keep in sync with PermissionSeeder.
    public const MANAGE_NEWS = 'manage-news';
    public const MANAGE_CATEGORIES = 'manage-categories';
    public const MANAGE_TAGS = 'manage-tags';
    public const MANAGE_VIDEOS = 'manage-videos';
    public const MANAGE_ADVERTISEMENTS = 'manage-advertisements';
    public const MANAGE_SOCIAL_LINKS = 'manage-social-links';
    public const MANAGE_SETTINGS = 'manage-settings';
    public const MANAGE_MEDIA = 'manage-media';
    public const MANAGE_USERS = 'manage-users';
    public const MANAGE_ADMINISTRATORS = 'manage-administrators';
    public const MANAGE_ROLES = 'manage-roles';
    public const VIEW_REPORTS = 'view-reports';
    public const VIEW_AUDIT_LOGS = 'view-audit-logs';
    public const PUBLISH_NEWS = 'publish-news';
    public const MANAGE_DEATH_NOTICES = 'manage-death-notices';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user');
    }
}
