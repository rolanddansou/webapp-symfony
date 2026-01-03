<?php

namespace App\Feature\Notification\Channel\Sms;

/**
 * Value object representing the result of an SMS send operation.
 */
final readonly class SmsResult
{
    private function __construct(
        public bool $success,
        public ?string $messageId,
        public ?string $errorCode,
        public ?string $errorMessage,
        public array $metadata,
    ) {}

    public static function success(string $messageId, array $metadata = []): self
    {
        return new self(
            success: true,
            messageId: $messageId,
            errorCode: null,
            errorMessage: null,
            metadata: $metadata,
        );
    }

    public static function failure(string $errorCode, string $errorMessage, array $metadata = []): self
    {
        return new self(
            success: false,
            messageId: null,
            errorCode: $errorCode,
            errorMessage: $errorMessage,
            metadata: $metadata,
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
