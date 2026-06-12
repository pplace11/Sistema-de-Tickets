<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Entity;
use App\Models\Inbox;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExampleTicketSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed fictitious tickets for the Tickets page.
     */
    public function run(): void
    {
        $inboxes = Inbox::query()->get()->keyBy('name');
        $types = TicketType::query()->get()->keyBy('name');
        $statuses = TicketStatus::query()->get()->keyBy('name');
        $entities = Entity::query()->get()->keyBy('nif');
        $contacts = Contact::query()->get()->keyBy('email');
        $operator = User::query()->where('email', 'operador@tickets.local')->first();

        $tickets = [
            [
                'ticket_number' => 'FQ-001',
                'subject' => 'Pedido de ativacao de conta portal cliente',
                'inbox' => 'Comercial',
                'type' => 'Pedido',
                'status' => 'Aberto',
                'entity_nif' => '509999990',
                'creator_email' => 'contacto@fiquitizia.example',
                'message' => 'Solicitamos ativacao de acesso ao portal para dois novos utilizadores da equipa comercial.',
                'cc' => ['assistencia@fiquitizia.example'],
            ],
            [
                'ticket_number' => 'FQ-002',
                'subject' => 'Erro intermitente no envio de anexos',
                'inbox' => 'Apoio Tecnico',
                'type' => 'Incidente',
                'status' => 'Em Tratamento',
                'entity_nif' => '509999991',
                'creator_email' => 'marta.silva@nortedigital.example',
                'message' => 'Ao anexar ficheiros acima de 8MB, o upload falha em alguns pedidos. Precisamos de analise.',
                'cc' => ['it@nortedigital.example', 'gestao@nortedigital.example'],
            ],
            [
                'ticket_number' => 'FQ-003',
                'subject' => 'Duvida sobre estados de workflow',
                'inbox' => 'Apoio Tecnico',
                'type' => 'Duvida',
                'status' => 'Aberto',
                'entity_nif' => '509999992',
                'creator_email' => 'rui.ferreira@atlantech.example',
                'message' => 'Podem confirmar a diferenca entre os estados Aberto e Em Tratamento no fluxo de tickets?',
                'cc' => ['suporte@atlantech.example'],
            ],
            [
                'ticket_number' => 'FQ-004',
                'subject' => 'Atualizacao de contacto principal de RH',
                'inbox' => 'Recursos Humanos',
                'type' => 'Pedido',
                'status' => 'Fechado',
                'entity_nif' => '509999993',
                'creator_email' => 'ines.carvalho@lusitex.example',
                'message' => 'Atualizacao concluida. Favor manter este ticket para historico de alteracao cadastral.',
                'cc' => ['rh@lusitex.example'],
            ],
            [
                'ticket_number' => 'FQ-005',
                'subject' => 'Lentidao na pesquisa de tickets por entidade',
                'inbox' => 'Apoio Tecnico',
                'type' => 'Incidente',
                'status' => 'Em Tratamento',
                'entity_nif' => '509999994',
                'creator_email' => 'tiago.rocha@beiranova.example',
                'message' => 'A pesquisa por entidade demora mais de 10 segundos em horario de ponta.',
                'cc' => ['infra@beiranova.example'],
            ],
            [
                'ticket_number' => 'FQ-006',
                'subject' => 'Confirmacao de regras de notificacao por email',
                'inbox' => 'Comercial',
                'type' => 'Duvida',
                'status' => 'Aberto',
                'entity_nif' => '509999995',
                'creator_email' => 'catarina.lopes@tagusprime.example',
                'message' => 'Precisamos validar quais perfis recebem notificacao quando o ticket muda de estado.',
                'cc' => ['operacoes@tagusprime.example'],
            ],
        ];

        foreach ($tickets as $seedTicket) {
            $inbox = $inboxes->get($seedTicket['inbox']);
            $type = $types->get($seedTicket['type']);
            $status = $statuses->get($seedTicket['status']);
            $entity = $entities->get($seedTicket['entity_nif']);
            $contact = $contacts->get($seedTicket['creator_email']);

            if (! $inbox || ! $type || ! $status || ! $entity) {
                continue;
            }

            $ticket = Ticket::updateOrCreate(
                ['ticket_number' => $seedTicket['ticket_number']],
                [
                    'subject' => $seedTicket['subject'],
                    'inbox_id' => $inbox->id,
                    'ticket_type_id' => $type->id,
                    'ticket_status_id' => $status->id,
                    'assigned_operator_id' => $operator?->id,
                    'entity_id' => $entity->id,
                    'creator_contact_id' => $contact?->id,
                    'message' => $seedTicket['message'],
                ]
            );

            $ticket->cc()->delete();

            foreach (array_unique($seedTicket['cc']) as $email) {
                $ticket->cc()->create(['email' => $email]);
            }
        }
    }
}
