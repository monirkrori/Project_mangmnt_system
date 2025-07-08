<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Team\AddMemberRequest;
use App\Http\Requests\V1\Team\RemoveMemberRequest;
use App\Http\Requests\V1\Team\StoreTeamRequest;
use App\Http\Requests\V1\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use App\Services\V1\TeamService;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    protected TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->middleware('auth:sanctum');
        $this->teamService = $teamService;
    }

    /**
     * Get all teams.
     */
    public function index(): JsonResponse
    {
        $teams = $this->teamService->getAllTeams();
        return $this->successResponse($teams);
    }

    /**
     * Store a new team.
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = $this->teamService->createTeam($request->validated());

        return $this->successResponse(['team' => $team], 'Team created successfully.', 201);
    }

    public function show(int $teamId): JsonResponse
    {
        $team = $this->teamService->getTeamById($teamId);
        return $this->successResponse($team);
    }

    /**
     * Update a team.
     */

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $updated = $this->teamService->updateTeam($team, $request->validated());

        return $this->successResponse(['team' => $updated], 'Team updated successfully.', 200);
    }

    /**
     * Delete a team.
     */
    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $this->teamService->deleteTeam($team);

        return $this->successResponse([], 'Team deleted successfully.', 200);
    }

    /**
     * Add a user to the team.
     */
    public function addMember(AddMemberRequest $request, Team $team): JsonResponse
    {
        $this->authorize('manageMembers', $team);

        $user = User::findOrFail($request->validated()['user_id']);
        $this->teamService->addMember($team, $user);

        return $this->successResponse(['user' => $user], 'Member added successfully.', 200);
    }

    /**
     * Remove a user from the team.
     */
    public function removeMember(RemoveMemberRequest $request, Team $team): JsonResponse
    {
        $this->authorize('manageMembers', $team);

        $user = User::findOrFail($request->validated()['user_id']);
        $this->teamService->removeMember($team, $user);

        return $this->successResponse([], 'Member removed successfully.', 200);
    }

}
