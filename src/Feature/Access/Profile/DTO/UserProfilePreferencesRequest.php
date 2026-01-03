<?php

namespace App\Feature\Access\Profile\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserProfilePreferencesRequest
{
    public function __construct(
        #[Assert\Length(min: 2, max: 10)]
        public ?string $language = null,

        public ?bool   $marketingOptIn = null,

        public ?bool   $pushEnabled = null,

        public ?bool   $emailEnabled = null,
    ) {}
}
