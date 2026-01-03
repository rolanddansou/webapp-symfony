<?php

namespace App\Feature\Notification\Exception;

use Symfony\Component\HttpFoundation\Response;

final class NotificationException extends \RuntimeException
{
    public static function notFound(string $notificationId): self
    {
        return new self(
            "Notification not found: $notificationId",
            Response::HTTP_NOT_FOUND
        );
    }

    public static function deliveryFailed(string $channel, string $reason): self
    {
        return new self(
            "Notification delivery failed on channel '$channel': $reason",
            Response::HTTP_SERVICE_UNAVAILABLE
        );
    }

    public static function invalidChannel(string $channel): self
    {
        return new self(
            "Invalid notification channel: $channel",
            Response::HTTP_BAD_REQUEST
        );
    }

    public static function channelNotConfigured(string $channel): self
    {
        return new self(
            "Notification channel not configured: $channel",
            Response::HTTP_SERVICE_UNAVAILABLE
        );
    }

    public static function userNotFound(string $userId): self
    {
        return new self(
            "User not found for notification: $userId",
            Response::HTTP_NOT_FOUND
        );
    }

    public static function templateNotFound(string $templateName): self
    {
        return new self(
            "Notification template not found: $templateName",
            Response::HTTP_NOT_FOUND
        );
    }

    public static function invalidTemplate(string $reason): self
    {
        return new self(
            "Invalid notification template: $reason",
            Response::HTTP_BAD_REQUEST
        );
    }

    public static function rateLimitExceeded(string $userId): self
    {
        return new self(
            "Notification rate limit exceeded for user: $userId",
            Response::HTTP_TOO_MANY_REQUESTS
        );
    }

    public static function alreadyRead(string $notificationId): self
    {
        return new self(
            "Notification already marked as read: $notificationId",
            Response::HTTP_CONFLICT
        );
    }

    public static function expired(string $notificationId): self
    {
        return new self(
            "Notification has expired: $notificationId",
            Response::HTTP_GONE
        );
    }

    public static function userPreferencesDisabled(string $channel): self
    {
        return new self(
            "User has disabled notifications for channel: $channel",
            Response::HTTP_FORBIDDEN
        );
    }

    public static function invalidPriority(int $priority): self
    {
        return new self(
            "Invalid notification priority: $priority",
            Response::HTTP_BAD_REQUEST
        );
    }
}
