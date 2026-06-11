<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
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
        return (new MailMessage)
            ->subject('Novo ticket '.$this->ticket->ticket_number)
            ->greeting('Olá,')
            ->line('Foi criado um novo ticket no sistema.')
            ->line('Número: '.$this->ticket->ticket_number)
            ->line('Assunto: '.$this->ticket->subject)
            ->line('Mensagem: '.$this->ticket->message);
    }
}
