<?php

declare(strict_types=1);

namespace App\Services\Central;

/**
 * Replace {{variable}} placeholders in notification template strings.
 */
class NotificationTemplateRenderer
{
    /**
     * @param  array<string, scalar|null>  $variables
     */
    public function render(?string $content, array $variables): ?string
    {
        if ($content === null || $content === '') {
            return $content;
        }

        $replacements = [];

        foreach ($variables as $key => $value) {
            $replacements['{{'.$key.'}}'] = (string) ($value ?? '');
        }

        return strtr($content, $replacements);
    }
}
