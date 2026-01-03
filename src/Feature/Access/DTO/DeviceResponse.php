<?php

namespace App\Feature\Access\DTO;

use App\Entity\Access\UserDevice;

final class DeviceResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $deviceId,
        public readonly string $platform,
        public readonly ?string $pushToken,
        public readonly \DateTimeImmutable $lastActiveAt,
    ) {
    }

    public static function fromEntity(UserDevice $device): self
    {
        return new self(
            id: $device->getId(),
            deviceId: $device->getDeviceId(),
            platform: $device->getPlatform(),
            pushToken: $device->getPushToken(),
            lastActiveAt: \DateTimeImmutable::createFromInterface($device->getUpdatedAt() ?? $device->getCreatedAt()),
        );
    }
}
