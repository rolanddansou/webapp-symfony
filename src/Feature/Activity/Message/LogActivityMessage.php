<?php

namespace App\Feature\Activity\Message;

use App\Feature\Helper\DateHelper;

/**
 * Async message for logging activities.
 * Allows activity logging to happen asynchronously without blocking the main request.
 */
final readonly class LogActivityMessage
{
    public function __construct(
        private string $actorId,
        private string $actorType,
        private string $action,
        private ?string $targetId = null,
        private ?string $targetType = null,
        private array $metadata = [],
        private ?\DateTimeImmutable $occurredAt = null,
    ) {
    }

    public static function create(
        string $actorId,
        string $actorType,
        string $action,
        ?string $targetId = null,
        ?string $targetType = null,
        array $metadata = [],
    ): self {
        return new self(
            actorId: $actorId,
            actorType: $actorType,
            action: $action,
            targetId: $targetId,
            targetType: $targetType,
            metadata: $metadata,
            occurredAt: DateHelper::nowUTC(),
        );
    }

    public function getActorId(): string
    {
        return $this->actorId;
    }

    public function getActorType(): string
    {
        return $this->actorType;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getTargetId(): ?string
    {
        return $this->targetId;
    }

    public function getTargetType(): ?string
    {
        return $this->targetType;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getOccurredAt(): ?\DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
