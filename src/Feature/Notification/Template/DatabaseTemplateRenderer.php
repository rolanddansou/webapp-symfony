<?php

namespace App\Feature\Notification\Template;

use App\Repository\Notification\NotificationTemplateRepository;

/**
 * Database-backed template renderer using NotificationTemplate entities.
 */
final class DatabaseTemplateRenderer implements TemplateRendererInterface
{
    public function __construct(
        private readonly NotificationTemplateRepository $templateRepository,
    ) {}

    public function render(string $templateCode, array $variables, string $locale = 'fr'): RenderedTemplate
    {
        $template = $this->templateRepository->findOneBy(['code' => $templateCode, 'isEnabled' => true]);

        if (!$template) {
            // Fallback to default rendering if template not found
            return $this->renderDefault($templateCode, $variables);
        }

        $title = $this->interpolate($template->getSubject() ?? '', $variables);
        $body = $this->interpolate($template->getBodyTemplate() ?? '', $variables);
        $emailHtml = $template->getEmailTemplate() 
            ? $this->interpolate($template->getEmailTemplate(), $variables) 
            : null;
        $smsText = $template->getSmsTemplate()
            ? $this->interpolate($template->getSmsTemplate(), $variables)
            : null;

        return new RenderedTemplate(
            title: $title,
            body: $body,
            emailHtml: $emailHtml,
            emailText: strip_tags($emailHtml ?? $body),
            smsText: $smsText,
            metadata: [
                'template_code' => $templateCode,
                'template_name' => $template->getName(),
            ],
        );
    }

    public function exists(string $templateCode): bool
    {
        return $this->templateRepository->findOneBy(['code' => $templateCode, 'isEnabled' => true]) !== null;
    }

    private function renderDefault(string $templateCode, array $variables): RenderedTemplate
    {
        $title = $variables['title'] ?? $templateCode;
        $body = $variables['body'] ?? $variables['message'] ?? '';

        return new RenderedTemplate(
            title: $title,
            body: $body,
            emailText: $body,
        );
    }

    /**
     * Simple variable interpolation using {{variable}} syntax.
     */
    private function interpolate(string $template, array $variables): string
    {
        $result = $template;

        foreach ($variables as $key => $value) {
            if (is_scalar($value)) {
                $result = str_replace('{{' . $key . '}}', (string) $value, $result);
                $result = str_replace('{{ ' . $key . ' }}', (string) $value, $result);
            }
        }

        // Clean up any remaining placeholders
        $result = preg_replace('/\{\{\s*\w+\s*\}\}/', '', $result);

        return $result;
    }
}
