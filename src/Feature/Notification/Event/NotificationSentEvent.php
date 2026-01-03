<?php

namespace App\Feature\Notification\Event;

use App\Entity\Notification\Notification;
use Symfony\Contracts\EventDispatcher\Event;

final class NotificationSentEvent extends Event
{
    public const NAME = 'notification.sent';

    public function __construct(
        public readonly Notification $notification,
    ) {}
}
