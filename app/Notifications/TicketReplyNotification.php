<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Support\TicketNotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Ticket $ticket,
        private readonly TicketReply $reply,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $template = config('tickets.notifications.reply');

        $replacements = [
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'reply_message' => strip_tags($this->reply->message),
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
