<?php

namespace App\Feature\Notification\Result;

/**
 * Value object representing the result of a notification delivery attempt.
 */
final readonly class DeliveryResult
{
    private function __construct(
        private bool $success,
        private string $channel,
        private ?string $externalId = null,
        private ?string $errorMessage = null,
        private ?string $errorCode = null,
        private array $metadata = [],
    ) {}

    public static function success(string $channel, ?string $externalId = null, array $metadata = []): self
    {
        return new self(
            success: true,
            channel: $channel,
            externalId: $externalId,
            metadata: $metadata,
        );
    }

    public static function failure(string $channel, string $errorMessage, ?string $errorCode = null, array $metadata = []): self
    {
        return new self(
            success: false,
            channel: $channel,
            errorMessage: $errorMessage,
            errorCode: $errorCode,
            metadata: $metadata,
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isRetryable(): bool
    {
        // Common retryable error codes
        $retryableCodes = ['rate_limited', 'timeout', 'service_unavailable', 'temporary_failure'];
        
        return $this->isFailure() && in_array($this->errorCode, $retryableCodes, true);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'channel' => $this->channel,
            'external_id' => $this->externalId,
            'error_message' => $this->errorMessage,
            'error_code' => $this->errorCode,
            'metadata' => $this->metadata,
        ];
    }
}
