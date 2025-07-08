<?php
 namespace App\Services\V1;

namespace App\Services\V1;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class TeamService
{
    protected string $cacheKey = 'teams';

    /**
     * Retrieve all teams with caching.
     */
    public function getAllTeams(): Collection
    {
        return Cache::remember($this->cacheKey, now()->addHours(12), function () {
            return Team::with('owner')->get();
        });
    }

    /**
     * Create a new team and assign current user as owner.
     */
    public function createTeam(array $data): Team
    {
        $data['owner_id'] = Auth::id();
        $team = Team::create($data);

        if ($team->wasRecentlyCreated) {
            Cache::forget($this->cacheKey);
        }

        return $team;
    }

    /**
     * Update a team's data and refresh cache if name changes.
     */
    public function updateTeam(Team $team, array $data): Team
    {
        $team->update($data);

        if ($team->wasChanged('name')) {
            Cache::forget($this->cacheKey);
        }

        Cache::put($this->cacheKey, Team::with('owner')->get(), now()->addHours(1));

        return $team;
    }

    /**
     * Find a team by its ID.
     */
    public function getTeamById(int $id): Team
    {
        return Team::with('owner')->findOrFail($id);
    }

    /**
     * Delete the given team and clear cache.
     */
    public function deleteTeam(Team $team): void
    {
        $team->delete();
        Cache::forget($this->cacheKey);
    }

    /**
     * Add a user to a team.
     */
    public function addMember(Team $team, User $user): void
    {
        $team->members()->attach($user->id);
        Cache::forget($this->cacheKey);
    }

    /**
     * Remove a user from a team.
     */
    public function removeMember(Team $team, User $user): void
    {
        $team->members()->detach($user->id);
        Cache::forget($this->cacheKey);
    }

}
