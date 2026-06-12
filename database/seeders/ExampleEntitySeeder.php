<?php

namespace Database\Seeders;

use App\Models\Entity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExampleEntitySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a fictitious entity for the Entities page.
     */
    public function run(): void
    {
        $entities = [
            [
                'nif' => '509999990',
                'name' => 'Fiquitizia Solutions Lda',
                'phone' => '212345678',
                'mobile' => '919876543',
                'website' => 'https://fiquitizia.example',
                'email' => 'geral@fiquitizia.example',
                'internal_notes' => 'Entidade ficticia para testes da pagina de Entidades.',
            ],
            [
                'nif' => '509999991',
                'name' => 'NorteDigital Servicos Lda',
                'phone' => '223450001',
                'mobile' => '931110001',
                'website' => 'https://nortedigital.example',
                'email' => 'contacto@nortedigital.example',
                'internal_notes' => 'Entidade ficticia adicional para testes.',
            ],
            [
                'nif' => '509999992',
                'name' => 'Atlantech Consultoria SA',
                'phone' => '214560002',
                'mobile' => '932220002',
                'website' => 'https://atlantech.example',
                'email' => 'geral@atlantech.example',
                'internal_notes' => 'Entidade ficticia adicional para testes.',
            ],
            [
                'nif' => '509999993',
                'name' => 'Lusitex Operacoes Unipessoal',
                'phone' => '229870003',
                'mobile' => '933330003',
                'website' => 'https://lusitex.example',
                'email' => 'apoio@lusitex.example',
                'internal_notes' => 'Entidade ficticia adicional para testes.',
            ],
            [
                'nif' => '509999994',
                'name' => 'BeiraNova Industria Lda',
                'phone' => '239760004',
                'mobile' => '934440004',
                'website' => 'https://beiranova.example',
                'email' => 'comercial@beiranova.example',
                'internal_notes' => 'Entidade ficticia adicional para testes.',
            ],
            [
                'nif' => '509999995',
                'name' => 'TagusPrime Logistica SA',
                'phone' => '218650005',
                'mobile' => '935550005',
                'website' => 'https://tagusprime.example',
                'email' => 'info@tagusprime.example',
                'internal_notes' => 'Entidade ficticia adicional para testes.',
            ],
        ];

        foreach ($entities as $entity) {
            Entity::updateOrCreate(
                ['nif' => $entity['nif']],
                $entity
            );
        }
    }
}
