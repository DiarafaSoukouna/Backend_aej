<?php

namespace Database\Factories;

class GuichetFactory
{
    // Guichets par workflow
    const Guichets = [
        'AGR_CLASSIQUE' => [
            [
                'code' => 'GUI_AGR_CLASSIQUE',
                'libelle' => 'AGR Classique',
                'description' => 'Guichet pour les AGR classiques avec financement de 100,000 à 500,000 FCFA',
                'couleur' => '#3498db',
                'montant_min' => 100000,
                'montant_max' => 500000,
                'is_active' => true,
                'is_form_active' => true,
            ],
        ],
        'AGR_PLUS' => [
            [
                'code' => 'GUI_AGR_PLUS',
                'libelle' => 'AGR Plus',
                'description' => 'Guichet pour les AGR Plus avec financement de 1,000,001 à 3,000,000 FCFA',
                'couleur' => '#e74c3c',
                'montant_min' => 1000001,
                'montant_max' => 3000000,
                'is_active' => true,
                'is_form_active' => true,
            ],
        ],
        'MPE' => [
            [
                'code' => 'GUI_MPE',
                'libelle' => 'MPE',
                'description' => 'Guichet MPE pour financement de 3,000,001 à 20,000,000 FCFA',
                'couleur' => '#2ecc71',
                'montant_min' => 3000001,
                'montant_max' => 20000000,
                'is_active' => true,
                'is_form_active' => true,
            ],
        ],
        'MEPS' => [
            [
                'code' => 'GUI_MEPS',
                'libelle' => 'MEPS',
                'description' => 'Guichet MEPS pour financement de 20,000,001 à 100,000,000 FCFA',
                'couleur' => '#f39c12',
                'montant_min' => 20000001,
                'montant_max' => 100000000,
                'is_active' => true,
                'is_form_active' => true,
            ],
        ],
        'CAPITAL_INVEST' => [
            [
                'code' => 'GUI_CAPITAL_INVEST',
                'libelle' => 'Capital Investissement',
                'description' => 'Guichet pour le capital investissement avec financement supérieur à 100,000,000 FCFA',
                'couleur' => '#9b59b6',
                'montant_min' => 100000001,
                'montant_max' => 999999999,
                'is_active' => true,
                'is_form_active' => true,
            ],
        ],
        'MENTORAT' => [
            [
                'code' => 'GUI_MENTORAT',
                'libelle' => 'Mentorat',
                'description' => 'Guichet pour le programme de mentorat',
                'couleur' => '#1abc9c',
                'montant_min' => 0,
                'montant_max' => 0,
                'is_active' => true,
                'is_form_active' => true,
            ],
        ],
        'PERMIS' => [
            [
                'code' => 'GUI_PERMIS',
                'libelle' => 'Permis',
                'description' => 'Guichet pour le programme de permis',
                'couleur' => '#34495e',
                'montant_min' => 0,
                'montant_max' => 0,
                'is_active' => true,
                'is_form_active' => true,
            ],
        ],
        'STARTUP_BOOST' => [
            [
                'code' => 'GUI_STARTUP_BOOST',
                'libelle' => 'Startup Boost',
                'description' => 'Guichet pour les projets structurants et start-ups',
                'couleur' => '#e67e22',
                'montant_min' => 20000001,
                'montant_max' => 200000000,
                'is_active' => true,
                'is_form_active' => true,
            ],
        ],
    ];

    public static function getGuichets(): array
    {
        return self::Guichets;
    }
}
