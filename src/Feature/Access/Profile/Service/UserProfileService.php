<?php

namespace App\Feature\Access\Profile\Service;

use App\Entity\Access\UserProfile\UserProfile;
use App\Entity\Access\UserProfile\UserProfilePreferences;
use App\Feature\Access\Profile\DTO\UpdateUserProfileRequest;
use App\Feature\Access\Profile\DTO\UserProfilePreferencesRequest;
use App\Feature\Access\Profile\Event\UserProfileUpdatedEvent;
use App\Repository\Access\UserProfile\UserProfilePreferencesRepository;
use App\Repository\Access\UserProfile\UserProfileRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class UserProfileService implements UserProfileServiceInterface
{
    public function __construct(
        private UserProfileRepository            $customerRepository,
        private UserProfilePreferencesRepository $preferencesRepository,
        private EventDispatcherInterface         $eventDispatcher,
    ) {}

    public function getUserProfile(string $id): ?UserProfile
    {
        return $this->customerRepository->find($id);
    }

    public function updateCustomer(UserProfile $customer, UpdateUserProfileRequest $request): UserProfile
    {
        $changedFields = [];

        if ($request->fullName !== null && $request->fullName !== $customer->getFullName()) {
            $customer->setFullName($request->fullName);
            $changedFields[] = 'fullName';
        }

        if ($request->phoneNumber !== null && $request->phoneNumber !== $customer->getPhoneNumber()) {
            $customer->setPhoneNumber($request->phoneNumber);
            $changedFields[] = 'phoneNumber';
        }

        if (!empty($changedFields)) {
            $this->customerRepository->save($customer, true);

            $this->eventDispatcher->dispatch(
                new UserProfileUpdatedEvent($customer, $changedFields),
                UserProfileUpdatedEvent::NAME
            );
        }

        return $customer;
    }

    public function updatePreferences(UserProfile $customer, array $preferences): UserProfile
    {
        $customerPreferences = $customer->getPreferences();

        if (!$customerPreferences) {
            $customerPreferences = new UserProfilePreferences($customer);
            $customer->setPreferences($customerPreferences);
        }

        if (isset($preferences['language'])) {
            $customerPreferences->setLanguage($preferences['language']);
        }

        if (isset($preferences['marketingOptIn'])) {
            $customerPreferences->setMarketingOptIn($preferences['marketingOptIn']);
        }

        if (isset($preferences['pushEnabled'])) {
            $customerPreferences->setPushEnabled($preferences['pushEnabled']);
        }

        if (isset($preferences['emailEnabled'])) {
            $customerPreferences->setEmailEnabled($preferences['emailEnabled']);
        }

        $this->preferencesRepository->save($customerPreferences, true);

        return $customer;
    }

    public function updatePreferencesFromRequest(UserProfile $customer, UserProfilePreferencesRequest $request): UserProfile
    {
        $preferences = [];

        if ($request->language !== null) {
            $preferences['language'] = $request->language;
        }

        if ($request->marketingOptIn !== null) {
            $preferences['marketingOptIn'] = $request->marketingOptIn;
        }

        if ($request->pushEnabled !== null) {
            $preferences['pushEnabled'] = $request->pushEnabled;
        }

        if ($request->emailEnabled !== null) {
            $preferences['emailEnabled'] = $request->emailEnabled;
        }

        return $this->updatePreferences($customer, $preferences);
    }
}
