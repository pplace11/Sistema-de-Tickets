<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Entity;
use App\Models\Inbox;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthorizationRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocks_unauthenticated_access_to_tickets_api(): void
    {
        $this->getJson('/api/tickets')->assertUnauthorized();
    }

    public function test_prevents_clients_from_viewing_tickets_from_another_entity(): void
    {
        $ctx = $this->buildTicketContext();

        $client = User::factory()->create([
            'role' => 'client',
            'entity_id' => $ctx['entityB']->id,
        ]);

        $this->actingAs($client)
            ->getJson('/api/tickets/'.$ctx['ticket']->id)
            ->assertForbidden();
    }

    public function test_prevents_operators_from_viewing_tickets_outside_their_inboxes(): void
    {
        $ctx = $this->buildTicketContext();

        $this->actingAs($ctx['operatorInboxB'])
            ->getJson('/api/tickets/'.$ctx['ticket']->id)
            ->assertForbidden();
    }

    public function test_prevents_clients_from_creating_entities_and_contacts(): void
    {
        $ctx = $this->buildTicketContext();

        $client = User::factory()->create([
            'role' => 'client',
            'entity_id' => $ctx['entityA']->id,
        ]);

        $this->actingAs($client)
            ->postJson('/api/entities', [
                'nif' => '509999999',
                'name' => 'Nova Entidade',
            ])
            ->assertForbidden();

        $this->actingAs($client)
            ->postJson('/api/contacts', [
                'name' => 'Novo Contacto',
                'email' => 'novo@example.test',
                'entity_ids' => [$ctx['entityA']->id],
            ])
            ->assertForbidden();
    }

    public function test_prevents_clients_from_assigning_operator_on_ticket_creation(): void
    {
        $ctx = $this->buildTicketContext();

        $client = User::factory()->create([
            'role' => 'client',
            'entity_id' => $ctx['entityA']->id,
        ]);

        $this->actingAs($client)
            ->postJson('/api/tickets', [
                'subject' => 'Ticket criado por cliente',
                'inbox_id' => $ctx['inboxA']->id,
                'ticket_type_id' => $ctx['ticketType']->id,
                'ticket_status_id' => $ctx['ticketStatus']->id,
                'assigned_operator_id' => $ctx['operatorInboxA']->id,
                'entity_id' => $ctx['entityA']->id,
                'creator_contact_id' => $ctx['contactA']->id,
                'message' => 'Teste de permissao',
            ])
            ->assertForbidden();
    }

    public function test_sends_explicit_notification_when_assigned_operator_changes(): void
    {
        Notification::fake();

        $ctx = $this->buildTicketContext();

        $manager = User::factory()->create([
            'role' => 'operator',
            'entity_id' => null,
        ]);
        $manager->inboxes()->sync([$ctx['inboxA']->id]);

        $newOperator = User::factory()->create([
            'role' => 'operator',
            'entity_id' => null,
            'email' => 'novo-operador@example.test',
        ]);
        $newOperator->inboxes()->sync([$ctx['inboxA']->id]);

        $this->actingAs($manager)
            ->patchJson('/api/tickets/'.$ctx['ticket']->id, [
                'assigned_operator_id' => $newOperator->id,
            ])
            ->assertOk();

        Notification::assertSentOnDemand(
            TicketAssignedNotification::class,
            function (TicketAssignedNotification $notification, array $channels, object $notifiable) use ($newOperator) {
                return in_array('mail', $channels, true)
                    && ($notifiable->routes['mail'] ?? null) === $newOperator->email;
            }
        );
    }

    private function buildTicketContext(): array
    {
        $inboxA = Inbox::create(['name' => 'Comercial', 'slug' => 'comercial', 'is_active' => true]);
        $inboxB = Inbox::create(['name' => 'Apoio Tecnico', 'slug' => 'apoio-tecnico', 'is_active' => true]);

        $ticketType = TicketType::create(['name' => 'Incidente', 'is_active' => true]);
        $ticketStatus = TicketStatus::create(['name' => 'Aberto', 'is_closed' => false, 'is_active' => true]);

        $entityA = Entity::create(['nif' => '501111111', 'name' => 'Entidade A']);
        $entityB = Entity::create(['nif' => '502222222', 'name' => 'Entidade B']);

        $contactA = Contact::create([
            'name' => 'Contacto A',
            'email' => 'contacto-a@example.test',
        ]);
        $contactA->entities()->sync([$entityA->id]);

        $operatorInboxA = User::factory()->create([
            'role' => 'operator',
            'entity_id' => null,
        ]);
        $operatorInboxA->inboxes()->sync([$inboxA->id]);

        $operatorInboxB = User::factory()->create([
            'role' => 'operator',
            'entity_id' => null,
        ]);
        $operatorInboxB->inboxes()->sync([$inboxB->id]);

        $ticket = Ticket::create([
            'ticket_number' => 'TC-001',
            'subject' => 'Falha de acesso',
            'inbox_id' => $inboxA->id,
            'ticket_type_id' => $ticketType->id,
            'ticket_status_id' => $ticketStatus->id,
            'assigned_operator_id' => null,
            'entity_id' => $entityA->id,
            'creator_contact_id' => $contactA->id,
            'creator_user_id' => null,
            'message' => 'Descricao inicial',
        ]);

        return [
            'inboxA' => $inboxA,
            'inboxB' => $inboxB,
            'ticketType' => $ticketType,
            'ticketStatus' => $ticketStatus,
            'entityA' => $entityA,
            'entityB' => $entityB,
            'contactA' => $contactA,
            'operatorInboxA' => $operatorInboxA,
            'operatorInboxB' => $operatorInboxB,
            'ticket' => $ticket,
        ];
    }
}
