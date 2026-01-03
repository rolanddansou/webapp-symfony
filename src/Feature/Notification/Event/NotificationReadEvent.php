<?php

namespace App\Feature\Notification\Event;

use App\Entity\Notification\Notification;
use Symfony\Contracts\EventDispatcher\Event;

final class NotificationReadEvent extends Event
{
    public const NAME = 'notification.read';

    public function __construct(
        public readonly Notification $notification,
    ) {}
}
