<?php

namespace App\Policies\V1;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{

/*
* admin has all permissions already
*/
    public function before(User $user, string $ability): bool|null
    {
        return $user->hasRole('admin') ? true : null;
    }
/*
* Determine if the given user can update the project.
*/
    public function update(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('update-project') && $user->id === $project->created_by_user_id;
    }

/*
* Determine if the given user can delete the project.
*/
    public function delete(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('delete-project') && $user->id === $project->created_by_user_id;
    }

/*
* Determine if the given user can view the project.
*/
    public function view(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('view-project');
    }

    /**
     * Determine if the given user can add a member to the project.
     */
    public function addMember(User $user, Project $project): bool
    {
        return $user->hasPermissionTo('manage-project-member') && $user->id === $project->created_by_user_id;
    }
}
