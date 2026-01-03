<?php

namespace App\Feature\Access\DTO;

final class AuthResponse
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
        public readonly int $expiresIn,
        public readonly string $tokenType = 'Bearer',
        public readonly ?UserProfileResponse $user,
        public readonly bool $emailVerificationRequired = false,
    ) {}

    public static function create(
        string $accessToken,
        string $refreshToken,
        int $expiresIn,
        ?UserProfileResponse $user = null,
        bool $emailVerificationRequired = false,
    ): self {
        return new self($accessToken, $refreshToken, $expiresIn, 'Bearer', $user, $emailVerificationRequired);
    }
}
