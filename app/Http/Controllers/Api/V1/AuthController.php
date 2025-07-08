<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Services\V1\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    protected AuthService $authService;

    /**
     * Inject the AuthService.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle user login and generate API token.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->login($request->email, $request->password);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse([$token, 'Login successfully '], 200);

    }

    /**
     * Revoke the user's current access token.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return $this->successResponse(['logout' => 'Logged out successfully'], 200);
    }
}
