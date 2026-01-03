<?php

namespace App\Entity\Notification;

use App\Entity\Trait\AttributeTrait;
use App\Entity\Trait\EnabledDisabledTrait;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Feature\Helper\DateHelper;
use App\Repository\Notification\InAppNotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InAppNotificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class InAppNotification
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DELIVERED = 'delivered';

    use IdTrait;
    use TimestampTrait;
    use AttributeTrait;
    use EnabledDisabledTrait;

    public function __construct()
    {
        $this->channel = "info";
    }

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 100)]
    private ?string $channel = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $data = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastSentAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expectedConsumedAt = null;

    #[ORM\Column(length: 50)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column]
    private ?int $attempts = 0;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getLastSentAt(): ?\DateTimeImmutable
    {
        return $this->lastSentAt;
    }

    public function setLastSentAt(?\DateTimeImmutable $lastSentAt): static
    {
        $this->lastSentAt = $lastSentAt;

        return $this;
    }

    public function markAsSent(): self
    {
        $this->status = self::STATUS_SENT;
        $this->lastSentAt = DateHelper::nowUTC();
        return $this;
    }

    public function markAsFailed(string $error): self
    {
        $this->status = self::STATUS_FAILED;
        $this->errorMessage = $error;
        $this->attempts++;
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? "";
    }

    public function getExpectedConsumedAt(): ?\DateTimeImmutable
    {
        return $this->expectedConsumedAt;
    }

    public function setExpectedConsumedAt(?\DateTimeImmutable $expectedConsumedAt): static
    {
        $this->expectedConsumedAt = $expectedConsumedAt;

        return $this;
    }

    public function isExpectationPass(int $toleranceInMinutes = 2)
    {
        $expected = $this->getExpectedConsumedAt();

        if (!$expected) {
            return false;
        }

        $now = DateHelper::nowUTC();

        if ($toleranceInMinutes > 0) {
            $expected = $expected->modify("+{$toleranceInMinutes} minutes");
        }

        return $now > $expected;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getAttempts(): ?int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts): static
    {
        $this->attempts = $attempts;

        return $this;
    }
}
