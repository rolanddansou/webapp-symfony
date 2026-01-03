<?php

namespace App\Feature\Notification\Result;

use App\Feature\Notification\Message\NotificationMessage;

/**
 * Result of dispatching a notification to multiple channels.
 */
final readonly class DispatchResult
{
    /**
     * @param DeliveryResult[] $channelResults
     */
    public function __construct(
        private NotificationMessage $message,
        private array $channelResults,
        private int $successCount,
        private int $failureCount,
    ) {}

    public static function noChannelsAvailable(NotificationMessage $message): self
    {
        return new self($message, [], 0, 0);
    }

    public function getMessage(): NotificationMessage
    {
        return $this->message;
    }

    /**
     * @return DeliveryResult[]
     */
    public function getChannelResults(): array
    {
        return $this->channelResults;
    }

    public function getResultForChannel(string $channelId): ?DeliveryResult
    {
        return $this->channelResults[$channelId] ?? null;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getTotalAttempts(): int
    {
        return $this->successCount + $this->failureCount;
    }

    public function hasAnySuccess(): bool
    {
        return $this->successCount > 0;
    }

    public function hasAllFailed(): bool
    {
        return $this->successCount === 0 && $this->failureCount > 0;
    }

    public function wasFullySuccessful(): bool
    {
        return $this->failureCount === 0 && $this->successCount > 0;
    }

    public function hadNoChannels(): bool
    {
        return empty($this->channelResults);
    }

    /**
     * Get list of successful channel IDs.
     * @return string[]
     */
    public function getSuccessfulChannels(): array
    {
        return array_keys(array_filter(
            $this->channelResults,
            fn(DeliveryResult $r) => $r->isSuccess()
        ));
    }

    /**
     * Get list of failed channel IDs.
     * @return string[]
     */
    public function getFailedChannels(): array
    {
        return array_keys(array_filter(
            $this->channelResults,
            fn(DeliveryResult $r) => $r->isFailure()
        ));
    }

    public function toArray(): array
    {
        return [
            'message_type' => $this->message->getType(),
            'recipient_id' => $this->message->getRecipientId(),
            'success_count' => $this->successCount,
            'failure_count' => $this->failureCount,
            'successful_channels' => $this->getSuccessfulChannels(),
            'failed_channels' => $this->getFailedChannels(),
            'channel_results' => array_map(fn($r) => $r->toArray(), $this->channelResults),
        ];
    }
}
