<?php

namespace App\Feature\Notification\Dispatcher;

use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DeliveryResult;
use App\Feature\Notification\Result\DispatchResult;

/**
 * Interface for notification dispatchers.
 */
interface NotificationDispatcherInterface
{
    /**
     * Dispatch a notification to all appropriate channels.
     */
    public function dispatch(NotificationMessage $message): DispatchResult;

    /**
     * Dispatch a notification to a specific channel.
     */
    public function dispatchToChannel(NotificationMessage $message, string $channelId): DeliveryResult;
}
