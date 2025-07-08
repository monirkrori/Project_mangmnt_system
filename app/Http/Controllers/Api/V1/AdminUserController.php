<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\StoreUserRequest;
use App\Http\Requests\V1\User\UpdateUserRequest;
use App\Models\User;
use App\Services\V1\AdminUserService;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    protected AdminUserService $service;

    /**
     * AdminUserController constructor
     *
     * @param AdminUserService $service
     */
    public function __construct(AdminUserService $service)
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
        $this->service = $service;
    }

    /**
     * Display a list of users with roles and permissions.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = $this->service->listUsers();
        return $this->successResponse($users, 'Users retrieved successfully');
    }

    /**
     * Store a new user and return access token.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $result = $this->service->createUser($request->validated());

        return $this->successResponse([
            'user' => $result['user'],
            'token' => $result['token']
        ], 'User created successfully', 201);
    }

    /**
     * Update an existing user.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->service->updateUser($user, $request->validated());
        return $this->successResponse($updatedUser, 'User updated successfully');
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->service->deleteUser($user);
        return $this->successResponse([], 'User deleted successfully');
    }

    /**
     * Toggle user's active status.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function toggleStatus(User $user): JsonResponse
    {
        $updated = $this->service->toggleActivation($user);
        return $this->successResponse($updated, 'User status toggled');
    }
}
