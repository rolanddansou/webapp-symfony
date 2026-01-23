<?php

namespace App\Feature\Notification\Channel;

use App\Feature\Notification\Message\NotificationMessage;
use App\Feature\Notification\Result\DeliveryResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Email notification channel using Symfony Mailer.
 */
#[AutoconfigureTag('app.notification_channel')]
final class EmailChannel implements NotificationChannelInterface
{
    public const CHANNEL_ID = 'email';

    public function __construct(
        private readonly LoggerInterface $logger,
        #[Autowire('%app_name%')]
        private string $appName,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private readonly string $notificationsDsn,
        private readonly string $senderEmail,
        private readonly string $senderName,
    ) {
    }

    public function getChannelId(): string
    {
        return self::CHANNEL_ID;
    }

    public function supports(NotificationMessage $message): bool
    {
        // Email channel supports all notifications that have a recipient email
        return !empty($message->getRecipientEmail());
    }

    public function getPriority(): int
    {
        return 20; // Medium priority
    }

    public function deliver(NotificationMessage $message): DeliveryResult
    {
        try {
            $email = (new Email())
                ->from(sprintf('%s <%s>', $this->senderName, $this->senderEmail))
                ->to($message->getRecipientEmail())
                ->subject($message->getTitle())
                ->text($message->getBody());

            // Add HTML content if available in data
            if (isset($message->getData()['email_html'])) {
                $email->html($message->getData()['email_html']);
            } else {
                // Generate simple HTML from body
                $email->html($this->generateHtmlBody($message));
            }

            $email->sender(new Address($this->senderEmail, $this->senderName));

            // Attach PDF invoice if present
            if (isset($message->getData()['invoice_pdf_path']) && !empty($message->getData()['invoice_pdf_path'])) {
                $relativePdfPath = $message->getData()['invoice_pdf_path'];
                $absolutePdfPath = $this->projectDir . DIRECTORY_SEPARATOR . $relativePdfPath;

                if (file_exists($absolutePdfPath)) {
                    $email->attachFromPath($absolutePdfPath);
                } else {
                    $this->logger->warning('Attachment PDF file not found', ['path' => $absolutePdfPath]);
                }
            }

            $transport = Transport::fromDsn($this->notificationsDsn);
            $mailer = new Mailer($transport);
            $mailer->send($email);

            $this->logger->info('Email notification sent', [
                'recipient' => $message->getRecipientEmail(),
                'type' => $message->getType(),
            ]);

            return DeliveryResult::success(
                channel: self::CHANNEL_ID,
                metadata: ['recipient' => $message->getRecipientEmail()]
            );

        } catch (\Throwable $e) {
            $this->logger->error('Failed to send email notification', [
                'recipient' => $message->getRecipientEmail(),
                'error' => $e->getMessage(),
            ]);

            $errorCode = $this->mapExceptionToErrorCode($e);

            return DeliveryResult::failure(
                channel: self::CHANNEL_ID,
                errorMessage: $e->getMessage(),
                errorCode: $errorCode,
                metadata: ['recipient' => $message->getRecipientEmail()]
            );
        }
    }

    private function generateHtmlBody(NotificationMessage $message): string
    {
        $body = nl2br(htmlspecialchars($message->getBody()));
        $actionButton = '';
        $appName = htmlspecialchars($this->appName);

        if ($message->getActionUrl()) {
            $label = htmlspecialchars($message->getActionLabel() ?? 'Voir plus');
            $url = htmlspecialchars($message->getActionUrl());
            $actionButton = <<<HTML
                <p style="margin-top: 20px;">
                    <a href="{$url}" style="background-color: #F99F00; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
                        {$label}
                    </a>
                </p>
            HTML;
        }

        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>{$message->getTitle()}</title>
            </head>
            <body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); padding: 20px; border-radius: 8px 8px 0 0;">
                    <h1 style="color: white; margin: 0; font-size: 24px;">{$appName}</h1>
                </div>
                <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 8px 8px;">
                    <h2 style="color: #333; margin-top: 0;">{$message->getTitle()}</h2>
                    <p>{$body}</p>
                    {$actionButton}
                </div>
                <div style="text-align: center; padding: 20px; color: #888; font-size: 12px;">
                    <p>© {$appName}</p>
                </div>
            </body>
            </html>
        HTML;
    }

    private function mapExceptionToErrorCode(\Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'rate') || str_contains($message, 'limit')) {
            return 'rate_limited';
        }
        if (str_contains($message, 'timeout')) {
            return 'timeout';
        }
        if (str_contains($message, 'unavailable') || str_contains($message, '503')) {
            return 'service_unavailable';
        }
        if (str_contains($message, 'invalid') && str_contains($message, 'address')) {
            return 'invalid_recipient';
        }

        return 'unknown_error';
    }
}
