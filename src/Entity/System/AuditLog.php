<?php

namespace App\Entity\System;

use App\Entity\Trait\IdTrait;
use App\Feature\Helper\DateHelper;
use App\Repository\System\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'system_audit_log', indexes: [
    new ORM\Index(columns: ['actor_type']),
    new ORM\Index(columns: ['actor_id']),
    new ORM\Index(columns: ['action']),
    new ORM\Index(columns: ['created_at'])
])]
class AuditLog
{
    use IdTrait;

    #[ORM\Column(length: 60)]
    private string $action;

    #[ORM\Column(type: "json")]
    private array $details = [];

    #[ORM\Column(length: 36, nullable: true)]
    private ?string $actorId;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $actorType; // "admin", "merchant_staff", "system"

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $action, array $details, ?string $actorId, ?string $actorType)
    {
        $this->action = $action;
        $this->details = $details;
        $this->actorId = $actorId;
        $this->actorType = $actorType;
        $this->createdAt = DateHelper::nowUTC();
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(array $details): void
    {
        $this->details = $details;
    }

    public function getActorId(): ?string
    {
        return $this->actorId;
    }

    public function setActorId(?string $actorId): void
    {
        $this->actorId = $actorId;
    }

    public function getActorType(): ?string
    {
        return $this->actorType;
    }

    public function setActorType(?string $actorType): void
    {
        $this->actorType = $actorType;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
