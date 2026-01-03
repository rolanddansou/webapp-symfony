<?php

namespace App\Feature\Access\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RefreshTokenRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Refresh token is required')]
        public readonly string $refreshToken,

        #[Assert\NotBlank(message: 'Device ID is required')]
        public readonly string $deviceId,
    ) {}
}
