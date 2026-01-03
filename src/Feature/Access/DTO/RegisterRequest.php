<?php

namespace App\Feature\Access\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Invalid email format')]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters')]
        public readonly string $password,

        #[Assert\NotBlank(message: 'Full name is required')]
        #[Assert\Length(min: 2, max: 120)]
        public readonly string $fullName,

        #[Assert\Length(max: 30)]
        public readonly ?string $phoneNumber = null,

        #[Assert\NotBlank(message: 'Device ID is required')]
        public readonly ?string $deviceId = null,
    ) {}
}
