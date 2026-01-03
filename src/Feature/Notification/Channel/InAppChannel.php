<?php

namespace App\Feature\Notification\Channel;

use App\Entity\Access\IdentityInterface;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationDelivery;
use App\Feature\Helper\DateHelper;
use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DeliveryResult;
use App\Repository\Access\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * In-app notification channel - stores notifications in the main Notification entity.
 */
#[AutoconfigureTag('app.notification_channel')]
final class InAppChannel implements NotificationChannelInterface
{
    public const CHANNEL_ID = 'in_app';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger,
    ) {}

    public function getChannelId(): string
    {
        return self::CHANNEL_ID;
    }

    public function supports(NotificationMessage $message): bool
    {
        // In-app notifications are always supported
        return true;
    }

    public function getPriority(): int
    {
        return 5; // Highest priority - always store in-app notifications
    }

    public function deliver(NotificationMessage $message): DeliveryResult
    {
        try {
            // Find the user identity
            $user = $this->userRepository->find($message->getRecipientId());

            if (!$user) {
                return DeliveryResult::failure(
                    channel: self::CHANNEL_ID,
                    errorMessage: 'User not found',
                    errorCode: 'user_not_found'
                );
            }

            $notification = new Notification();
            $notification->setUser($user);
            $notification->setType($message->getType());
            $notification->setTitle($message->getTitle());
            $notification->setMessage($message->getBody());
            $notification->setData($message->getData());
            $notification->setPriority($message->getPriority());
            $notification->setSentAt(DateHelper::nowUTC());

            if ($message->getActionUrl()) {
                $notification->setActionUrl($message->getActionUrl());
                $notification->setActionLabel($message->getActionLabel());
            }

            // Create the in-app delivery record
            $delivery = new NotificationDelivery();
            $delivery->setNotification($notification);
            $delivery->setChannel(self::CHANNEL_ID);
            $delivery->markAsSent();

            $notification->addDelivery($delivery);

            $this->entityManager->persist($notification);
            $this->entityManager->flush();

            $this->logger->debug('In-app notification created', [
                'notification_id' => $notification->getId(),
                'recipient_id' => $message->getRecipientId(),
                'type' => $message->getType(),
            ]);

            return DeliveryResult::success(
                channel: self::CHANNEL_ID,
                externalId: $notification->getId(),
                metadata: ['notification_id' => $notification->getId()]
            );

        } catch (\Throwable $e) {
            $this->logger->error('Failed to create in-app notification', [
                'recipient_id' => $message->getRecipientId(),
                'error' => $e->getMessage(),
            ]);

            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: $e->getMessage(),
                errorCode: 'database_error'
            );
        }
    }
}
