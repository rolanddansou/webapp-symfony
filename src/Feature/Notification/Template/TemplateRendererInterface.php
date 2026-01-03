<?php

namespace App\Feature\Notification\Template;

/**
 * Interface for notification template rendering.
 * Allows different template engines to be used.
 */
interface TemplateRendererInterface
{
    /**
     * Render a template with the given variables.
     */
    public function render(string $templateCode, array $variables, string $locale = 'fr'): RenderedTemplate;

    /**
     * Check if a template exists.
     */
    public function exists(string $templateCode): bool;
}
