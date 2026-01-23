<?php

namespace App\Feature\Notification;

/**
 * Constants for notification types used throughout the application.
 */
final class NotificationType
{
    // Authentication & Security
    public const WELCOME = 'welcome';
    public const EMAIL_VERIFICATION = 'email_verification';
    public const PASSWORD_RESET = 'password_reset';
    public const PASSWORD_CHANGED = 'password_changed';
    public const SECURITY_ALERT = 'security_alert';
    public const NEW_DEVICE_LOGIN = 'new_device_login';

    // Marketing
    public const PROMOTIONAL = 'promotional';
    public const NEWSLETTER = 'newsletter';

    // System
    public const SYSTEM_MAINTENANCE = 'system_maintenance';
    public const SYSTEM_UPDATE = 'system_update';

    /**
     * Get all notification types.
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::WELCOME,
            self::EMAIL_VERIFICATION,
            self::PASSWORD_RESET,
            self::PASSWORD_CHANGED,
            self::SECURITY_ALERT,
            self::NEW_DEVICE_LOGIN,
            self::PROMOTIONAL,
            self::NEWSLETTER,
            self::SYSTEM_MAINTENANCE,
            self::SYSTEM_UPDATE,
        ];
    }

    /**
     * Check if the notification type requires immediate delivery.
     */
    public static function isUrgent(string $type): bool
    {
        return in_array($type, [
            self::SECURITY_ALERT,
            self::PASSWORD_RESET,
            self::NEW_DEVICE_LOGIN,
        ], true);
    }

    /**
     * Check if the notification type is marketing-related.
     */
    public static function isMarketing(string $type): bool
    {
        return in_array($type, [
            self::PROMOTIONAL,
            self::NEWSLETTER,
        ], true);
    }

    /**
     * Get default channels for a notification type.
     * @return string[]
     */
    public static function getDefaultChannels(string $type): array
    {
        return match ($type) {
            self::EMAIL_VERIFICATION,
            self::PASSWORD_RESET => ['email'],

            self::SECURITY_ALERT,
            self::NEW_DEVICE_LOGIN => ['email', 'push', 'in_app'],

            self::PROMOTIONAL,
            self::NEWSLETTER => ['email'],

            default => ['in_app', 'push'],
        };
    }

    /**
     * Get the label for a notification type.
     */
    public static function getLabel(string $type, string $locale = 'fr'): string
    {
        $labels = [
            'fr' => [
                self::WELCOME => 'Bienvenue',
                self::EMAIL_VERIFICATION => 'Vérification email',
                self::PASSWORD_RESET => 'Réinitialisation mot de passe',
                self::PASSWORD_CHANGED => 'Mot de passe modifié',
                self::SECURITY_ALERT => 'Alerte de sécurité',
                self::NEW_DEVICE_LOGIN => 'Nouvelle connexion',
                self::PROMOTIONAL => 'Promotion',
                self::NEWSLETTER => 'Newsletter',
                self::SYSTEM_MAINTENANCE => 'Maintenance système',
                self::SYSTEM_UPDATE => 'Mise à jour système',
            ],
        ];

        return $labels[$locale][$type] ?? $type;
    }
}
