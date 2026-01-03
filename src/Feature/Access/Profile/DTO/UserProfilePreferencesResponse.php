<?php

namespace App\Feature\Access\Profile\DTO;

use App\Entity\Access\UserProfile\UserProfilePreferences;

final readonly class UserProfilePreferencesResponse
{
    public function __construct(
        public string $language,
        public bool   $marketingOptIn,
        public bool   $pushEnabled,
        public bool   $emailEnabled,
    ) {}

    public static function fromEntity(UserProfilePreferences $preferences): self
    {
        return new self(
            language: $preferences->getLanguage(),
            marketingOptIn: $preferences->isMarketingOptIn(),
            pushEnabled: $preferences->isPushEnabled(),
            emailEnabled: $preferences->isEmailEnabled(),
        );
    }
}
