<?php

namespace App\Feature\Notification\Channel;

use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DeliveryResult;

/**
 * Interface for notification delivery channels.
 * Each channel (Email, Push, SMS) implements this interface.
 */
interface NotificationChannelInterface
{
    /**
     * Get the unique identifier for this channel.
     */
    public function getChannelId(): string;

    /**
     * Check if this channel supports the given notification type.
     */
    public function supports(NotificationMessage $message): bool;

    /**
     * Deliver the notification through this channel.
     */
    public function deliver(NotificationMessage $message): DeliveryResult;

    /**
     * Get the priority of this channel (lower = higher priority).
     */
    public function getPriority(): int;
}
