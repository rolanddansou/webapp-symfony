<?php

namespace App\Feature\Notification\Dispatcher;

use App\Feature\Notification\Channel\ChannelRegistry;
use App\Feature\Notification\Event\NotificationDispatchedEvent;
use App\Feature\Notification\Event\NotificationFailedEvent;
use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Preference\PreferenceCheckerInterface;
use App\Feature\Notification\Result\DeliveryResult;
use App\Feature\Notification\Result\DispatchResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Dispatches notifications to the appropriate channels based on user preferences.
 * Coordinates between channels, preferences, and delivery tracking.
 */
final readonly class NotificationDispatcher implements NotificationDispatcherInterface
{
    public function __construct(
        private ChannelRegistry $channelRegistry,
        private PreferenceCheckerInterface $preferenceChecker,
        private EventDispatcherInterface $eventDispatcher,
        #[Target('notification.logger')]
        private LoggerInterface $logger,
    ) {
    }

    public function dispatch(NotificationMessage $message): DispatchResult
    {
        $userId = $message->getRecipientId();
        $notificationType = $message->getType();

        // Determine which channels to use
        $channelIds = $this->determineChannels($message);

        $this->logger->info('Determined channels for notification', [
            'user_id' => $userId,
            'type' => $notificationType,
            'channels' => $channelIds,
        ]);

        if (empty($channelIds)) {
            $this->logger->info('No channels available for notification', [
                'user_id' => $userId,
                'type' => $notificationType,
            ]);

            return DispatchResult::noChannelsAvailable($message);
        }

        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($channelIds as $channelId) {
            $channel = $this->channelRegistry->get($channelId);

            if (!$channel) {
                $this->logger->warning('Channel not found', ['channel_id' => $channelId]);
                continue;
            }

            if (!$channel->supports($message)) {
                $this->logger->info('Channel does not support message', [
                    'channel_id' => $channelId,
                    'user_id' => $userId,
                    'type' => $notificationType,
                ]);
                continue;
            }

            try {
                $result = $channel->deliver($message);
                $results[$channelId] = $result;

                if ($result->isSuccess()) {
                    $successCount++;
                } else {
                    $failureCount++;
                    $this->handleDeliveryFailure($message, $channelId, $result);
                }
            } catch (\Throwable $e) {
                $this->logger->error('Channel delivery exception', [
                    'channel' => $channelId,
                    'error' => $e->getMessage(),
                ]);

                $result = DeliveryResult::failure($channelId, $e->getMessage(), 'exception');
                $results[$channelId] = $result;
                $failureCount++;

                $this->handleDeliveryFailure($message, $channelId, $result);
            }
        }

        $dispatchResult = new DispatchResult($message, $results, $successCount, $failureCount);

        // Dispatch success event if at least one channel succeeded
        if ($dispatchResult->hasAnySuccess()) {
            $this->eventDispatcher->dispatch(
                new NotificationDispatchedEvent($message, $dispatchResult),
                NotificationDispatchedEvent::NAME
            );
        }

        $this->logger->info('Notification dispatched', [
            'user_id' => $userId,
            'type' => $notificationType,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return $dispatchResult;
    }

    public function dispatchToChannel(NotificationMessage $message, string $channelId): DeliveryResult
    {
        $channel = $this->channelRegistry->get($channelId);

        if (!$channel) {
            return DeliveryResult::failure($channelId, 'Channel not found', 'channel_not_found');
        }

        if (!$channel->supports($message)) {
            return DeliveryResult::failure($channelId, 'Channel does not support this message', 'unsupported');
        }

        return $channel->deliver($message);
    }

    /**
     * Determine which channels should be used for this notification.
     * @return string[]
     */
    private function determineChannels(NotificationMessage $message): array
    {
        $userId = $message->getRecipientId();
        $notificationType = $message->getType();

        // If specific channels are requested, use those (filtered by preference)
        if ($message->getChannels() !== null) {
            return $this->preferenceChecker->filterChannelsByPreference(
                $userId,
                $notificationType,
                $message->getChannels()
            );
        }

        // Get enabled channels for this notification type
        $enabledChannels = $this->preferenceChecker->getEnabledChannels($userId, $notificationType);

        // Filter to only channels that support this message
        $supportingChannels = $this->channelRegistry->getSupportingChannels($message);
        $supportingChannelIds = array_map(fn($c) => $c->getChannelId(), $supportingChannels);

        return array_intersect($enabledChannels, $supportingChannelIds);
    }

    private function handleDeliveryFailure(NotificationMessage $message, string $channelId, DeliveryResult $result): void
    {
        $this->eventDispatcher->dispatch(
            new NotificationFailedEvent($message, $channelId, $result),
            NotificationFailedEvent::NAME
        );
    }
}
