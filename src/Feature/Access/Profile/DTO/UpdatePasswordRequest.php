<?php

namespace App\Feature\Access\Profile\DTO;

final readonly class UpdatePasswordRequest
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
    ) {
    }
}
