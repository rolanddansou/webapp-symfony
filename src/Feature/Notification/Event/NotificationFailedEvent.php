<?php

namespace App\Feature\Notification\Event;

use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DeliveryResult;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a notification fails to be delivered through a channel.
 */
final class NotificationFailedEvent extends Event
{
    public const NAME = 'notification.failed';

    public function __construct(
        public readonly NotificationMessage $message,
        public readonly string $channelId,
        public readonly DeliveryResult $result,
    ) {}
}
