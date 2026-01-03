<?php

namespace App\Feature\Notification\Channel\Sms;

use Psr\Log\LoggerInterface;

/**
 * Vonage (formerly Nexmo) SMS provider implementation.
 * 
 * Requires the Vonage SDK: composer require vonage/client
 * 
 * Configuration via environment variables:
 * - VONAGE_API_KEY: Your Vonage API Key
 * - VONAGE_API_SECRET: Your Vonage API Secret
 * - VONAGE_SENDER_ID: Sender ID or phone number
 */
final class VonageSmsProvider implements SmsProviderInterface
{
    private const NAME = 'vonage';

    public function __construct(
        private readonly ?string $apiKey,
        private readonly ?string $apiSecret,
        private readonly ?string $senderId,
        private readonly LoggerInterface $logger,
    ) {}

    public function getName(): string
    {
        return self::NAME;
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) 
            && !empty($this->apiSecret) 
            && !empty($this->senderId)
            && class_exists(\Vonage\Client::class);
    }

    public function send(string $to, string $message, array $options = []): SmsResult
    {
        if (!$this->isAvailable()) {
            return SmsResult::failure(
                errorCode: 'provider_not_configured',
                errorMessage: 'Vonage provider is not properly configured'
            );
        }

        if (!$this->validatePhoneNumber($to)) {
            return SmsResult::failure(
                errorCode: 'invalid_phone_number',
                errorMessage: 'Invalid phone number format. Must be E.164 format.'
            );
        }

        try {
            $credentials = new \Vonage\Client\Credentials\Basic($this->apiKey, $this->apiSecret);
            $client = new \Vonage\Client($credentials);

            // Remove + prefix for Vonage (uses E.164 without +)
            $toNumber = ltrim($to, '+');

            $smsMessage = new \Vonage\SMS\Message\SMS(
                $toNumber,
                $options['from'] ?? $this->senderId,
                $message
            );

            // Set message type if Unicode characters detected
            if ($this->containsUnicode($message)) {
                $smsMessage->setType('unicode');
            }

            $response = $client->sms()->send($smsMessage);
            $sentMessage = $response->current();

            if ($sentMessage->getStatus() == 0) {
                $this->logger->info('SMS sent via Vonage', [
                    'message_id' => $sentMessage->getMessageId(),
                    'to' => $this->maskPhoneNumber($to),
                    'status' => 'sent',
                ]);

                return SmsResult::success(
                    messageId: $sentMessage->getMessageId(),
                    metadata: [
                        'status' => 'sent',
                        'remaining_balance' => $sentMessage->getRemainingBalance(),
                        'message_price' => $sentMessage->getMessagePrice(),
                        'network' => $sentMessage->getNetwork(),
                    ]
                );
            }

            $errorText = $sentMessage->getErrorText() ?? 'Unknown error';
            
            $this->logger->error('Vonage SMS failed', [
                'to' => $this->maskPhoneNumber($to),
                'status' => $sentMessage->getStatus(),
                'error' => $errorText,
            ]);

            return SmsResult::failure(
                errorCode: $this->mapVonageErrorCode((int) $sentMessage->getStatus()),
                errorMessage: $errorText,
                metadata: ['vonage_status' => $sentMessage->getStatus()]
            );

        } catch (\Vonage\Client\Exception\Request $e) {
            $this->logger->error('Vonage request error', [
                'to' => $this->maskPhoneNumber($to),
                'error' => $e->getMessage(),
            ]);

            return SmsResult::failure(
                errorCode: 'request_error',
                errorMessage: $e->getMessage()
            );

        } catch (\Throwable $e) {
            $this->logger->error('Unexpected error sending SMS via Vonage', [
                'to' => $this->maskPhoneNumber($to),
                'error' => $e->getMessage(),
            ]);

            return SmsResult::failure(
                errorCode: 'unexpected_error',
                errorMessage: $e->getMessage()
            );
        }
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // E.164 format: +[country code][number], max 15 digits
        return preg_match('/^\+[1-9]\d{1,14}$/', $phoneNumber) === 1;
    }

    private function containsUnicode(string $text): bool
    {
        return strlen($text) !== mb_strlen($text, 'UTF-8');
    }

    private function maskPhoneNumber(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        return substr($phone, 0, 3) . str_repeat('*', $length - 5) . substr($phone, -2);
    }

    private function mapVonageErrorCode(int $status): string
    {
        return match ($status) {
            1 => 'throttled',
            2 => 'missing_params',
            3 => 'invalid_params',
            4 => 'invalid_credentials',
            5 => 'internal_error',
            6 => 'invalid_message',
            7 => 'number_barred',
            8 => 'partner_account_barred',
            9 => 'partner_quota_exceeded',
            10 => 'too_many_binds',
            11 => 'account_not_http',
            12 => 'message_too_long',
            15 => 'invalid_sender',
            22 => 'invalid_network_code',
            23 => 'invalid_callback_url',
            29 => 'non_whitelisted_destination',
            default => 'vonage_error_' . $status,
        };
    }
}
