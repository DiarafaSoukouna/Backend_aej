<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProjetSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        // Vérifier qu'il existe bien 10 000 promoteurs
        $promoteurIds = DB::table('promoteurs')
            ->whereBetween('id', [1, 10000])
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (count($promoteurIds) < 10000) {
            throw new \Exception(
                'Il faut au moins 10 000 promoteurs dans la table promoteurs.'
            );
        }

        // Villes réalistes de Côte d'Ivoire
        $villes = [
            'Abidjan',
            'Bouaké',
            'Yamoussoukro',
            'Daloa',
            'Korhogo',
            'San-Pédro',
            'Man',
            'Gagnoa',
            'Abengourou',
            'Divo',
            'Anyama',
            'Bingerville',
            'Grand-Bassam',
            'Agboville',
            'Adzopé',
            'Dabou',
            'Jacqueville',
            'Sassandra',
            'Soubré',
            'Guiglo',
            'Duékoué',
            'Odienné',
            'Séguéla',
            'Bondoukou',
            'Aboisso',
            'Daoukro',
            'Bouna',
            'Ferkessédougou',
            'Tengréla',
            'Boundiali',
            'Issia',
            'Vavoua',
            'Zuénoula',
            'Sinfra',
            'Tiassalé',
            'Dimbokro',
            'Bocanda',
            'Toumodi',
            'Oumé',
            'Lakota',
            'Adiaké',
            'Grand-Lahou',
            'Tabou',
            'Danané',
            'Bangolo',
            'Toulepleu',
        ];

        // Intitulés réalistes
        $intitules = [
            'Création d’une unité de transformation de manioc',
            'Création d’une ferme avicole',
            'Développement d’une exploitation de cacao',
            'Création d’une unité de transformation de noix de cajou',
            'Création d’un atelier de couture',
            'Création d’une entreprise de transformation de produits agricoles',
            'Création d’un élevage de porcs',
            'Création d’une unité de production de jus naturels',
            'Création d’une boulangerie moderne',
            'Création d’un restaurant',
            'Création d’une activité de transformation du maïs',
            'Développement d’une exploitation maraîchère',
            'Création d’une unité de production de savon',
            'Création d’un atelier de menuiserie',
            'Création d’une entreprise de transport',
            'Création d’une unité de production d’aliments pour bétail',
            'Développement d’une activité de pisciculture',
            'Création d’un centre de services numériques',
            'Création d’une unité de transformation de fruits',
            'Développement d’une activité de commerce de détail',
        ];

        $secteurs = [
            'Agriculture',
            'Élevage',
            'Pêche et aquaculture',
            'Agroalimentaire',
            'Commerce',
            'Artisanat',
            'Transport',
            'Restauration',
            'Technologies numériques',
            'Services',
            'Textile et habillement',
            'Construction',
            'Industrie',
            'Énergie',
            'Tourisme',
        ];

        $projets = [];

        for ($i = 0; $i < 10000; $i++) {

            // Un promoteur différent pour chaque projet
            $promoteurId = $promoteurIds[$i];

            $ville = $faker->randomElement($villes);
            $secteur = $faker->randomElement($secteurs);
            $intitule = $faker->randomElement($intitules);

            $projets[] = [

                'code' => 'PROJ-' . str_pad(
                    $i + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                ),

                'intitule' => $intitule,

                'matricule' => 'MAT-' . str_pad(
                    $i + 1,
                    8,
                    '0',
                    STR_PAD_LEFT
                ),

                'description' =>
                    $intitule .
                    ' situé à ' .
                    $ville .
                    ', dans le secteur ' .
                    $secteur .
                    '. Le projet vise à développer une activité économique locale et à créer des emplois.',

                'montant_total' => $faker->randomFloat(
                    2,
                    500000,
                    50000000
                ),

                // Pas encore de données dans ces tables
                'dispositif_id' => null,
                'organisme_id' => null,
                'guichet_id' => null,
                'secteur_id' => null,
                'commune_id' => null,
                'agence_id' => null,

                // Promoteur unique
                'promoteur_id' => $promoteurId,

                'stade_projet' => $faker->randomElement([
                    'CREATION',
                    'DEVELOPPEMENT',
                ]),

                'type_projet' => $faker->randomElement([
                    'INDIVIDUEL',
                    'COLLECTIF',
                ]),

                'statut' => $faker->randomElement([
                    'BROUILLON',
                    'EN_SOUMISSION',
                    'EN_COURS',
                    'EN_ANALYSE',
                    'EN_FORMATION',
                    'EN_FINANCEMENT',
                    'EN_DECAISSEMENT',
                    'EN_SUIVI',
                    'EN_REMBOURSEMENT',
                    'TERMINE',
                ]),

                'localisation' => $ville,

                'geolocalisation' =>
                    $faker->latitude(4.0, 10.7) .
                    ',' .
                    $faker->longitude(-8.6, -2.5),

                'date_certification' => $faker
                    ->optional(0.6)
                    ->dateTimeBetween('-3 years', 'now')
                    ?->format('Y-m-d'),

                'date_transmission_partenaire' => $faker
                    ->optional(0.6)
                    ->dateTimeBetween('-3 years', 'now')
                    ?->format('Y-m-d'),

                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insertion par lots de 500
            if (count($projets) === 500) {
                DB::table('micro_projets')->insert($projets);
                $projets = [];
            }
        }

        // Dernier lot
        if (!empty($projets)) {
            DB::table('micro_projets')->insert($projets);
        }
    }
}