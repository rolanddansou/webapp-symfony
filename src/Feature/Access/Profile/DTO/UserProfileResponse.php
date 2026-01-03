<?php

namespace App\Feature\Access\Profile\DTO;

use App\Entity\Access\UserProfile\UserProfile;

final readonly class UserProfileResponse
{
    public function __construct(
        public string                          $id,
        public string                          $email,
        public string                          $fullName,
        public ?string                         $phoneNumber,
        public ?UserProfilePreferencesResponse $preferences,
        public ?\DateTimeImmutable             $createdAt,
    ) {}

    public static function fromEntity(UserProfile $customer): self
    {
        return new self(
            id: (string) $customer->getId(),
            email: $customer->getCustomerEmail(),
            fullName: $customer->getFullName() ?? '',
            phoneNumber: $customer->getPhoneNumber(),
            preferences: $customer->getPreferences()
                ? UserProfilePreferencesResponse::fromEntity($customer->getPreferences())
                : null,
            createdAt: $customer->getCreatedAt(),
        );
    }
}
