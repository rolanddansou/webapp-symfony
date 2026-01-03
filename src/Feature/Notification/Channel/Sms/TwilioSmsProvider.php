<?php

namespace App\Feature\Notification\Channel\Sms;

use Psr\Log\LoggerInterface;

/**
 * Twilio SMS provider implementation.
 * 
 * Requires the Twilio SDK: composer require twilio/sdk
 * 
 * Configuration via environment variables:
 * - TWILIO_ACCOUNT_SID: Your Twilio Account SID
 * - TWILIO_AUTH_TOKEN: Your Twilio Auth Token
 * - TWILIO_PHONE_NUMBER: Your Twilio phone number (sender)
 */
final class TwilioSmsProvider implements SmsProviderInterface
{
    private const NAME = 'twilio';

    public function __construct(
        private readonly ?string $accountSid,
        private readonly ?string $authToken,
        private readonly ?string $fromNumber,
        private readonly LoggerInterface $logger,
    ) {}

    public function getName(): string
    {
        return self::NAME;
    }

    public function isAvailable(): bool
    {
        return !empty($this->accountSid) 
            && !empty($this->authToken) 
            && !empty($this->fromNumber)
            && class_exists(\Twilio\Rest\Client::class);
    }

    public function send(string $to, string $message, array $options = []): SmsResult
    {
        if (!$this->isAvailable()) {
            return SmsResult::failure(
                errorCode: 'provider_not_configured',
                errorMessage: 'Twilio provider is not properly configured'
            );
        }

        if (!$this->validatePhoneNumber($to)) {
            return SmsResult::failure(
                errorCode: 'invalid_phone_number',
                errorMessage: 'Invalid phone number format. Must be E.164 format.'
            );
        }

        try {
            $client = new \Twilio\Rest\Client($this->accountSid, $this->authToken);

            $messageOptions = [
                'from' => $options['from'] ?? $this->fromNumber,
                'body' => $message,
            ];

            // Optional: Add messaging service SID if provided
            if (isset($options['messaging_service_sid'])) {
                $messageOptions['messagingServiceSid'] = $options['messaging_service_sid'];
                unset($messageOptions['from']);
            }

            // Optional: Add status callback URL
            if (isset($options['status_callback'])) {
                $messageOptions['statusCallback'] = $options['status_callback'];
            }

            $twilioMessage = $client->messages->create($to, $messageOptions);

            $this->logger->info('SMS sent via Twilio', [
                'message_sid' => $twilioMessage->sid,
                'to' => $this->maskPhoneNumber($to),
                'status' => $twilioMessage->status,
            ]);

            return SmsResult::success(
                messageId: $twilioMessage->sid,
                metadata: [
                    'status' => $twilioMessage->status,
                    'segments' => $twilioMessage->numSegments ?? 1,
                    'price' => $twilioMessage->price,
                    'price_unit' => $twilioMessage->priceUnit,
                ]
            );

        } catch (\Twilio\Exceptions\RestException $e) {
            $this->logger->error('Twilio SMS failed', [
                'to' => $this->maskPhoneNumber($to),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
            ]);

            return SmsResult::failure(
                errorCode: $this->mapTwilioErrorCode($e->getCode()),
                errorMessage: $e->getMessage(),
                metadata: ['twilio_error_code' => $e->getCode()]
            );

        } catch (\Throwable $e) {
            $this->logger->error('Unexpected error sending SMS via Twilio', [
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

    private function maskPhoneNumber(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        return substr($phone, 0, 3) . str_repeat('*', $length - 5) . substr($phone, -2);
    }

    private function mapTwilioErrorCode(int $code): string
    {
        return match ($code) {
            21211 => 'invalid_phone_number',
            21214 => 'invalid_phone_number',
            21608 => 'unverified_number',
            21610 => 'blocked_number',
            21612 => 'unreachable_number',
            21614 => 'incapable_number',
            30003 => 'unreachable_number',
            30004 => 'blocked_by_carrier',
            30005 => 'unknown_destination',
            30006 => 'landline_unreachable',
            30007 => 'carrier_violation',
            30008 => 'unknown_error',
            32203 => 'rate_limited',
            default => 'twilio_error_' . $code,
        };
    }
}
