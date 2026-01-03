<?php

namespace App\Feature\Access\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class DeviceRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 200)]
        public readonly string $deviceId,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['android', 'ios', 'web'])]
        public readonly string $platform,

        #[Assert\Length(max: 200)]
        public readonly ?string $pushToken = null,
    ) {
    }
}
