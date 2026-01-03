<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\Identity;
use App\Entity\Access\RefreshToken;

interface TokenServiceInterface
{
    /**
     * Generate JWT access token for user
     */
    public function generateAccessToken(Identity $user): string;

    /**
     * Generate refresh token for user
     */
    public function generateRefreshToken(Identity $user, string $deviceId): RefreshToken;

    /**
     * Validate and get user from refresh token
     */
    public function validateRefreshToken(string $token, string $deviceId): ?Identity;

    /**
     * Invalidate refresh token (logout)
     */
    public function invalidateRefreshToken(string $token): void;

    /**
     * Invalidate all refresh tokens for user (logout from all devices)
     */
    public function invalidateAllRefreshTokens(Identity $user): void;

    /**
     * Get token expiration time in seconds
     */
    public function getAccessTokenTtl(): int;
}
