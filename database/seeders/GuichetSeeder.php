<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guichet;
use Database\Factories\GuichetFactory;

class GuichetSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Guichets
        foreach (GuichetFactory::getGuichets() as $workflowCode => $guichets) {
            foreach ($guichets as $guichetData) {
                Guichet::updateOrCreate(
                    ['code' => $guichetData['code']],
                    [
                        'workflow_code' => $workflowCode,
                        'libelle' => $guichetData['libelle'],
                        'description' => $guichetData['description'],
                        'couleur' => $guichetData['couleur'],
                        'montant_min' => $guichetData['montant_min'],
                        'montant_max' => $guichetData['montant_max'],
                        'is_active' => $guichetData['is_active'],
                        'is_form_active' => $guichetData['is_form_active'],
                    ]
                );
            }
        }
    }
}