<?php

namespace App\EventSubscriber\Notification;

use App\Feature\Notification\Service\NotificationServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens to domain events and sends appropriate notifications.
 * Decoupled from the actual sending mechanism.
 */
final readonly class DomainEventNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        //private NotificationServiceInterface $notificationService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [

        ];
    }

    /*
     * example event handler
    public function onPointsAdded(PointsAddedEvent $event): void
    {
        $card = $event->card;
        $customer = $card->getCustomer();

        if (!$customer instanceof UserProfile) {
            return;
        }

        $user = $customer->getUser();
        $storeName = $event->card->getMerchant()?->getMerchantName();

        $message = NotificationFactory::pointsEarned(
            user: $user,
            points: $event->points,
            reason: $event->reason,
            storeName: $storeName
        );

        $this->notificationService->dispatchAsync($message);
    }
    */
}
