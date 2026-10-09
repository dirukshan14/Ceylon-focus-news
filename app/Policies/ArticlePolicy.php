<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\Permission;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([Permission::MANAGE_NEWS]);
    }

    public function view(User $user, Article $article): bool
    {
        return $user->hasPermission(Permission::MANAGE_NEWS)
            || $article->author_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::MANAGE_NEWS);
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->isSuperAdmin() || $user->hasRole('administrator', 'editor')) {
            return true;
        }

        // Authors/reporters may only edit their own, unpublished articles.
        return $article->author_id === $user->id
            && in_array($article->status, ['draft', 'pending_review'], true);
    }

    public function delete(User $user, Article $article): bool
    {
        if ($user->isSuperAdmin() || $user->hasRole('administrator', 'editor')) {
            return true;
        }

        return $article->author_id === $user->id && $article->status === 'draft';
    }

    public function publish(User $user, Article $article): bool
    {
        return $user->hasPermission(Permission::PUBLISH_NEWS);
    }
}
