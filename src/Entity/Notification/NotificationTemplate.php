<?php

namespace App\Entity\Notification;

use App\Entity\Trait\EnabledDisabledTrait;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;
use App\Repository\Notification\NotificationTemplateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationTemplateRepository::class)]
#[ORM\Table(name: 'notification_templates')]
#[ORM\HasLifecycleCallbacks]
class NotificationTemplate
{
    use IdTrait;
    use TimestampTrait;
    use EnabledDisabledTrait;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $bodyTemplate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $emailTemplate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $smsTemplate = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $availableVariables = null;

    #[ORM\Column(length: 50)]
    private ?string $notificationType = null;

    public function __construct()
    {
        $this->availableVariables = [];
    }

    // Getters and Setters
    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getBodyTemplate(): ?string
    {
        return $this->bodyTemplate;
    }

    public function setBodyTemplate(?string $bodyTemplate): self
    {
        $this->bodyTemplate = $bodyTemplate;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getEmailTemplate(): ?string
    {
        return $this->emailTemplate;
    }

    public function setEmailTemplate(?string $emailTemplate): void
    {
        $this->emailTemplate = $emailTemplate;
    }

    public function getSmsTemplate(): ?string
    {
        return $this->smsTemplate;
    }

    public function setSmsTemplate(?string $smsTemplate): void
    {
        $this->smsTemplate = $smsTemplate;
    }

    public function getAvailableVariables(): ?array
    {
        return $this->availableVariables;
    }

    public function setAvailableVariables(?array $availableVariables): void
    {
        $this->availableVariables = $availableVariables;
    }

    public function getNotificationType(): ?string
    {
        return $this->notificationType;
    }

    public function setNotificationType(?string $notificationType): void
    {
        $this->notificationType = $notificationType;
    }
}
