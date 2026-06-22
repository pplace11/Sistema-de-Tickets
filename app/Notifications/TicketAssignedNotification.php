<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Support\TicketNotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $template = TicketNotificationTemplate::resolveForTicket('assigned', $this->ticket);

        $replacements = [
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'status_name' => $this->ticket->status?->name ?? '-',
        ];

        $mail = (new MailMessage)
            ->subject(TicketNotificationTemplate::render((string) ($template['subject'] ?? ''), $replacements))
            ->greeting(TicketNotificationTemplate::render((string) ($template['greeting'] ?? 'Ola,'), $replacements));

        foreach (($template['lines'] ?? []) as $line) {
            $mail->line(TicketNotificationTemplate::render((string) $line, $replacements));
        }

        return $mail;
    }
}
