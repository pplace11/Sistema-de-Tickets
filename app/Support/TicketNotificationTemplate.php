<?php

namespace App\Support;

class TicketNotificationTemplate
{
    /**
     * @param array<string, string> $replacements
     */
    public static function render(string $template, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $template = str_replace('{{'.$key.'}}', (string) $value, $template);
        }

        return $template;
    }
}
