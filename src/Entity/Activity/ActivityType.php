<?php

namespace App\Entity\Activity;

class ActivityType
{
    public const PROFILE_UPDATED = 'PROFILE_UPDATED';
    public const MANUAL_NOTE = 'MANUAL_NOTE';

    // Auth activities
    public const EMAIL_VERIFIED = 'EMAIL_VERIFIED';
    public const PASSWORD_CHANGED = 'PASSWORD_CHANGED';
    public const LOGIN = 'LOGIN';
    public const LOGOUT = 'LOGOUT';
    public const REGISTER = 'REGISTER';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::PROFILE_UPDATED,
            self::MANUAL_NOTE,
            self::EMAIL_VERIFIED,
            self::PASSWORD_CHANGED,
            self::LOGIN,
            self::LOGOUT,
            self::REGISTER,
        ];
    }
}
