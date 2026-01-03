<?php

namespace App\Feature\Notification;

use App\Entity\Access\IdentityInterface;
use App\Feature\Helper\DateHelper;
use App\Feature\Notification\Message\NotificationMessage;

/**
 * Factory for creating notification messages with proper defaults.
 */
final class NotificationFactory
{
    /**
     * Create a welcome notification for a new user.
     */
    public static function welcome(IdentityInterface $user): NotificationMessage
    {
        return NotificationMessage::forUser(
            user: $user,
            type: NotificationType::WELCOME,
            title: 'Bienvenue sur Fidelys !',
            body: 'Merci de nous avoir rejoint. Commencez à collecter des points dès maintenant.',
            data: ['user_name' => $user->getUserEmail()],
            channels: NotificationType::getDefaultChannels(NotificationType::WELCOME),
        );
    }

    /**
     * Create an email verification notification.
     */
    public static function emailVerification(IdentityInterface $user, string $code): NotificationMessage
    {
        return NotificationMessage::forUser(
            user: $user,
            type: NotificationType::EMAIL_VERIFICATION,
            title: 'Vérifiez votre adresse email',
            body: "Votre code de vérification est : {$code}. Ce code expire dans 15 minutes.",
            data: [
                'code' => $code,
                'expires_in' => '15 minutes',
            ],
            priority: 8,
            channels: ['email'],
        );
    }

    /**
     * Create a password reset notification.
     */
    public static function passwordReset(IdentityInterface $user, string $code): NotificationMessage
    {
        return NotificationMessage::forUser(
            user: $user,
            type: NotificationType::PASSWORD_RESET,
            title: 'Réinitialisation de votre mot de passe',
            body: "Votre code de réinitialisation est : {$code}. Ce code expire dans 15 minutes.",
            data: [
                'code' => $code,
                'expires_in' => '15 minutes',
            ],
            priority: 9,
            channels: ['email'],
        );
    }


    /**
     * Create a security alert notification.
     */
    public static function securityAlert(
        IdentityInterface $user,
        string $alertType,
        string $details
    ): NotificationMessage {
        return NotificationMessage::forUser(
            user: $user,
            type: NotificationType::SECURITY_ALERT,
            title: '⚠️ Alerte de sécurité',
            body: $details,
            data: [
                'alert_type' => $alertType,
                'details' => $details,
            ],
            priority: 10,
            channels: NotificationType::getDefaultChannels(NotificationType::SECURITY_ALERT),
        );
    }

    /**
     * Create a new device login notification.
     */
    public static function newDeviceLogin(
        IdentityInterface $user,
        string $deviceInfo,
        string $ipAddress
    ): NotificationMessage {
        return NotificationMessage::forUser(
            user: $user,
            type: NotificationType::NEW_DEVICE_LOGIN,
            title: '🔐 Nouvelle connexion détectée',
            body: "Une connexion a été détectée depuis un nouvel appareil : {$deviceInfo}",
            data: [
                'device_info' => $deviceInfo,
                'ip_address' => $ipAddress,
                'timestamp' => DateHelper::nowUTC()->format(\DateTimeInterface::ATOM),
            ],
            priority: 9,
            channels: NotificationType::getDefaultChannels(NotificationType::NEW_DEVICE_LOGIN),
        );
    }

    /**
     * Create a custom notification.
     */
    public static function custom(
        IdentityInterface $user,
        string $type,
        string $title,
        string $body,
        array $data = [],
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        int $priority = 0,
        ?array $channels = null,
    ): NotificationMessage {
        return NotificationMessage::forUser(
            user: $user,
            type: $type,
            title: $title,
            body: $body,
            data: $data,
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            priority: $priority,
            channels: $channels ?? NotificationType::getDefaultChannels($type),
        );
    }
}
