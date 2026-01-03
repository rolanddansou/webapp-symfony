<?php

namespace App\Feature\Access\Profile\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserProfileRequest
{
    public function __construct(
        #[Assert\Length(min: 2, max: 120)]
        public ?string $fullName = null,

        #[Assert\Length(max: 30)]
        public ?string $phoneNumber = null,
    ) {}
}
