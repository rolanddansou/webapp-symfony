<?php

namespace App\Feature\Notification\Channel\Sms;

/**
 * Interface for SMS providers.
 * Implement this interface to add support for different SMS services.
 */
interface SmsProviderInterface
{
    /**
     * Get the unique identifier for this provider.
     */
    public function getName(): string;

    /**
     * Check if the provider is properly configured and available.
     */
    public function isAvailable(): bool;

    /**
     * Send an SMS message.
     *
     * @param string $to The recipient phone number in E.164 format
     * @param string $message The message body (max 160 chars for single SMS)
     * @param array $options Additional options (e.g., sender ID, priority)
     * @return SmsResult The result of the send operation
     */
    public function send(string $to, string $message, array $options = []): SmsResult;

    /**
     * Validate a phone number format.
     */
    public function validatePhoneNumber(string $phoneNumber): bool;
}
