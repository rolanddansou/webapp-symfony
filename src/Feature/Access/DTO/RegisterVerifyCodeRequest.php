<?php

namespace App\Feature\Access\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterVerifyCodeRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Invalid email format')]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Verification code is required')]
        #[Assert\Length(exactly: 6, exactMessage: 'Verification code must be 6 digits')]
        #[Assert\Regex(pattern: '/^\d{6}$/', message: 'Verification code must contain only digits')]
        public readonly string $code,
    ) {
    }

    public static function create(string $email, string $code): self
    {
        return new self(email: $email, code: $code);
    }
}

