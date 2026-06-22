<?php

namespace App\Support;

use App\Models\Ticket;

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

    /**
     * @return array<string, mixed>
     */
    public static function resolveForTicket(string $notificationKey, Ticket $ticket): array
    {
        $ticket->loadMissing('inbox');

        $inboxSlug = (string) ($ticket->inbox?->slug ?? '');
        $inboxTemplate = $inboxSlug !== ''
            ? config('tickets.notifications.by_inbox.'.$inboxSlug.'.'.$notificationKey)
            : null;

        if (is_array($inboxTemplate)) {
            return $inboxTemplate;
        }

        $defaultTemplate = config('tickets.notifications.'.$notificationKey, []);

        return is_array($defaultTemplate) ? $defaultTemplate : [];
    }
}
