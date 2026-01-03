<?php

namespace App\Entity\Notification;

use App\Entity\Access\IdentityInterface;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Feature\Helper\DateHelper;
use App\Repository\Notification\UserNotificationPreferenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserNotificationPreferenceRepository::class)]
#[ORM\Table(name: 'user_notification_preferences')]
#[ORM\HasLifecycleCallbacks]
class UserNotificationPreference
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?IdentityInterface $user = null;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $emailEnabled = true;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $pushEnabled = true;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $smsEnabled = false;

    #[ORM\Column(type: Types::JSON)]
    private ?array $enabledNotificationTypes = [];

    #[ORM\Column(type: Types::JSON)]
    private ?array $channelPreferences = [];

    #[ORM\Column(options: ["default" => false])]
    private ?bool $marketingEnabled = false;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $quietHoursStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $quietHoursEnd = null;

    public function __construct()
    {
        $this->enabledNotificationTypes = [];
        $this->channelPreferences = [];
    }

    public function isNotificationTypeEnabled(string $type): bool
    {
        return in_array($type, $this->enabledNotificationTypes);
    }

    public function isChannelEnabled(string $channel): bool
    {
        return match ($channel) {
            NotificationDelivery::CHANNEL_EMAIL => $this->emailEnabled,
            NotificationDelivery::CHANNEL_PUSH, NotificationDelivery::CHANNEL_FCM => $this->pushEnabled,
            NotificationDelivery::CHANNEL_SMS => $this->smsEnabled,
            default => false,
        };
    }

    public function isInQuietHours(): bool
    {
        if ($this->quietHoursStart === null || $this->quietHoursEnd === null) {
            return false;
        }

        $now = DateHelper::nowUTC();
        $currentTime = $now->format('H:i:s');
        $start = $this->quietHoursStart->format('H:i:s');
        $end = $this->quietHoursEnd->format('H:i:s');

        if ($start < $end) {
            return $currentTime >= $start && $currentTime <= $end;
        } else {
            return $currentTime >= $start || $currentTime <= $end;
        }
    }

    // Getters and Setters
    public function getUser(): ?IdentityInterface
    {
        return $this->user;
    }

    public function setUser(?IdentityInterface $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function isEmailEnabled(): ?bool
    {
        return $this->emailEnabled;
    }

    public function setEmailEnabled(?bool $emailEnabled): self
    {
        $this->emailEnabled = $emailEnabled;
        return $this;
    }

    public function isPushEnabled(): ?bool
    {
        return $this->pushEnabled;
    }

    public function setPushEnabled(?bool $pushEnabled): self
    {
        $this->pushEnabled = $pushEnabled;
        return $this;
    }

    public function isSmsEnabled(): ?bool
    {
        return $this->smsEnabled;
    }

    public function setSmsEnabled(?bool $smsEnabled): self
    {
        $this->smsEnabled = $smsEnabled;
        return $this;
    }
}
