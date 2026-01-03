<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\Identity;
use App\Entity\Access\RefreshToken;
use App\Repository\Access\RefreshTokenRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class TokenService implements TokenServiceInterface
{
    private const ACCESS_TOKEN_TTL = 3600; // 1 hour
    private const REFRESH_TOKEN_TTL = 2592000; // 30 days

    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly RefreshTokenRepository $refreshTokenRepository,
    ) {}

    public function generateAccessToken(Identity $user): string
    {
        // Create a UserInterface wrapper for JWT
        $jwtUser = new IdentityUser($user);
        return $this->jwtManager->create($jwtUser);
    }

    public function generateRefreshToken(Identity $user, string $deviceId): RefreshToken
    {
        // Remove existing refresh token for this device
        $existingToken = $this->refreshTokenRepository->findOneBy([
            'user' => $user,
            'deviceId' => $deviceId,
        ]);

        if ($existingToken) {
            $this->refreshTokenRepository->remove($existingToken);
        }

        // Create new refresh token
        $refreshToken = new RefreshToken();
        $refreshToken->setToken(bin2hex(random_bytes(64)));
        $refreshToken->setUser($user);
        $refreshToken->setDeviceId($deviceId);
        $refreshToken->setExpiresAt(new \DateTimeImmutable('+' . self::REFRESH_TOKEN_TTL . ' seconds'));

        $this->refreshTokenRepository->save($refreshToken, true);

        return $refreshToken;
    }

    public function validateRefreshToken(string $token, string $deviceId): ?Identity
    {
        $refreshToken = $this->refreshTokenRepository->findOneBy([
            'token' => $token,
            'deviceId' => $deviceId,
        ]);

        if (!$refreshToken) {
            return null;
        }

        // Check if token is expired
        if ($refreshToken->getExpiresAt() < new \DateTimeImmutable()) {
            $this->refreshTokenRepository->remove($refreshToken, true);
            return null;
        }

        return $refreshToken->getUser();
    }

    public function invalidateRefreshToken(string $token): void
    {
        $refreshToken = $this->refreshTokenRepository->findOneBy(['token' => $token]);

        if ($refreshToken) {
            $this->refreshTokenRepository->remove($refreshToken, true);
        }
    }

    public function invalidateAllRefreshTokens(Identity $user): void
    {
        $tokens = $this->refreshTokenRepository->findBy(['user' => $user]);

        foreach ($tokens as $token) {
            $this->refreshTokenRepository->remove($token);
        }

        $this->refreshTokenRepository->flush();
    }

    public function getAccessTokenTtl(): int
    {
        return self::ACCESS_TOKEN_TTL;
    }
}
