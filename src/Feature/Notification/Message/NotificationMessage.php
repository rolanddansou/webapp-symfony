<?php

namespace App\Feature\Notification\Message;

use App\Entity\Access\IdentityInterface;

/**
 * Value object representing a notification message to be sent.
 * Immutable and decoupled from the entity.
 */
final readonly class NotificationMessage
{
    public function __construct(
        private string $recipientId,
        private string $recipientEmail,
        private string $type,
        private string $title,
        private string $body,
        private array $data = [],
        private ?string $actionUrl = null,
        private ?string $actionLabel = null,
        private int $priority = 0,
        private ?array $channels = null,
        private ?string $locale = 'fr',
    ) {}

    public static function forUser(
        IdentityInterface $user,
        string            $type,
        string            $title,
        string            $body,
        array|null        $data = null,
        ?string           $actionUrl = null,
        ?string           $actionLabel = null,
        int|null          $priority = null,
        ?array            $channels = null,
    ): self {
        $data ??= [];
        $priority ??= 0;
        return new self(
            recipientId: $user->getUserId(),
            recipientEmail: $user->getUserEmail(),
            type: $type,
            title: $title,
            body: $body,
            data: $data,
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            priority: $priority,
            channels: $channels,
        );
    }

    public function getRecipientId(): string
    {
        return $this->recipientId;
    }

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function getActionLabel(): ?string
    {
        return $this->actionLabel;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getChannels(): ?array
    {
        return $this->channels;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function isHighPriority(): bool
    {
        return $this->priority >= 8;
    }

    public function withData(array $additionalData): self
    {
        return new self(
            recipientId: $this->recipientId,
            recipientEmail: $this->recipientEmail,
            type: $this->type,
            title: $this->title,
            body: $this->body,
            data: array_merge($this->data, $additionalData),
            actionUrl: $this->actionUrl,
            actionLabel: $this->actionLabel,
            priority: $this->priority,
            channels: $this->channels,
            locale: $this->locale,
        );
    }

    public function toArray(): array
    {
        return [
            'recipient_id' => $this->recipientId,
            'recipient_email' => $this->recipientEmail,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'action_url' => $this->actionUrl,
            'action_label' => $this->actionLabel,
            'priority' => $this->priority,
            'channels' => $this->channels,
            'locale' => $this->locale,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            recipientId: $data['recipient_id'],
            recipientEmail: $data['recipient_email'],
            type: $data['type'],
            title: $data['title'],
            body: $data['body'],
            data: $data['data'] ?? [],
            actionUrl: $data['action_url'] ?? null,
            actionLabel: $data['action_label'] ?? null,
            priority: $data['priority'] ?? 0,
            channels: $data['channels'] ?? null,
            locale: $data['locale'] ?? 'fr',
        );
    }
}
