<?php

namespace App\Services\V1;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Attempt user login with email and password.
     *
     * @param string $email
     * @param string $password
     * @return User
     *
     * @throws ValidationException If login attempt is rate-limited or credentials are invalid.
     */
    public function login(string $email, string $password): User
    {
        $ipKey = 'login_attempts:' . request()->ip();

        // Rate limiting: max 5 attempts per 60 seconds
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again later.'],
            ]);
        }

        RateLimiter::hit($ipKey, 60);

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        return $user;
    }
}
