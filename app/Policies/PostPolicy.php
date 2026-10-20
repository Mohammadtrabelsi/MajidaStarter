<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * Permission gates the whole post module (`manage posts`). Changing or removing
 * an existing post is further limited to its author, unless the user is an admin.
 */
class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage posts');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('manage posts');
    }

    public function create(User $user): bool
    {
        return $user->can('manage posts');
    }

    public function update(User $user, Post $post): bool
    {
        return $this->ownsOrAdministers($user, $post);
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->ownsOrAdministers($user, $post);
    }

    public function restore(User $user, Post $post): bool
    {
        return $this->ownsOrAdministers($user, $post);
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $this->ownsOrAdministers($user, $post);
    }

    private function ownsOrAdministers(User $user, Post $post): bool
    {
        return $user->can('manage posts')
            && ($user->isAdmin() || $post->user_id === $user->id);
    }
}
