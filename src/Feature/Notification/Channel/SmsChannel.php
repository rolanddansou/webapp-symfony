<?php

namespace App\Feature\Notification\Channel;

use App\Feature\Notification\Channel\Sms\SmsProviderInterface;
use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DeliveryResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * SMS notification channel.
 * Uses pluggable SMS providers (Twilio, Vonage, etc.) for actual delivery.
 */
#[AutoconfigureTag('app.notification_channel')]
final class SmsChannel implements NotificationChannelInterface
{
    public const CHANNEL_ID = 'sms';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ?SmsProviderInterface $smsProvider = null,
        private readonly bool $enabled = false,
    ) {}

    public function getChannelId(): string
    {
        return self::CHANNEL_ID;
    }

    public function supports(NotificationMessage $message): bool
    {
        // Only support if enabled, provider is available, and phone number exists
        if (!$this->enabled || $this->smsProvider === null || !$this->smsProvider->isAvailable()) {
            return false;
        }
        
        return isset($message->getData()['phone_number']);
    }

    public function getPriority(): int
    {
        return 30; // Lower priority - SMS is typically more expensive
    }

    public function deliver(NotificationMessage $message): DeliveryResult
    {
        if (!$this->enabled) {
            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: 'SMS channel is not enabled',
                errorCode: 'channel_disabled'
            );
        }

        if ($this->smsProvider === null || !$this->smsProvider->isAvailable()) {
            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: 'No SMS provider configured or available',
                errorCode: 'no_provider'
            );
        }

        $phoneNumber = $message->getData()['phone_number'] ?? null;

        if (!$phoneNumber) {
            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: 'No phone number provided',
                errorCode: 'missing_phone'
            );
        }

        if (!$this->smsProvider->validatePhoneNumber($phoneNumber)) {
            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: 'Invalid phone number format',
                errorCode: 'invalid_phone'
            );
        }

        try {
            $smsBody = $this->formatSmsBody($message);
            $result = $this->smsProvider->send($phoneNumber, $smsBody, [
                'type' => $message->getType(),
            ]);

            if ($result->isSuccess()) {
                $this->logger->info('SMS notification sent', [
                    'provider' => $this->smsProvider->getName(),
                    'message_id' => $result->getMessageId(),
                    'phone' => $this->maskPhoneNumber($phoneNumber),
                    'type' => $message->getType(),
                ]);

                return DeliveryResult::success(
                    channel: self::CHANNEL_ID,
                    metadata: array_merge(
                        ['phone' => $this->maskPhoneNumber($phoneNumber)],
                        ['provider' => $this->smsProvider->getName()],
                        ['message_id' => $result->getMessageId()],
                        $result->getMetadata()
                    )
                );
            }

            $this->logger->warning('SMS delivery failed', [
                'provider' => $this->smsProvider->getName(),
                'phone' => $this->maskPhoneNumber($phoneNumber),
                'error_code' => $result->getErrorCode(),
                'error' => $result->getErrorMessage(),
            ]);

            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: $result->getErrorMessage() ?? 'SMS delivery failed',
                errorCode: $result->getErrorCode() ?? 'delivery_failed'
            );

        } catch (\Throwable $e) {
            $this->logger->error('Failed to send SMS notification', [
                'provider' => $this->smsProvider->getName(),
                'phone' => $this->maskPhoneNumber($phoneNumber),
                'error' => $e->getMessage(),
            ]);

            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: $e->getMessage(),
                errorCode: $this->mapExceptionToErrorCode($e)
            );
        }
    }

    private function formatSmsBody(NotificationMessage $message): string
    {
        // SMS has 160 character limit for single message
        $body = $message->getTitle() . ': ' . $message->getBody();

        if (strlen($body) > 160) {
            $body = substr($body, 0, 157) . '...';
        }

        return $body;
    }

    private function maskPhoneNumber(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        return substr($phone, 0, 2) . str_repeat('*', $length - 4) . substr($phone, -2);
    }

    private function mapExceptionToErrorCode(\Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'invalid') && str_contains($message, 'number')) {
            return 'invalid_phone';
        }
        if (str_contains($message, 'rate') || str_contains($message, 'limit')) {
            return 'rate_limited';
        }
        if (str_contains($message, 'insufficient') || str_contains($message, 'balance')) {
            return 'insufficient_balance';
        }

        return 'unknown_error';
    }
}
