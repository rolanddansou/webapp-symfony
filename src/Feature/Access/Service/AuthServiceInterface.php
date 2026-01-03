<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\Identity;
use App\Feature\Access\DTO\AuthResponse;
use App\Feature\Access\DTO\LoginRequest;
use App\Feature\Access\DTO\RegisterRequest;

interface AuthServiceInterface
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): AuthResponse;

    /**
     * Authenticate user and return tokens
     */
    public function login(LoginRequest $request): AuthResponse;

    /**
     * Refresh access token using refresh token
     */
    public function refreshToken(string $refreshToken, string $deviceId): AuthResponse;

    /**
     * Logout user and invalidate refresh token
     */
    public function logout(Identity $user, string $deviceId): void;

    /**
     * Get current authenticated user profile
     */
    public function getCurrentUser(Identity $user): Identity;

    /**
     * Request password reset
     */
    public function requestPasswordReset(string $email): void;

    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): void;
}
