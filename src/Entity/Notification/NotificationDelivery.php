<?php

namespace App\Entity\Notification;

use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Feature\Helper\DateHelper;
use App\Repository\Notification\NotificationDeliveryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationDeliveryRepository::class)]
#[ORM\Table(name: 'notification_deliveries')]
#[ORM\HasLifecycleCallbacks]
class NotificationDelivery
{
    public const CHANNEL_FCM = 'fcm';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_PUSH = 'push';
    public const CHANNEL_IN_APP = 'in_app';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DELIVERED = 'delivered';

    use IdTrait;
    use TimestampTrait;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Notification $notification = null;

    #[ORM\Column(length: 50)]
    private ?string $channel = null;

    #[ORM\Column(length: 50)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deliveredAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(nullable: true)]
    private ?int $attempts = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    public function __construct()
    {
        $this->metadata = [];
    }

    public function markAsSent(): self
    {
        $this->status = self::STATUS_SENT;
        $this->sentAt = DateHelper::nowUTC();
        return $this;
    }

    public function markAsDelivered(): self
    {
        $this->status = self::STATUS_DELIVERED;
        $this->deliveredAt = DateHelper::nowUTC();
        return $this;
    }

    public function markAsFailed(string $error): self
    {
        $this->status = self::STATUS_FAILED;
        $this->errorMessage = $error;
        $this->attempts++;
        return $this;
    }

    // Getters and Setters
    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): self
    {
        $this->notification = $notification;
        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(?string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getAttempts(): ?int
    {
        return $this->attempts;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function __toString(): string
    {
        return $this->notification?->getTitle() ?? "";
    }
}
