<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactFunction;
use App\Models\Entity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExampleContactSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a fictitious contact for the Contacts page.
     */
    public function run(): void
    {
        $contacts = [
            [
                'name' => 'Andre Fiquitizia',
                'email' => 'contacto@fiquitizia.example',
                'phone' => '214440111',
                'mobile' => '936660111',
                'internal_notes' => 'Contacto ficticio para testes da pagina de Contactos.',
                'function' => 'Tecnico',
                'entity_nifs' => ['509999990', '509999991'],
            ],
            [
                'name' => 'Marta Silva',
                'email' => 'marta.silva@nortedigital.example',
                'phone' => '223450101',
                'mobile' => '931110101',
                'internal_notes' => 'Contacto ficticio adicional para testes.',
                'function' => 'Diretor',
                'entity_nifs' => ['509999991'],
            ],
            [
                'name' => 'Rui Ferreira',
                'email' => 'rui.ferreira@atlantech.example',
                'phone' => '214560202',
                'mobile' => '932220202',
                'internal_notes' => 'Contacto ficticio adicional para testes.',
                'function' => 'Tecnico',
                'entity_nifs' => ['509999992'],
            ],
            [
                'name' => 'Ines Carvalho',
                'email' => 'ines.carvalho@lusitex.example',
                'phone' => '229870303',
                'mobile' => '933330303',
                'internal_notes' => 'Contacto ficticio adicional para testes.',
                'function' => 'Gestor RH',
                'entity_nifs' => ['509999993'],
            ],
            [
                'name' => 'Tiago Rocha',
                'email' => 'tiago.rocha@beiranova.example',
                'phone' => '239760404',
                'mobile' => '934440404',
                'internal_notes' => 'Contacto ficticio adicional para testes.',
                'function' => 'Diretor',
                'entity_nifs' => ['509999994'],
            ],
            [
                'name' => 'Catarina Lopes',
                'email' => 'catarina.lopes@tagusprime.example',
                'phone' => '218650505',
                'mobile' => '935550505',
                'internal_notes' => 'Contacto ficticio adicional para testes.',
                'function' => 'Tecnico',
                'entity_nifs' => ['509999995'],
            ],
        ];

        foreach ($contacts as $seedContact) {
            $function = ContactFunction::firstOrCreate(['name' => $seedContact['function']]);

            $contact = Contact::updateOrCreate(
                ['email' => $seedContact['email']],
                [
                    'name' => $seedContact['name'],
                    'contact_function_id' => $function->id,
                    'phone' => $seedContact['phone'],
                    'mobile' => $seedContact['mobile'],
                    'internal_notes' => $seedContact['internal_notes'],
                ]
            );

            $entityIds = Entity::query()
                ->where(function ($query) use ($seedContact) {
                    foreach ($seedContact['entity_nifs'] as $index => $nif) {
                        if ($index === 0) {
                            $query->where('nif', $nif);
                        } else {
                            $query->orWhere('nif', $nif);
                        }
                    }
                })
                ->pluck('id')
                ->all();

            $contact->entities()->sync($entityIds);
        }
    }
}
