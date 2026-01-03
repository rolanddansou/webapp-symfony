<?php

namespace App\Feature\Access\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class ResetPasswordRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Code is required')]
        #[Assert\Length(exactly: 6)]
        public readonly string $code,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters')]
        public readonly string $newPassword,
    ) {}
}
