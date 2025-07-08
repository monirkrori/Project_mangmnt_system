<?php

namespace App\Policies\V1;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{

    /**
     * admin have all permissions already
     */
    public function before(User $user, string $ability): bool|null
    {
        return $user->hasRole('admin') ? true : null;
    }

    /**
     * Determine whether the user can update the task.
     * Only Admins or task creators can update.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasRole('admin') || $user->id === $task->created_by;
    }

    /**
     * Determine whether the user can delete the task.
     * Only Admins or task creators can delete.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->hasRole('admin') || $user->id === $task->created_by;
    }

    /**
     * Determine whether the user can view the task
     * Only Admins or task creators and the asignee can view
     */
    public function view(User $user, Task $task): bool
    {
        return $user->hasRole('admin') || $user->id === $task->assigned_to || $user->id === $task->created_by;
    }
}
