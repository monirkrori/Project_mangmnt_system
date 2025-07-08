<?php

namespace App\Policies\V1;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Allow update only if the user is the owner and has 'update-team' permission.
     */
    public function update(User $user, Team $team): bool
    {
        return $user->hasPermissionTo('update-team') && $user->id === $team->owner_id;
    }

    /**
     * Allow delete only if the user is the owner and has 'delete-team' permission.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->hasPermissionTo('delete-team') && $user->id === $team->owner_id;
    }

    /**
     * Allow member management only if the user is the owner and has 'manage-team-members' permission.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        return $user->hasPermissionTo('manage-team-member') && $user->id === $team->owner_id;
    }
}
