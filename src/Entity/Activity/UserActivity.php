<?php

namespace App\Entity\Activity;

use App\Entity\Trait\IdTrait;
use App\Repository\Activity\UserActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserActivityRepository::class)]
#[ORM\Table(name: 'user_activity')]
#[ORM\Index(name: 'idx_activity_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_activity_type', columns: ['type'])]
#[ORM\Index(name: 'idx_activity_occurred', columns: ['occurred_at'])]
#[ORM\Index(name: 'idx_activity_actor', columns: ['actor_type','actor_id'])]
class UserActivity
{
    use IdTrait;

    #[ORM\Column(length: 36)]
    private string $userId;

    #[ORM\Column(length: 50)]
    private string $type;

    #[ORM\Column(type: 'json')]
    private array $payload = [];

    #[ORM\Column(length: 36, nullable: true)]
    private ?string $actorId = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $actorType = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $occurredAt;

    public function __construct(string $userId, string $type, array $payload = [], ?string $actorId = null, ?string $actorType = null)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->payload = $payload;
        $this->actorId = $actorId;
        $this->actorType = $actorType;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
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

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function setOccurredAt(\DateTimeImmutable $occurredAt): void
    {
        $this->occurredAt = $occurredAt;
    }
}
