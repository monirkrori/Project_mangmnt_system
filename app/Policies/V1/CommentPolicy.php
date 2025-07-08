<?php

namespace App\Policies\V1;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * admin have all permissions already
     */
    public function before(User $user, string $ability): bool|null
    {
        return $user->hasRole('admin') ? true : null;
    }

    /**
     * Determine whether the user can update the comment
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the comment
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can view the comment
     */
    public function view(User $user, Comment $comment): bool
    {
        return true; // Public comments
    }
}
