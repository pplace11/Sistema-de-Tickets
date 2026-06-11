<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
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
        return (new MailMessage)
            ->subject('Nova resposta no ticket '.$this->ticket->ticket_number)
            ->greeting('Olá,')
            ->line('O ticket '.$this->ticket->ticket_number.' recebeu uma nova resposta.')
            ->line('Mensagem: '.$this->reply->message);
    }
}
