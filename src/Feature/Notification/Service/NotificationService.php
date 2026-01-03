<?php

namespace App\Feature\Notification\Service;

use App\Entity\Access\IdentityInterface;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationDelivery;
use App\Feature\Notification\Dispatcher\NotificationDispatcherInterface;
use App\Feature\Notification\Event\NotificationReadEvent;
use App\Feature\Notification\Event\NotificationSentEvent;
use App\Feature\Notification\Message\DispatchNotificationMessage;
use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DispatchResult;
use App\Repository\Notification\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Main notification service that orchestrates notification creation and delivery.
 * Uses the dispatcher for multi-channel delivery while maintaining backward compatibility.
 */
final readonly class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
        private NotificationDispatcherInterface $dispatcher,
        #[Target('notification.logger')]
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus
    ) {
    }

    /**
     * Simple API for sending notifications - creates notification entity and dispatches.
     */
    public function send(
        IdentityInterface $user,
        string $type,
        string $title,
        string $message,
        ?array $data = null
    ): Notification {
        // Create and persist the notification entity first
        $notification = $this->createNotificationEntity($user, $type, $title, $message, $data);

        // Create a message for the dispatcher
        $notificationMessage = NotificationMessage::forUser(
            user: $user,
            type: $type,
            title: $title,
            body: $message,
            data: array_merge($data ?? [], ['notification_id' => $notification->getId()]),
        );

        // Dispatch to channels (async-friendly)
        $this->dispatchAsync($notificationMessage);

        $this->eventDispatcher->dispatch(
            new NotificationSentEvent($notification),
            NotificationSentEvent::NAME
        );

        return $notification;
    }

    /**
     * Send notification with extended options.
     */
    public function sendWithOptions(
        IdentityInterface $user,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        int $priority = 0,
        ?\DateTimeInterface $expiresAt = null,
        ?array $channels = null
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setData($data);
        $notification->setActionUrl($actionUrl);
        $notification->setActionLabel($actionLabel);
        $notification->setPriority($priority);
        $notification->setExpiresAt($expiresAt);
        $notification->setSentAt(new \DateTimeImmutable());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // Create message with all options
        $notificationMessage = NotificationMessage::forUser(
            user: $user,
            type: $type,
            title: $title,
            body: $message,
            data: array_merge($data ?? [], ['notification_id' => $notification->getId()]),
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            priority: $priority,
            channels: $channels,
        );

        $this->dispatchAsync($notificationMessage);

        $this->eventDispatcher->dispatch(
            new NotificationSentEvent($notification),
            NotificationSentEvent::NAME
        );

        return $notification;
    }

    /**
     * Dispatch a pre-built notification message directly.
     */
    public function dispatch(NotificationMessage $message): DispatchResult
    {
        return $this->dispatcher->dispatch($message);
    }

    public function markAsRead(Notification $notification): void
    {
        if (!$notification->isRead()) {
            $notification->markAsRead();
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(
                new NotificationReadEvent($notification),
                NotificationReadEvent::NAME
            );
        }
    }

    public function markAllAsRead(IdentityInterface $user): int
    {
        return $this->notificationRepository->markAllAsReadForUser($user);
    }

    public function getUnread(IdentityInterface $user, int $limit = 50): array
    {
        return $this->notificationRepository->findUnreadByUser($user, $limit);
    }

    public function getRecent(IdentityInterface $user, int $limit = 50, ?string $type = null, bool $unreadOnly = false): array
    {
        return $this->notificationRepository->findRecentByUser($user, $limit, $type, $unreadOnly);
    }

    public function countUnread(IdentityInterface $user): int
    {
        return $this->notificationRepository->countUnreadByUser($user);
    }

    public function getById(string $id): ?Notification
    {
        return $this->notificationRepository->find($id);
    }

    public function delete(Notification $notification): void
    {
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }

    /**
     * Create and persist a notification entity.
     */
    private function createNotificationEntity(
        IdentityInterface $user,
        string $type,
        string $title,
        string $message,
        ?array $data
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setData($data);
        $notification->setSentAt(new \DateTimeImmutable());

        // Create a pending in_app delivery
        $delivery = new NotificationDelivery();
        $delivery->setNotification($notification);
        $delivery->setChannel(NotificationDelivery::CHANNEL_IN_APP);
        $delivery->markAsSent();
        $notification->addDelivery($delivery);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * Dispatch notification message - can be made async via Messenger.
     */
    public function dispatchAsync(NotificationMessage $message): void
    {
        try {
            $this->messageBus->dispatch(new DispatchNotificationMessage(message: $message));
        } catch (ExceptionInterface $e) {
            $this->logger->error('Failed to dispatch notification message: ' . $e->getMessage(), [
                'exception' => $e,
                'recipient_id' => $message->getRecipientId(),
                'type' => $message->getType(),
            ]);
        }
    }
}
