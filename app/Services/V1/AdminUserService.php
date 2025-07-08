<?php

namespace App\Services\V1;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminUserService
{
    /**
     * Get a paginated list of users with their roles and permissions.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listUsers()
    {
        return User::with('roles', 'permissions')->paginate(20);
    }

    /**
     * Create a new user and assign a role if provided.
     * Returns both the user and the generated access token.
     *
     * @param array $data
     * @return array{
     *     user: \App\Models\User,
     *     token: string
     * }
     */
    public function createUser(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        // Create a new access token for the user
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Update the user's details and optionally their role and password.
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return \App\Models\User
     */
    public function updateUser(User $user, array $data): User
    {
        $updateFields = [
            'name'  => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
        ];

        if (!empty($data['password'])) {
            $updateFields['password'] = Hash::make($data['password']);
        }

        $user->update($updateFields);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        Log::info('Admin updated user', [
            'admin_id' => auth()->id(),
            'updated_user_id' => $user->id,
            'changes' => $data,
        ]);

        return $user;
    }

    /**
     * Delete the given user.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    /**
     * Toggle the user's activation status (active/inactive).
     *
     * @param \App\Models\User $user
     * @return \App\Models\User
     */
    public function toggleActivation(User $user): User
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        return $user;
    }
}
