<?php

namespace App\Feature\Access\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterSendCodeRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Invalid email format')]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters')]
        public readonly string $password,

        #[Assert\NotBlank(message: 'Password confirmation is required')]
        public readonly string $passwordConfirm,

        #[Assert\NotBlank(message: 'Full name is required')]
        #[Assert\Length(min: 2, max: 120)]
        public readonly string $fullName,

        #[Assert\Length(max: 30)]
        public readonly ?string $phoneNumber = null,
    ) {
    }

    public static function create(
        string $email,
        string $password,
        string $passwordConfirm,
        string $fullName,
        ?string $phoneNumber = null
    ): self {
        return new self(
            email: $email,
            password: $password,
            passwordConfirm: $passwordConfirm,
            fullName: $fullName,
            phoneNumber: $phoneNumber
        );
    }

    public function passwordsMatch(): bool
    {
        return $this->password === $this->passwordConfirm;
    }
}

