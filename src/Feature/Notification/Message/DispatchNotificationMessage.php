<?php

namespace App\Feature\Notification\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * Async message for dispatching notifications via Symfony Messenger.
 * This allows notification delivery to happen asynchronously.
 */
#[AsMessage('async')]
final readonly class DispatchNotificationMessage implements \JsonSerializable
{
    public function __construct(private NotificationMessage $message) {}

    /**
     * Create from a NotificationMessage value object.
     */
    public static function fromNotificationMessage(NotificationMessage $message): self {
        return new self(
            message: $message,
        );
    }

    /**
     * Convert back to NotificationMessage for processing.
     */
    public function toNotificationMessage(): NotificationMessage
    {
        return $this->message;
    }

    public function getRecipientId(): string
    {
        return $this->message->getRecipientId();
    }

    public function getRecipientEmail(): string
    {
        return $this->message->getRecipientEmail();
    }

    public function getType(): string
    {
        return $this->message->getType();
    }

    public function getTitle(): string
    {
        return $this->message->getTitle();
    }

    public function getBody(): string
    {
        return $this->message->getBody();
    }

    public function getData(): array
    {
        return $this->message->getData();
    }

    public function getActionUrl(): ?string
    {
        return $this->message->getActionUrl();
    }

    public function getActionLabel(): ?string
    {
        return $this->message->getActionLabel();
    }

    public function getPriority(): int
    {
        return $this->message->getPriority();
    }

    public function getChannels(): ?array
    {
        return $this->message->getChannels();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'message' => $this->message->toArray(),
        ];
    }
}
