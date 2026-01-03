<?php

namespace App\Feature\Access\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request DTO for verifying a code (pre-registration)
 */
final class VerifyCodeRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Invalid email format')]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Code is required')]
        #[Assert\Length(exactly: 6, exactMessage: 'Code must be exactly 6 digits')]
        public readonly string $code,
    ) {
    }
}
