<?php

namespace Database\Seeders;

use App\Models\ContactFunction;
use App\Models\Inbox;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['Comercial', 'Apoio Tecnico', 'Recursos Humanos'] as $inbox) {
            Inbox::firstOrCreate(
                ['slug' => Str::slug($inbox)],
                ['name' => $inbox, 'is_active' => true]
            );
        }

        foreach (['Incidente', 'Pedido', 'Duvida'] as $type) {
            TicketType::firstOrCreate(['name' => $type], ['is_active' => true]);
        }

        TicketStatus::firstOrCreate(['name' => 'Aberto'], ['is_closed' => false, 'is_active' => true]);
        TicketStatus::firstOrCreate(['name' => 'Em Tratamento'], ['is_closed' => false, 'is_active' => true]);
        TicketStatus::firstOrCreate(['name' => 'Fechado'], ['is_closed' => true, 'is_active' => true]);

        foreach (['Diretor', 'Tecnico', 'Gestor RH'] as $function) {
            ContactFunction::firstOrCreate(['name' => $function]);
        }

        $operator = User::firstOrCreate([
            'email' => 'operador@tickets.local',
        ], [
            'name' => 'Operador Padrao',
            'password' => 'password',
            'role' => 'operator',
        ]);

        $operator->inboxes()->syncWithoutDetaching(Inbox::query()->pluck('id')->all());

        User::firstOrCreate([
            'email' => 'admin@inovcorp.test',
        ], [
            'name' => 'Admin InovCorp',
            'password' => 'password',
            'role' => 'operator',
            'is_active' => true,
        ])->inboxes()->syncWithoutDetaching(Inbox::query()->pluck('id')->all());

        User::firstOrCreate([
            'email' => 'cliente@tickets.local',
        ], [
            'name' => 'Cliente Padrao',
            'password' => 'password',
            'role' => 'client',
        ]);
    }
}
