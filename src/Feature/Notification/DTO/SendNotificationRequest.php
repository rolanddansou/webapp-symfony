<?php

namespace App\Feature\Notification\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class SendNotificationRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $userId,

        #[Assert\NotBlank]
        public readonly string $type,

        #[Assert\NotBlank]
        public readonly string $title,

        #[Assert\NotBlank]
        public readonly string $message,

        public readonly ?array $data = null,

        public readonly ?string $actionUrl = null,

        public readonly ?string $actionLabel = null,

        public readonly ?int $priority = 0,
    ) {}
}
