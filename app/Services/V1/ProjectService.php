<?php

namespace App\Services\V1;

use App\Events\V1\Project\ProjectCreated;
use App\Events\V1\Project\ProjectUpdated;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    protected int $cacheDuration = 3600;
    protected string $projectsListKey = 'projects.list';
    protected string $projectDetailKeyPrefix = 'project.detail.';

    /**
     * Get all projects with optional caching.
     */
    public function getAllProjects(): Collection
    {
        return cache_remember($this->projectsListKey, $this->cacheDuration, function () {
            return Project::select('id', 'name', 'description', 'status', 'due_date', 'created_at', 'updated_at')
                ->latest()
                ->get();
        });
    }

    /**
     * Find a specific project by ID with relationships and caching.
     */
    public function findProjectById(int $id): ?Project
    {
        $cacheKey = $this->projectDetailKeyPrefix . $id;

        return cache_remember($cacheKey, $this->cacheDuration, function () use ($id) {
            return Project::with(['team', 'creator', 'members', 'tasks'])->find($id);
        });
    }

    /**
     * Create a new project and dispatch an event.
     */
    public function createProject(array $data, User $creator): Project
    {
        return DB::transaction(function () use ($data, $creator) {
            $data['created_by_user_id'] = $creator->id;
            $data['status'] = 'pending';

            $project = Project::create($data);

            if ($project->wasRecentlyCreated) {
                ProjectCreated::dispatch($project, $creator);
                cache_forget($this->projectsListKey);
            }

            return $project->loadMissing(['team', 'creator']);
        });
    }

    /**
     * Update an existing project and dispatch an event if changed.
     */
    public function updateProject(Project $project, array $data, User $updater): Project
    {
        $project->fill($data);

        if (!$project->isDirty()) {
            return $project->loadMissing(['team', 'creator']);
        }

        $changes = $project->getDirty();
        $project->save();

        ProjectUpdated::dispatch($project, $updater, $changes);
        cache_forget([
            $this->projectDetailKeyPrefix . $project->id,
            $this->projectsListKey
        ]);

        return $project->loadMissing(['team', 'creator']);
    }

    /**
     * Delete a project and clear related cache.
     */
    public function deleteProject(Project $project): void
    {
        $project->delete();
        cache_forget([
            $this->projectDetailKeyPrefix . $project->id,
            $this->projectsListKey
        ]);
    }

    /**
     * Add a member to a project.
     */
    public function addMember(Project $project, User $user): void
    {
        $project->members()->syncWithoutDetaching([$user->id]);
        cache_forget($this->projectDetailKeyPrefix . $project->id);
    }

    /**
     * Remove a member from a project.
     */
    public function removeMember(Project $project, User $user): void
    {
        if ($project->creator_id === $user->id) {
            throw new Exception('Cannot remove project creator.');
        }

        $project->members()->detach($user->id);
        cache_forget($this->projectDetailKeyPrefix . $project->id);
    }

    /**
     * Get active projects related to a user (basic example).
     */
    public function getActiveProjectsForUser(User $user): Collection
    {
        $cacheKey = "user.{$user->id}.active_projects";

        return cache_remember($cacheKey, $this->cacheDuration, function () {
            return Project::active()
                ->with(['team', 'creator'])
                ->get();
        });
    }


}
