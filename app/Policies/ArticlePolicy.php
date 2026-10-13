<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Only the admin may manage articles; everything else is denied.
     */
    public function before(User $user): ?bool
    {
        return (bool) $user->is_admin;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Article $article): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Article $article): bool
    {
        return true;
    }

    public function delete(User $user, Article $article): bool
    {
        return true;
    }

    public function restore(User $user, Article $article): bool
    {
        return true;
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return true;
    }
}
