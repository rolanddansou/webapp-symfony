<?php

namespace App\Feature\Activity\Exception;

use Symfony\Component\HttpFoundation\Response;

final class ActivityException extends \RuntimeException
{
    public static function notFound(string $activityId): self
    {
        return new self(
            "Activity not found: $activityId",
            Response::HTTP_NOT_FOUND
        );
    }

    public static function invalidType(string $type): self
    {
        return new self(
            "Invalid activity type: $type",
            Response::HTTP_BAD_REQUEST
        );
    }

    public static function invalidDateRange(): self
    {
        return new self(
            'Invalid date range: from date must be before to date',
            Response::HTTP_BAD_REQUEST
        );
    }

    public static function userNotFound(string $userId): self
    {
        return new self(
            "User not found for activity: $userId",
            Response::HTTP_NOT_FOUND
        );
    }

    public static function recordFailed(string $reason): self
    {
        return new self(
            "Failed to record activity: $reason",
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    public static function invalidPayload(string $reason): self
    {
        return new self(
            "Invalid activity payload: $reason",
            Response::HTTP_BAD_REQUEST
        );
    }
}
