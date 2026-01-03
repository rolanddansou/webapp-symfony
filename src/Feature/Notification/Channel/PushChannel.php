<?php

namespace App\Feature\Notification\Channel;

use App\Entity\Access\UserDevice;
use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DeliveryResult;
use App\Repository\Access\UserDeviceRepository;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Push notification channel using Firebase Cloud Messaging (FCM).
 */
#[AutoconfigureTag('app.notification_channel')]
final class PushChannel implements NotificationChannelInterface
{
    public const CHANNEL_ID = 'push';

    private const TTL = '86400s'; // 24 hours
    private const ANDROID_COLOR = '#F99F00';

    public function __construct(
        //private readonly Messaging $messaging,
        private readonly UserDeviceRepository $deviceRepository,
        private readonly LoggerInterface $logger,
    ) {}

    public function getChannelId(): string
    {
        return self::CHANNEL_ID;
    }

    public function supports(NotificationMessage $message): bool
    {
        // Check if user has registered devices
        $devices = $this->deviceRepository->findActiveByUserId($message->getRecipientId());
        return !empty($devices);
    }

    public function getPriority(): int
    {
        return 10; // High priority - push notifications are typically most immediate
    }

    public function deliver(NotificationMessage $message): DeliveryResult
    {
        $devices = $this->deviceRepository->findActiveByUserId($message->getRecipientId());

        if (empty($devices)) {
            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: 'No active devices found for user',
                errorCode: 'no_devices'
            );
        }

        $successCount = 0;
        $failedTokens = [];
        $lastError = null;

        foreach ($devices as $device) {
            try {
                $this->sendToDevice($device, $message);
                $successCount++;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                $failedTokens[] = $device->getPushToken();

                // Log invalid tokens
                if ($this->isInvalidTokenError($e)) {
                    $device->setPushToken(null);
                    $this->deviceRepository->save($device, true);

                    $this->logger->info('Invalid FCM token detected', [
                        'device_id' => $device->getId(),
                    ]);
                }
            }
        }

        if ($successCount === 0) {
            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: $lastError ?? 'All delivery attempts failed',
                errorCode: 'all_failed',
                metadata: ['failed_tokens_count' => count($failedTokens)]
            );
        }

        $this->logger->info('Push notifications sent', [
            'recipient_id' => $message->getRecipientId(),
            'success_count' => $successCount,
            'failed_count' => count($failedTokens),
        ]);

        return DeliveryResult::success(
            channel: self::CHANNEL_ID,
            metadata: [
                'devices_targeted' => count($devices),
                'success_count' => $successCount,
                'failed_count' => count($failedTokens),
            ]
        );
    }

    private function sendToDevice(UserDevice $device, NotificationMessage $message): void
    {
        $data = $this->prepareData($message);
        $notification = $this->buildNotification($message);

        $cloudMessage = CloudMessage::withTarget('token', $device->getPushToken())
            ->withNotification($notification)
            ->withData($data)
            ->withAndroidConfig($this->buildAndroidConfig($message))
            ->withApnsConfig($this->buildApnsConfig($message))
            ->withHighestPossiblePriority();

        $this->messaging->send($cloudMessage);
    }

    private function buildNotification(NotificationMessage $message): Notification
    {
        return Notification::create($message->getTitle(), $message->getBody());
    }

    private function prepareData(NotificationMessage $message): array
    {
        $data = array_merge([
            'type' => $message->getType(),
            'priority' => (string) $message->getPriority(),
        ], $message->getData());

        if ($message->getActionUrl()) {
            $data['action_url'] = $message->getActionUrl();
            $data['action_label'] = $message->getActionLabel() ?? 'Voir';
        }

        // FCM requires all data values to be strings
        return array_map(function ($value) {
            if (is_array($value) || is_object($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            return (string) $value;
        }, $data);
    }

    private function buildAndroidConfig(NotificationMessage $message): AndroidConfig
    {
        return AndroidConfig::fromArray([
            'ttl' => self::TTL,
            'priority' => $message->isHighPriority() ? 'high' : 'normal',
            'notification' => [
                'color' => self::ANDROID_COLOR,
                'sound' => 'default',
                'click_action' => 'OPEN_APP',
            ],
        ]);
    }

    private function buildApnsConfig(NotificationMessage $message): ApnsConfig
    {
        return ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => $message->isHighPriority() ? '10' : '5',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $message->getTitle(),
                        'body' => $message->getBody(),
                    ],
                    'badge' => 1,
                    'sound' => 'default',
                ],
            ],
        ]);
    }

    private function isInvalidTokenError(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());
        return str_contains($message, 'not-registered')
            || str_contains($message, 'invalid-registration-token')
            || str_contains($message, 'entity was not found');
    }
}
