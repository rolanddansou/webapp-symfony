<?php

namespace App\Feature\Notification\Template;

/**
 * Value object representing a rendered notification template.
 */
final readonly class RenderedTemplate
{
    public function __construct(
        private string $title,
        private string $body,
        private ?string $emailHtml = null,
        private ?string $emailText = null,
        private ?string $smsText = null,
        private array $metadata = [],
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getEmailHtml(): ?string
    {
        return $this->emailHtml;
    }

    public function getEmailText(): ?string
    {
        return $this->emailText;
    }

    public function getSmsText(): ?string
    {
        return $this->smsText;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function hasEmailContent(): bool
    {
        return $this->emailHtml !== null || $this->emailText !== null;
    }

    public function hasSmsContent(): bool
    {
        return $this->smsText !== null;
    }
}
