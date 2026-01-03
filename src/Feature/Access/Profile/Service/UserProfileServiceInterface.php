<?php

namespace App\Feature\Access\Profile\Service;

use App\Entity\Access\UserProfile\UserProfile;
use App\Feature\Access\Profile\DTO\UpdateUserProfileRequest;

interface UserProfileServiceInterface
{
    public function getUserProfile(string $id): ?UserProfile;
    public function updateCustomer(UserProfile $customer, UpdateUserProfileRequest $request): UserProfile;
    public function updatePreferences(UserProfile $customer, array $preferences): UserProfile;
}
