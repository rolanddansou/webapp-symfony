<?php

namespace App\Feature\Notification\Channel;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * Registry that holds all notification channels and provides channel selection logic.
 */
final class ChannelRegistry
{
    /** @var NotificationChannelInterface[] */
    private array $channels = [];

    public function __construct(
        #[TaggedIterator('app.notification_channel')]
        iterable $channels
    ) {
        foreach ($channels as $channel) {
            $this->channels[$channel->getChannelId()] = $channel;
        }

        // Sort by priority (lower = higher priority)
        uasort($this->channels, fn($a, $b) => $a->getPriority() <=> $b->getPriority());
    }

    /**
     * Get a specific channel by ID.
     */
    public function get(string $channelId): ?NotificationChannelInterface
    {
        return $this->channels[$channelId] ?? null;
    }

    /**
     * Get all registered channels.
     * @return NotificationChannelInterface[]
     */
    public function all(): array
    {
        return $this->channels;
    }

    /**
     * Get channels that support the given notification.
     * @return NotificationChannelInterface[]
     */
    public function getSupportingChannels(\App\Feature\Notification\Message\NotificationMessage $message): array
    {
        return array_filter(
            $this->channels,
            fn(NotificationChannelInterface $channel) => $channel->supports($message)
        );
    }

    /**
     * Get specific channels by IDs.
     * @param string[] $channelIds
     * @return NotificationChannelInterface[]
     */
    public function getByIds(array $channelIds): array
    {
        return array_filter(
            $this->channels,
            fn(NotificationChannelInterface $channel) => in_array($channel->getChannelId(), $channelIds, true)
        );
    }

    /**
     * Check if a channel exists.
     */
    public function has(string $channelId): bool
    {
        return isset($this->channels[$channelId]);
    }

    /**
     * Get all available channel IDs.
     * @return string[]
     */
    public function getAvailableChannelIds(): array
    {
        return array_keys($this->channels);
    }
}
