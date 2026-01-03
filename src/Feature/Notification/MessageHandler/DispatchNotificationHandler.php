<?php

namespace App\Feature\Notification\MessageHandler;

use App\Feature\Notification\Dispatcher\NotificationDispatcherInterface;
use App\Feature\Notification\Message\DispatchNotificationMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handles async notification dispatch messages.
 */
#[AsMessageHandler]
final readonly class DispatchNotificationHandler
{
    public function __construct(
        private NotificationDispatcherInterface $dispatcher,
        private LoggerInterface                 $logger,
    ) {}

    public function __invoke(DispatchNotificationMessage $message): void
    {
        $this->logger->debug('Processing async notification dispatch', [
            'type' => $message->getType(),
            'recipient_id' => $message->getRecipientId(),
            'channel_ids' => $message->getChannels(),
        ]);

        try {
            $notificationMessage = $message->toNotificationMessage();
            $result = $this->dispatcher->dispatch($notificationMessage);

            if ($result->hasAllFailed()) {
                $this->logger->warning('All notification channels failed', [
                    'type' => $message->getType(),
                    'recipient_id' => $message->getRecipientId(),
                    'failed_channels' => $result->getFailedChannels(),
                ]);
            } else {
                $this->logger->info('Notification dispatched successfully', [
                    'type' => $message->getType(),
                    'recipient_id' => $message->getRecipientId(),
                    'successful_channels' => $result->getSuccessfulChannels(),
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to dispatch notification', [
                'type' => $message->getType(),
                'recipient_id' => $message->getRecipientId(),
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}
