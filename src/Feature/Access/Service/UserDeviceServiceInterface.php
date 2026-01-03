<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\IdentityInterface;
use App\Entity\Access\UserDevice;
use App\Feature\Access\DTO\DeviceRequest;

interface UserDeviceServiceInterface
{
    /**
     * Register or update a user device.
     */
    public function registerDevice(IdentityInterface $user, DeviceRequest $request): UserDevice;

    /**
     * Remove a user device.
     */
    public function removeDevice(IdentityInterface $user, string $deviceId): void;

    /**
     * Get all devices for a user.
     * @return UserDevice[]
     */
    public function getUserDevices(IdentityInterface $user): array;
}
