<?php

namespace App\Feature\Notification\Event;

use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DispatchResult;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a notification is successfully sent to at least one channel.
 */
final class NotificationDispatchedEvent extends Event
{
    public const NAME = 'notification.dispatched';

    public function __construct(
        public readonly NotificationMessage $message,
        public readonly DispatchResult $result,
    ) {}
}
