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
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketReplyNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_create_ticket_with_rich_text_and_attachments(): void
    {
        Storage::fake('public');

        $ctx = $this->buildContext();

        $response = $this->actingAs($ctx['operatorA'])
            ->post('/api/tickets', [
                'subject' => 'Pedido com editor',
                'inbox_id' => $ctx['inboxA']->id,
                'ticket_type_id' => $ctx['typeIncident']->id,
                'ticket_status_id' => $ctx['statusOpen']->id,
                'entity_id' => $ctx['entityA']->id,
                'creator_contact_id' => $ctx['contactA']->id,
                'message' => '<p><strong>Mensagem</strong> com <a href="https://example.test">link</a></p>',
                'cc' => ['cliente@example.test'],
                'attachments' => [
                    UploadedFile::fake()->create('manual.pdf', 120, 'application/pdf'),
                ],
            ]);

        $response->assertCreated();

        $ticketId = (int) $response->json('id');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticketId,
            'subject' => 'Pedido com editor',
            'message' => '<p><strong>Mensagem</strong> com <a href="https://example.test">link</a></p>',
        ]);

        $attachment = DB::table('ticket_attachments')
            ->where('ticket_id', $ticketId)
            ->value('path');

        $this->assertNotNull($attachment);
        Storage::disk('public')->assertExists($attachment);
        $this->assertDatabaseHas('ticket_attachments', ['ticket_id' => $ticketId]);
    }

    public function test_operator_filters_tickets_by_type_operator_and_entity(): void
    {
        $ctx = $this->buildContext();

        $target = Ticket::create([
            'ticket_number' => 'TC-010',
            'subject' => 'Target ticket',
            'inbox_id' => $ctx['inboxA']->id,
            'ticket_type_id' => $ctx['typeIncident']->id,
            'ticket_status_id' => $ctx['statusOpen']->id,
            'assigned_operator_id' => $ctx['operatorA']->id,
            'entity_id' => $ctx['entityA']->id,
            'creator_contact_id' => $ctx['contactA']->id,
            'creator_user_id' => $ctx['operatorA']->id,
            'message' => 'Ticket alvo',
        ]);

        Ticket::create([
            'ticket_number' => 'TC-011',
            'subject' => 'Other type',
            'inbox_id' => $ctx['inboxA']->id,
            'ticket_type_id' => $ctx['typeRequest']->id,
            'ticket_status_id' => $ctx['statusOpen']->id,
            'assigned_operator_id' => $ctx['operatorA']->id,
            'entity_id' => $ctx['entityA']->id,
            'creator_contact_id' => $ctx['contactA']->id,
            'creator_user_id' => $ctx['operatorA']->id,
            'message' => 'Outro tipo',
        ]);

        Ticket::create([
            'ticket_number' => 'TC-012',
            'subject' => 'Other operator',
            'inbox_id' => $ctx['inboxA']->id,
            'ticket_type_id' => $ctx['typeIncident']->id,
            'ticket_status_id' => $ctx['statusOpen']->id,
            'assigned_operator_id' => $ctx['operatorB']->id,
            'entity_id' => $ctx['entityA']->id,
            'creator_contact_id' => $ctx['contactA']->id,
            'creator_user_id' => $ctx['operatorA']->id,
            'message' => 'Outro operador',
        ]);

        Ticket::create([
            'ticket_number' => 'TC-013',
            'subject' => 'Other entity',
            'inbox_id' => $ctx['inboxA']->id,
            'ticket_type_id' => $ctx['typeIncident']->id,
            'ticket_status_id' => $ctx['statusOpen']->id,
            'assigned_operator_id' => $ctx['operatorA']->id,
            'entity_id' => $ctx['entityB']->id,
            'creator_contact_id' => null,
            'creator_user_id' => $ctx['operatorA']->id,
            'message' => 'Outra entidade',
        ]);

        $response = $this->actingAs($ctx['operatorA'])
            ->getJson('/api/tickets?ticket_type_id='.$ctx['typeIncident']->id.'&assigned_operator_id='.$ctx['operatorA']->id.'&entity_id='.$ctx['entityA']->id);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $target->id);
    }

    public function test_client_lists_only_own_entity_tickets_even_when_filtering_other_entity(): void
    {
        $ctx = $this->buildContext();

        $client = User::factory()->create([
            'role' => 'client',
            'entity_id' => $ctx['entityA']->id,
        ]);

        Ticket::create([
            'ticket_number' => 'TC-020',
            'subject' => 'Client entity',
            'inbox_id' => $ctx['inboxA']->id,
            'ticket_type_id' => $ctx['typeIncident']->id,
            'ticket_status_id' => $ctx['statusOpen']->id,
            'assigned_operator_id' => null,
            'entity_id' => $ctx['entityA']->id,
            'creator_contact_id' => $ctx['contactA']->id,
            'creator_user_id' => null,
            'message' => 'Permitido',
        ]);

        Ticket::create([
            'ticket_number' => 'TC-021',
            'subject' => 'Other entity',
            'inbox_id' => $ctx['inboxA']->id,
            'ticket_type_id' => $ctx['typeIncident']->id,
            'ticket_status_id' => $ctx['statusOpen']->id,
            'assigned_operator_id' => null,
            'entity_id' => $ctx['entityB']->id,
            'creator_contact_id' => null,
            'creator_user_id' => null,
            'message' => 'Nao permitido',
        ]);

        $own = $this->actingAs($client)->getJson('/api/tickets');
        $own->assertOk();
        $own->assertJsonCount(1, 'data');

        $forced = $this->actingAs($client)->getJson('/api/tickets?entity_id='.$ctx['entityB']->id);
        $forced->assertOk();
        $forced->assertJsonCount(0, 'data');
    }

    public function test_lookups_returns_allowed_operators_for_operator_and_empty_for_client(): void
    {
        $ctx = $this->buildContext();

        $operatorOtherInbox = User::factory()->create([
            'role' => 'operator',
            'entity_id' => null,
            'is_active' => true,
            'name' => 'Operador Externo',
        ]);
        $operatorOtherInbox->inboxes()->sync([$ctx['inboxB']->id]);

        $operatorResponse = $this->actingAs($ctx['operatorA'])->getJson('/api/lookups');
        $operatorResponse->assertOk();

        $operatorIds = collect($operatorResponse->json('operators'))->pluck('id')->all();
        $this->assertContains($ctx['operatorA']->id, $operatorIds);
        $this->assertContains($ctx['operatorB']->id, $operatorIds);
        $this->assertNotContains($operatorOtherInbox->id, $operatorIds);

        $client = User::factory()->create([
            'role' => 'client',
            'entity_id' => $ctx['entityA']->id,
        ]);

        $clientResponse = $this->actingAs($client)->getJson('/api/lookups');
        $clientResponse->assertOk();
        $clientResponse->assertJsonCount(0, 'operators');
    }

    public function test_ticket_created_notification_uses_configurable_template(): void
    {
        Notification::fake();

        config()->set('tickets.notifications.created', [
            'subject' => 'Teste {{ticket_number}}',
            'greeting' => 'Saudacao {{subject}}',
            'lines' => [
                'Linha custom {{ticket_number}}',
                'Texto {{message}}',
            ],
        ]);

        $ctx = $this->buildContext();

        $this->actingAs($ctx['operatorA'])
            ->postJson('/api/tickets', [
                'subject' => 'Template',
                'inbox_id' => $ctx['inboxA']->id,
                'ticket_type_id' => $ctx['typeIncident']->id,
                'ticket_status_id' => $ctx['statusOpen']->id,
                'entity_id' => $ctx['entityA']->id,
                'creator_contact_id' => $ctx['contactA']->id,
                'message' => '<p>Mensagem HTML</p>',
                'cc' => ['destino@example.test'],
            ])
            ->assertCreated();

        Notification::assertSentOnDemand(
            TicketCreatedNotification::class,
            function (TicketCreatedNotification $notification): bool {
                $mail = $notification->toMail((object) []);

                return $mail->subject === 'Teste TC-001'
                    && $mail->greeting === 'Saudacao Template'
                    && in_array('Linha custom TC-001', $mail->introLines, true)
                    && in_array('Texto Mensagem HTML', $mail->introLines, true);
            }
        );
    }

    public function test_ticket_reply_notification_uses_configurable_template(): void
    {
        Notification::fake();

        config()->set('tickets.notifications.reply', [
            'subject' => 'Resposta {{ticket_number}}',
            'greeting' => 'Ola {{subject}}',
            'lines' => [
                'Linha {{ticket_number}}',
                'Texto {{reply_message}}',
            ],
        ]);

        $ctx = $this->buildContext();

        $ticket = Ticket::create([
            'ticket_number' => 'TC-001',
            'subject' => 'Ticket de resposta',
            'inbox_id' => $ctx['inboxA']->id,
            'ticket_type_id' => $ctx['typeIncident']->id,
            'ticket_status_id' => $ctx['statusOpen']->id,
            'assigned_operator_id' => $ctx['operatorA']->id,
            'entity_id' => $ctx['entityA']->id,
            'creator_contact_id' => $ctx['contactA']->id,
            'creator_user_id' => $ctx['operatorA']->id,
            'message' => 'Mensagem inicial',
        ]);

        $this->actingAs($ctx['operatorA'])
            ->postJson('/api/tickets/'.$ticket->id.'/replies', [
                'message' => '<p>Nova <strong>resposta</strong></p>',
            ])
            ->assertCreated();

        Notification::assertSentOnDemand(
            TicketReplyNotification::class,
            function (TicketReplyNotification $notification): bool {
                $mail = $notification->toMail((object) []);

                return $mail->subject === 'Resposta TC-001'
                    && $mail->greeting === 'Ola Ticket de resposta'
                    && in_array('Linha TC-001', $mail->introLines, true)
                    && in_array('Texto Nova resposta', $mail->introLines, true);
            }
        );
    }

    public function test_ticket_assigned_notification_uses_configurable_template(): void
    {
        Notification::fake();

        config()->set('tickets.notifications.assigned', [
            'subject' => 'Atribuicao {{ticket_number}}',
            'greeting' => 'Boas {{subject}}',
            'lines' => [
                'Linha {{ticket_number}}',
                'Estado atual {{status_name}}',
            ],
        ]);

        $ctx = $this->buildContext();

        $ticket = Ticket::create([
            'ticket_number' => 'TC-001',
            'subject' => 'Ticket para atribuir',
            'inbox_id' => $ctx['inboxA']->id,
            'ticket_type_id' => $ctx['typeIncident']->id,
            'ticket_status_id' => $ctx['statusOpen']->id,
            'assigned_operator_id' => $ctx['operatorA']->id,
            'entity_id' => $ctx['entityA']->id,
            'creator_contact_id' => $ctx['contactA']->id,
            'creator_user_id' => $ctx['operatorA']->id,
            'message' => 'Mensagem inicial',
        ]);

        $this->actingAs($ctx['operatorA'])
            ->patchJson('/api/tickets/'.$ticket->id, [
                'assigned_operator_id' => $ctx['operatorB']->id,
            ])
            ->assertOk();

        Notification::assertSentOnDemand(
            TicketAssignedNotification::class,
            function (TicketAssignedNotification $notification) use ($ctx): bool {
                $mail = $notification->toMail((object) []);

                return $mail->subject === 'Atribuicao TC-001'
                    && $mail->greeting === 'Boas Ticket para atribuir'
                    && in_array('Linha TC-001', $mail->introLines, true)
                    && in_array('Estado atual '.$ctx['statusOpen']->name, $mail->introLines, true);
            }
        );
    }

    public function test_ticket_number_sequence_remains_unique_across_multiple_creations(): void
    {
        Notification::fake();

        $ctx = $this->buildContext();

        $ticketNumbers = [];

        for ($i = 1; $i <= 5; $i++) {
            $response = $this->actingAs($ctx['operatorA'])
                ->postJson('/api/tickets', [
                    'subject' => 'Carga '.$i,
                    'inbox_id' => $ctx['inboxA']->id,
                    'ticket_type_id' => $ctx['typeIncident']->id,
                    'ticket_status_id' => $ctx['statusOpen']->id,
                    'entity_id' => $ctx['entityA']->id,
                    'creator_contact_id' => $ctx['contactA']->id,
                    'message' => 'Geracao sequencial',
                ]);

            $response->assertCreated();
            $ticketNumbers[] = (string) $response->json('ticket_number');
        }

        $this->assertCount(5, array_unique($ticketNumbers));
        $this->assertSame(
            ['TC-001', 'TC-002', 'TC-003', 'TC-004', 'TC-005'],
            $ticketNumbers
        );
    }

    private function buildContext(): array
    {
        $inboxA = Inbox::create(['name' => 'Comercial', 'slug' => 'comercial', 'is_active' => true]);
        $inboxB = Inbox::create(['name' => 'Apoio Tecnico', 'slug' => 'apoio-tecnico', 'is_active' => true]);

        $typeIncident = TicketType::create(['name' => 'Incidente', 'is_active' => true]);
        $typeRequest = TicketType::create(['name' => 'Pedido', 'is_active' => true]);
        $statusOpen = TicketStatus::create(['name' => 'Aberto', 'is_closed' => false, 'is_active' => true]);

        $entityA = Entity::create(['nif' => '501111111', 'name' => 'Entidade A']);
        $entityB = Entity::create(['nif' => '502222222', 'name' => 'Entidade B']);

        $contactA = Contact::create([
            'name' => 'Contacto A',
            'email' => 'contacto-a@example.test',
        ]);
        $contactA->entities()->sync([$entityA->id]);

        $operatorA = User::factory()->create([
            'role' => 'operator',
            'entity_id' => null,
            'is_active' => true,
            'name' => 'Operador A',
        ]);
        $operatorA->inboxes()->sync([$inboxA->id]);

        $operatorB = User::factory()->create([
            'role' => 'operator',
            'entity_id' => null,
            'is_active' => true,
            'name' => 'Operador B',
        ]);
        $operatorB->inboxes()->sync([$inboxA->id]);

        return [
            'inboxA' => $inboxA,
            'inboxB' => $inboxB,
            'typeIncident' => $typeIncident,
            'typeRequest' => $typeRequest,
            'statusOpen' => $statusOpen,
            'entityA' => $entityA,
            'entityB' => $entityB,
            'contactA' => $contactA,
            'operatorA' => $operatorA,
            'operatorB' => $operatorB,
        ];
    }
}
