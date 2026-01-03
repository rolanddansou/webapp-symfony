<?php

namespace App\Feature\Notification\Preference;

use App\Feature\Notification\Message\NotificationMessage;

/**
 * Interface for user notification preferences.
 * Allows checking if a user wants to receive notifications through specific channels.
 */
interface PreferenceCheckerInterface
{
    /**
     * Get the channels that the user has enabled for this notification type.
     * @return string[]
     */
    public function getEnabledChannels(string $userId, string $notificationType): array;

    /**
     * Check if a specific channel is enabled for the user.
     */
    public function isChannelEnabled(string $userId, string $channelId): bool;

    /**
     * Check if the user is currently in quiet hours.
     */
    public function isInQuietHours(string $userId): bool;

    /**
     * Filter channels based on user preferences.
     * @param string[] $channelIds
     * @return string[]
     */
    public function filterChannelsByPreference(string $userId, string $notificationType, array $channelIds): array;
}
