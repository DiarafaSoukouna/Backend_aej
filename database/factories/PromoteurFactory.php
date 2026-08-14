<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Promoteur;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promoteur>
 */
class PromoteurFactory extends Factory
{
    private array $noms = [
        // Akan / Baoulé / Agni
        'Kouassi', 'Kouadio', 'Koffi', 'Konan', 'Kouame', 'Yao', 'Yapi', 'Aka',
        'Assamoi', 'Adou', 'Amani', 'Kacou', 'N\'Dri', 'N\'Guessan', 'Djedje',
        'Angoua', 'Attia', 'Amoikon', 'Boa', 'Dje',
        // Malinké / Dioula / Sénoufo (Nord)
        'Bamba', 'Ouattara', 'Traore', 'Coulibaly', 'Diabate', 'Sidibe',
        'Doumbia', 'Kone', 'Diallo', 'Berte', 'Soro', 'Sekongo', 'Silue',
        'Diomande', 'Fofana', 'Toure', 'Cisse', 'Sanogo', 'Diarrassouba',
        'Cherif', 'Ba', 'Bakayoko', 'Camara', 'Keita', 'Sangare',
        // Bété / Gouro / Wê (Centre-Ouest)
        'Gbagbo', 'Guei', 'Zadi', 'Zamble', 'Gnamien', 'Zamblet',
        'Depry', 'Sery', 'Doh', 'Kra', 'Gogoua',
        // Autres
        'Djaha', 'Krou', 'Loba', 'Djire', 'Gadji', 'Angui', 'Digbeu',
    ];

    private array $prenomsHommes = [
        'Kouassi', 'Kouadio', 'Koffi', 'Konan', 'Kra', 'Kouame', 'Yao', 'Yapi',
        'Adama', 'Ibrahim', 'Mamadou', 'Boubacar', 'Moussa', 'Abdoulaye',
        'Souleymane', 'Issouf', 'Lassina', 'Karim', 'Yacouba', 'Seydou',
        'Drissa', 'Issa',
        'Jean-Baptiste', 'Serge', 'Franck', 'Patrick', 'Emmanuel', 'Arsene',
        'Didier', 'Herve', 'Cyrille', 'Landry', 'Armand', 'Christian',
        'Olivier', 'Fabrice', 'Guillaume', 'Stephane', 'Thierry', 'Marc',
        'Eric', 'Bertin', 'Parfait', 'Romuald', 'Wilfried', 'Aristide', 'Alain',
    ];

    private array $prenomsFemmes = [
        'Akissi', 'Amenan', 'Affoue', 'Ahou', 'Adjoua', 'Aya', 'Akoua', 'Amoin',
        'Adjara', 'Awa', 'Aminata', 'Fatoumata', 'Mariam', 'Mariame', 'Kadiatou',
        'Massandje', 'Djeneba', 'Sitan', 'Fanta', 'Assetou', 'Rokia', 'Salimata',
        'Ramata', 'Nafissatou', 'Habibatou', 'Aicha', 'Fatou',
        'Marie', 'Marie-Claire', 'Marie-Ange', 'Christine', 'Josephine',
        'Delphine', 'Solange', 'Odette', 'Henriette', 'Colette', 'Vivianne',
        'Brigitte', 'Chantal', 'Sylvie', 'Nadege', 'Ariane', 'Priscille',
        'Grace', 'Prisca', 'Esperance', 'Clarisse', 'Sandrine', 'Carine',
        'Melanie', 'Christelle', 'Berenice', 'Aurelie', 'Larissa', 'Judith',
        'Edwige', 'Fabienne', 'Roseline', 'Ornella', 'Tatiana', 'Charlene',
        'Vanessa', 'Patricia', 'Estelle', 'Jennifer',
    ];

    private array $villes = [
        'Abidjan', 'Bouake', 'Daloa', 'Yamoussoukro', 'San-Pedro', 'Korhogo',
        'Man', 'Divo', 'Gagnoa', 'Abengourou', 'Anyama', 'Agboville',
        'Grand-Bassam', 'Dabou', 'Bondoukou', 'Seguela', 'Odienne', 'Soubre',
        'Adzope', 'Bingerville', 'Sinfra', 'Toumodi', 'Issia', 'Katiola',
        'Ferkessedougou', 'Aboisso', 'Duekoue', 'Guiglo', 'Danane', 'Bouafle',
    ];

    public function definition(): array
    {
        // 1 = Homme, 2 = Femme
        $sexe = fake()->randomElement([1, 2]);

        $prenom = $sexe == 1
            ? fake()->randomElement($this->prenomsHommes)
            : fake()->randomElement($this->prenomsFemmes);

        $nom = fake()->randomElement($this->noms);

        $dateNaissance = fake()->dateTimeBetween('-65 years', '-18 years');
        $age = Carbon::parse($dateNaissance)->age;
        $trancheAge = $age <= 40 ? '18_40' : 'PLUS_40';

        // Prénoms/noms des parents tirés des mêmes listes ivoiriennes
        // (nom de famille du père/de la mère cohérent avec la logique locale)
        $nomDuPere = fake()->randomElement($this->prenomsHommes) . ' ' . $nom;
        $nomDeLaMere = fake()->randomElement($this->prenomsFemmes) . ' ' . fake()->randomElement($this->noms);

        $email = Str::slug($prenom . '.' . $nom)
            . fake()->unique()->numberBetween(100000, 999999999)
            . '@gmail.com';

        return [
            'profile' => null,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => fake()->unique()->numerify('07########'),
            'tranche_age' => $trancheAge,
            'datenaissance' => $dateNaissance,
            'lieunaissance' => fake()->randomElement($this->villes),
            'matriculeaej' => 'AEJ' . date('Y') . fake()->unique()->numberBetween(100000000, 999999999),
            'numerocni' => 'CI' . fake()->unique()->numberBetween(1000000000, 9999999999),
            'numerocmu' => 'CMU' . fake()->unique()->numberBetween(1000000000, 9999999999),
            'numerocnps' => 'CNPS' . fake()->unique()->numberBetween(1000000000, 9999999999),
            'raison_sociale' => fake()->optional(0.3)->company(),
            'handicap' => null,
            'nomdupere' => $nomDuPere,
            'nomdelamere' => $nomDeLaMere,

            // Relations fictives (plages plausibles en l'absence des vraies tables de référence)
            'sexe_id' => $sexe,
            'personnel_id' => rand(1, 50),
            'lieuhabitation_id' => rand(1, 20),
            'agenceregionale_id' => rand(1, 14),
            'secteuractivite_id' => rand(1, 20),
            'soussecteuractivite_id' => rand(1, 50),
            'situationmatrimoniale_id' => rand(1, 5),
            'typesituationhandicap_id' => rand(1, 5),
            'typepieceidentite_id' => rand(1, 3),
            'niveauetude_id' => rand(1, 7),
            'paysnationalite_id' => 1,
            'statut' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}