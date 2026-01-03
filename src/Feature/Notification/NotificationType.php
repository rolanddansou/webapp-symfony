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

    // Loyalty & Points
    public const POINTS_EARNED = 'points_earned';
    public const POINTS_SPENT = 'points_spent';
    public const POINTS_EXPIRING = 'points_expiring';
    public const CARD_ISSUED = 'card_issued';
    public const TIER_UPGRADED = 'tier_upgraded';
    public const TIER_DOWNGRADED = 'tier_downgraded';

    // Transactions
    public const TRANSACTION_COMPLETED = 'transaction_completed';
    public const TRANSACTION_FAILED = 'transaction_failed';
    public const TRANSACTION_REFUNDED = 'transaction_refunded';

    // Rewards & Offers
    public const REWARD_AVAILABLE = 'reward_available';
    public const REWARD_REDEEMED = 'reward_redeemed';
    public const REWARD_EXPIRING = 'reward_expiring';
    public const NEW_OFFER = 'new_offer';
    public const OFFER_EXPIRING = 'offer_expiring';

    // Vouchers
    public const VOUCHER_GENERATED = 'voucher_generated';
    public const VOUCHER_REDEEMED = 'voucher_redeemed';

    // Scans
    public const SCAN_COMPLETED = 'scan_completed';
    public const SCAN_FAILED = 'scan_failed';

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
            self::POINTS_EARNED,
            self::POINTS_SPENT,
            self::POINTS_EXPIRING,
            self::CARD_ISSUED,
            self::TIER_UPGRADED,
            self::TIER_DOWNGRADED,
            self::TRANSACTION_COMPLETED,
            self::TRANSACTION_FAILED,
            self::TRANSACTION_REFUNDED,
            self::REWARD_AVAILABLE,
            self::REWARD_REDEEMED,
            self::REWARD_EXPIRING,
            self::NEW_OFFER,
            self::OFFER_EXPIRING,
            self::VOUCHER_GENERATED,
            self::VOUCHER_REDEEMED,
            self::SCAN_COMPLETED,
            self::SCAN_FAILED,
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
            self::TRANSACTION_FAILED,
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
            self::NEW_OFFER,
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

            self::POINTS_EARNED,
            self::REWARD_REDEEMED,
            self::TRANSACTION_COMPLETED => ['push', 'in_app'],

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
                self::POINTS_EARNED => 'Points gagnés',
                self::POINTS_SPENT => 'Points dépensés',
                self::POINTS_EXPIRING => 'Points expirant bientôt',
                self::CARD_ISSUED => 'Carte émise',
                self::TIER_UPGRADED => 'Niveau augmenté',
                self::TIER_DOWNGRADED => 'Niveau diminué',
                self::TRANSACTION_COMPLETED => 'Transaction terminée',
                self::TRANSACTION_FAILED => 'Transaction échouée',
                self::TRANSACTION_REFUNDED => 'Transaction remboursée',
                self::REWARD_AVAILABLE => 'Récompense disponible',
                self::REWARD_REDEEMED => 'Récompense utilisée',
                self::REWARD_EXPIRING => 'Récompense expirant bientôt',
                self::NEW_OFFER => 'Nouvelle offre',
                self::OFFER_EXPIRING => 'Offre expirant bientôt',
                self::VOUCHER_GENERATED => 'Voucher généré',
                self::VOUCHER_REDEEMED => 'Voucher utilisé',
                self::SCAN_COMPLETED => 'Scan terminé',
                self::SCAN_FAILED => 'Scan échoué',
                self::PROMOTIONAL => 'Promotion',
                self::NEWSLETTER => 'Newsletter',
                self::SYSTEM_MAINTENANCE => 'Maintenance système',
                self::SYSTEM_UPDATE => 'Mise à jour système',
            ],
        ];

        return $labels[$locale][$type] ?? $type;
    }
}
