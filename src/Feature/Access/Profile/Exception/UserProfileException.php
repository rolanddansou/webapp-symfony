<?php

namespace App\Feature\Access\Profile\Exception;

use Symfony\Component\HttpFoundation\Response;

final class UserProfileException extends \RuntimeException
{
    public static function notFound(string $userId): self
    {
        return new self(
            "User not found: $userId",
            Response::HTTP_NOT_FOUND
        );
    }

    public static function alreadyExists(string $identifier): self
    {
        return new self(
            "User already exists with identifier: $identifier",
            Response::HTTP_CONFLICT
        );
    }

    public static function invalidData(string $reason): self
    {
        return new self(
            "Invalid user data: $reason",
            Response::HTTP_BAD_REQUEST
        );
    }

    public static function emailAlreadyInUse(string $email): self
    {
        return new self(
            "Email already in use: $email",
            Response::HTTP_CONFLICT
        );
    }

    public static function phoneAlreadyInUse(string $phone): self
    {
        return new self(
            "Phone number already in use: $phone",
            Response::HTTP_CONFLICT
        );
    }

    public static function preferencesNotFound(string $userId): self
    {
        return new self(
            "Preferences not found for user: $userId",
            Response::HTTP_NOT_FOUND
        );
    }
}
