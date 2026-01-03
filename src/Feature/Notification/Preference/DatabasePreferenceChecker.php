<?php

namespace App\Feature\Notification\Preference;

use App\Entity\Notification\NotificationDelivery;
use App\Entity\Notification\UserNotificationPreference;
use App\Repository\Notification\UserNotificationPreferenceRepository;

/**
 * Database-backed preference checker using UserNotificationPreference entity.
 */
final class DatabasePreferenceChecker implements PreferenceCheckerInterface
{
    // Notification types that should bypass quiet hours
    private const URGENT_TYPES = [
        'security_alert',
        'password_reset',
        'account_locked',
        'transaction_failed',
    ];

    // Default channels when user has no preferences set
    private const DEFAULT_CHANNELS = [
        NotificationDelivery::CHANNEL_IN_APP,
        NotificationDelivery::CHANNEL_PUSH,
    ];

    public function __construct(
        private readonly UserNotificationPreferenceRepository $preferenceRepository,
    ) {}

    public function getEnabledChannels(string $userId, string $notificationType): array
    {
        $preference = $this->getPreference($userId);

        if (!$preference) {
            return self::DEFAULT_CHANNELS;
        }

        $channels = [];

        if ($preference->isEmailEnabled()) {
            $channels[] = NotificationDelivery::CHANNEL_EMAIL;
        }

        if ($preference->isPushEnabled()) {
            $channels[] = NotificationDelivery::CHANNEL_PUSH;
            $channels[] = NotificationDelivery::CHANNEL_FCM;
        }

        if ($preference->isSmsEnabled()) {
            $channels[] = NotificationDelivery::CHANNEL_SMS;
        }

        // In-app notifications are always enabled
        $channels[] = NotificationDelivery::CHANNEL_IN_APP;

        return array_unique($channels);
    }

    public function isChannelEnabled(string $userId, string $channelId): bool
    {
        $preference = $this->getPreference($userId);

        if (!$preference) {
            // Default: in_app and push are enabled
            return in_array($channelId, self::DEFAULT_CHANNELS, true);
        }

        return $preference->isChannelEnabled($channelId);
    }

    public function isInQuietHours(string $userId): bool
    {
        $preference = $this->getPreference($userId);

        if (!$preference) {
            return false;
        }

        return $preference->isInQuietHours();
    }

    public function filterChannelsByPreference(string $userId, string $notificationType, array $channelIds): array
    {
        $preference = $this->getPreference($userId);
        $isUrgent = in_array($notificationType, self::URGENT_TYPES, true);

        // Check quiet hours for non-urgent notifications
        if (!$isUrgent && $this->isInQuietHours($userId)) {
            // During quiet hours, only allow in-app notifications
            return array_filter($channelIds, fn($id) => $id === NotificationDelivery::CHANNEL_IN_APP);
        }

        // Filter by user preferences
        return array_filter(
            $channelIds,
            fn(string $channelId) => $this->isChannelEnabled($userId, $channelId)
        );
    }

    private function getPreference(string $userId): ?UserNotificationPreference
    {
        return $this->preferenceRepository->findOneByUserId($userId);
    }
}
