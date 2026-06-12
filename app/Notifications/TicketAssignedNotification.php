<?php

namespace App\Notifications;

use App\Models\Ticket;
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
        return (new MailMessage)
            ->subject('Ticket atribuido '.$this->ticket->ticket_number)
            ->greeting('Ola,')
            ->line('Foi atribuido a si um ticket no sistema interno.')
            ->line('Numero: '.$this->ticket->ticket_number)
            ->line('Assunto: '.$this->ticket->subject)
            ->line('Estado: '.($this->ticket->status?->name ?? '-'));
    }
}
