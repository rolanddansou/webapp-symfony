<?php

namespace App\Feature\Notification\DTO;

use App\Entity\Notification\Notification;

final readonly class NotificationResponse
{
    /**
     * @param string $id
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array<string, mixed>|null $data
     * @param bool $isRead
     * @param \DateTimeInterface|null $readAt
     * @param \DateTimeInterface|null $sentAt
     * @param string|null $actionUrl
     * @param string|null $actionLabel
     * @param int $priority
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(
        public string              $id,
        public string              $type,
        public string              $title,
        public string              $message,
        public ?array              $data,
        public bool                $isRead,
        public ?\DateTimeInterface $readAt,
        public ?\DateTimeInterface $sentAt,
        protected ?string          $actionUrl,
        protected ?string          $actionLabel,
        protected int              $priority,
        public \DateTimeImmutable  $createdAt,
    ) {}

    public static function fromEntity(Notification $notification): self
    {
        return new self(
            id: (string) $notification->getId(),
            type: $notification->getType() ?? '',
            title: $notification->getTitle() ?? '',
            message: $notification->getMessage() ?? '',
            data: !empty($notification->getData()) ? $notification->getData() : null,
            isRead: $notification->isRead(),
            readAt: $notification->getReadAt(),
            sentAt: $notification->getSentAt(),
            actionUrl: $notification->getActionUrl(),
            actionLabel: $notification->getActionLabel(),
            priority: $notification->getPriority() ?? 0,
            createdAt: $notification->getCreatedAt(),
        );
    }
}
