<?php

namespace App\Feature\Notification\Service;

use App\Entity\Access\IdentityInterface;
use App\Entity\Notification\Notification;
use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DispatchResult;

interface NotificationServiceInterface
{
    /**
     * Send a notification using the simple API (creates and dispatches).
     */
    public function send(IdentityInterface $user, string $type, string $title, string $message, ?array $data = null): Notification;

    /**
     * Dispatch a pre-built notification message.
     */
    public function dispatch(NotificationMessage $message): DispatchResult;

    /**
     * Dispatch a notification message asynchronously.
     */
    public function dispatchAsync(NotificationMessage $message): void;

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): void;

    /**
     * Mark all notifications for a user as read.
     */
    public function markAllAsRead(IdentityInterface $user): int;

    /**
     * Get unread notifications for a user.
     * @return Notification[]
     */
    public function getUnread(IdentityInterface $user, int $limit = 50): array;

    /**
     * Get recent notifications for a user.
     * @return Notification[]
     */
    public function getRecent(IdentityInterface $user, int $limit = 50, ?string $type = null, bool $unreadOnly = false): array;

    /**
     * Count unread notifications for a user.
     */
    public function countUnread(IdentityInterface $user): int;

    /**
     * Get a notification by ID.
     */
    public function getById(string $id): ?Notification;

    /**
     * Delete a notification.
     */
    public function delete(Notification $notification): void;
}
