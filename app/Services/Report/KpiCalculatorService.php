<?php

namespace App\Services\Report;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * KpiCalculatorService — Calcul déterministe des KPIs (SANS IA).
 *
 * Toutes les métriques sont calculées via SQL direct et agrégations
 * mathématiques. Aucun appel à l'API Anthropic ici.
 */
class KpiCalculatorService
{
    // =========================================================================
    // KPIs INDIVIDUELS
    // =========================================================================

    /**
     * Calcule tous les KPIs d'un jeune bénéficiaire (promoteur).
     */
    public function individualKpis(int $promoteurId): array
    {
        $promoteur = DB::table('promoteurs as p')
            ->select('p.*')
            ->where('p.id', $promoteurId)
            ->first();

        if (!$promoteur) {
            return ['error' => 'Promoteur non trouvé'];
        }

        // Projets associés
        $projets = DB::table('micro_projets')
            ->where('promoteur_id', $promoteurId)
            ->get();

        $projetIds = $projets->pluck('id')->toArray();

        // Budget total accordé
        $budgetTotal = DB::table('budgets')
            ->whereIn('micro_projet_id', $projetIds)
            ->sum('montant_accorde');

        // Remboursements
        $remboursements = DB::table('remboursements')
            ->whereIn('plan_remboursement_id', function ($q) use ($projetIds) {
                $q->select('id')->from('plan_remboursements')->whereIn('micro_projet_id', $projetIds);
            })
            ->get();

        $montantPrevu  = $remboursements->sum('montant_echu');
        $montantPaye   = $remboursements->sum('montant_paye');
        $montantImpaye = $remboursements->sum('montant_impaye');

        $tauxRemboursement = $montantPrevu > 0
            ? round(($montantPaye / $montantPrevu) * 100, 2)
            : 0;

        // Retards de paiement
        $retards = $remboursements->filter(function ($r) {
            return !empty($r->date_paiement)
                && !empty($r->montant_echu)
                && Carbon::parse($r->date_paiement)->gt(Carbon::now());
        })->count();

        $echeancesNonPayees = $remboursements->filter(function ($r) {
            return $r->statut === 'non_paye' || (empty($r->montant_paye) && Carbon::now()->gt(Carbon::parse($r->created_at)->addMonth()));
        })->count();

        // Catégorie payeur
        $categorie = $this->categorizePayeur($tauxRemboursement, $retards);

        // Score de risque (0-100, 100 = très risqué)
        $scoreRisque = $this->calculateRiskScore($tauxRemboursement, $retards, $echeancesNonPayees);

        // Dernière exploitation connue
        $derniereExploitation = DB::table('exploitations')
            ->whereIn('micro_projet_id', $projetIds)
            ->orderByDesc('created_at')
            ->first();

        return [
            'promoteur_id'          => $promoteurId,
            'nom_complet'           => ($promoteur->prenom ?? '') . ' ' . ($promoteur->nom ?? ''),
            'matricule'             => $promoteur->matriculeaej ?? null,
            'nombre_projets'        => $projets->count(),
            'budget_total_accorde'  => (float) $budgetTotal,
            'montant_prevu'         => (float) $montantPrevu,
            'montant_paye'          => (float) $montantPaye,
            'montant_impaye'        => (float) $montantImpaye,
            'taux_remboursement'    => $tauxRemboursement,
            'nombre_retards'        => $retards,
            'echeances_non_payees'  => $echeancesNonPayees,
            'nombre_echeances'      => $remboursements->count(),
            'categorie_payeur'      => $categorie,
            'score_risque'          => $scoreRisque,
            'niveau_risque'         => $this->getRiskLevel($scoreRisque),
            'chiffre_affaires'      => $derniereExploitation?->chiffre_affaires ?? null,
            'nbre_emplois'          => $derniereExploitation?->nbre_emplois ?? null,
            'date_calcul'           => now()->toDateTimeString(),
        ];
    }

    // =========================================================================
    // KPIs GLOBAUX
    // =========================================================================

    /**
     * Calcule les KPIs globaux pour un organisme ou pour tous.
     */
    public function globalKpis(?int $organismeId, string $debut, string $fin): array
    {
        $query = DB::table('promoteurs as p')
            ->leftJoin('micro_projets as mp', 'mp.promoteur_id', '=', 'p.id');

        if ($organismeId) {
            $query->where('mp.organisme_id', $organismeId);
        }

        $totalPromoteurs = (clone $query)->distinct('p.id')->count('p.id');
        $totalProjets    = (clone $query)->count('mp.id');

        // Budget total accordé dans la période
        $budgetQuery = DB::table('budgets as b')
            ->join('micro_projets as mp', 'mp.id', '=', 'b.micro_projet_id')
            ->whereBetween('b.created_at', [$debut, $fin]);

        if ($organismeId) {
            $budgetQuery->where('mp.organisme_id', $organismeId);
        }

        $budgetTotal = $budgetQuery->sum('b.montant_accorde');

        // Remboursements dans la période
        $rembQuery = DB::table('remboursements as r')
            ->join('plan_remboursements as pr', 'pr.id', '=', 'r.plan_remboursement_id')
            ->join('micro_projets as mp', 'mp.id', '=', 'pr.micro_projet_id')
            ->whereBetween('r.created_at', [$debut, $fin]);

        if ($organismeId) {
            $rembQuery->where('mp.organisme_id', $organismeId);
        }

        $rembData      = (clone $rembQuery)->selectRaw('SUM(montant_echu) as prevu, SUM(montant_paye) as paye, SUM(montant_impaye) as impaye')->first();
        $montantPrevu  = (float) ($rembData->prevu ?? 0);
        $montantPaye   = (float) ($rembData->paye ?? 0);
        $montantImpaye = (float) ($rembData->impaye ?? 0);

        $tauxGlobal = $montantPrevu > 0 ? round(($montantPaye / $montantPrevu) * 100, 2) : 0;

        // Catégorisation des payeurs
        $categorisation = $this->payerCategorization($organismeId, $debut, $fin);

        // Répartition par secteur
        $parSecteur = DB::table('micro_projets as mp')
            ->join('secteurs as s', 's.id', '=', 'mp.secteur_id')
            ->select('s.libelle as secteur', DB::raw('COUNT(mp.id) as nombre_projets'), DB::raw('SUM(b.montant_accorde) as montant_total'))
            ->leftJoin('budgets as b', 'b.micro_projet_id', '=', 'mp.id')
            ->when($organismeId, fn($q) => $q->where('mp.organisme_id', $organismeId))
            ->groupBy('s.id', 's.libelle')
            ->orderByDesc('nombre_projets')
            ->limit(10)
            ->get()
            ->toArray();

        // Évolution mensuelle des remboursements (6 derniers mois)
        $evolutionMensuelle = DB::table('remboursements as r')
            ->join('plan_remboursements as pr', 'pr.id', '=', 'r.plan_remboursement_id')
            ->join('micro_projets as mp', 'mp.id', '=', 'pr.micro_projet_id')
            ->selectRaw('DATE_FORMAT(r.created_at, "%Y-%m") as mois, SUM(montant_echu) as prevu, SUM(montant_paye) as paye')
            ->whereBetween('r.created_at', [
                Carbon::parse($debut)->subMonths(5)->startOfMonth()->toDateString(),
                $fin
            ])
            ->when($organismeId, fn($q) => $q->where('mp.organisme_id', $organismeId))
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->toArray();

        return [
            'periode'              => ['debut' => $debut, 'fin' => $fin],
            'organisme_id'         => $organismeId,
            'total_promoteurs'     => $totalPromoteurs,
            'total_projets'        => $totalProjets,
            'budget_total_accorde' => (float) $budgetTotal,
            'montant_prevu'        => $montantPrevu,
            'montant_paye'         => $montantPaye,
            'montant_impaye'       => $montantImpaye,
            'taux_remboursement'   => $tauxGlobal,
            'categorisation'       => $categorisation,
            'par_secteur'          => $parSecteur,
            'evolution_mensuelle'  => $evolutionMensuelle,
            'date_calcul'          => now()->toDateTimeString(),
        ];
    }

    // =========================================================================
    // CATÉGORISATION DES PAYEURS
    // =========================================================================

    /**
     * Catégorise tous les promoteurs actifs en bons/en retard/défaillants.
     */
    public function payerCategorization(?int $organismeId = null, string $debut = '', string $fin = ''): array
    {
        $debut = $debut ?: Carbon::now()->subYear()->toDateString();
        $fin   = $fin   ?: Carbon::now()->toDateString();

        $query = DB::table('promoteurs as p')
            ->join('micro_projets as mp', 'mp.promoteur_id', '=', 'p.id')
            ->join('plan_remboursements as pr', 'pr.micro_projet_id', '=', 'mp.id')
            ->join('remboursements as r', 'r.plan_remboursement_id', '=', 'pr.id')
            ->selectRaw('
                p.id,
                CONCAT(p.prenom, " ", p.nom) as nom_complet,
                p.matriculeaej,
                SUM(r.montant_echu) as total_prevu,
                SUM(r.montant_paye) as total_paye,
                SUM(r.montant_impaye) as total_impaye,
                COUNT(r.id) as nb_echeances
            ')
            ->whereBetween('r.created_at', [$debut, $fin])
            ->when($organismeId, fn($q) => $q->where('mp.organisme_id', $organismeId))
            ->groupBy('p.id', 'p.prenom', 'p.nom', 'p.matriculeaej')
            ->get();

        $bonsPayeurs    = [];
        $payeursMoyens  = [];
        $enRetard       = [];
        $defaillants    = [];

        foreach ($query as $row) {
            $taux = $row->total_prevu > 0 ? ($row->total_paye / $row->total_prevu) * 100 : 0;
            $data = [
                'id'           => $row->id,
                'nom'          => $row->nom_complet,
                'matricule'    => $row->matriculeaej,
                'taux'         => round($taux, 2),
                'total_prevu'  => (float) $row->total_prevu,
                'total_paye'   => (float) $row->total_paye,
                'total_impaye' => (float) $row->total_impaye,
            ];

            if ($taux >= 95)       $bonsPayeurs[]   = $data;
            elseif ($taux >= 70)   $payeursMoyens[] = $data;
            elseif ($taux >= 30)   $enRetard[]      = $data;
            else                   $defaillants[]   = $data;
        }

        $total = count($bonsPayeurs) + count($payeursMoyens) + count($enRetard) + count($defaillants);

        return [
            'total_analyses'     => $total,
            'bons_payeurs'       => ['count' => count($bonsPayeurs),   'taux_pct' => $total > 0 ? round(count($bonsPayeurs)   / $total * 100, 1) : 0, 'liste' => $bonsPayeurs],
            'payeurs_moyens'     => ['count' => count($payeursMoyens), 'taux_pct' => $total > 0 ? round(count($payeursMoyens) / $total * 100, 1) : 0, 'liste' => $payeursMoyens],
            'en_retard'          => ['count' => count($enRetard),      'taux_pct' => $total > 0 ? round(count($enRetard)      / $total * 100, 1) : 0, 'liste' => $enRetard],
            'defaillants'        => ['count' => count($defaillants),   'taux_pct' => $total > 0 ? round(count($defaillants)   / $total * 100, 1) : 0, 'liste' => $defaillants],
        ];
    }

    // =========================================================================
    // HELPERS PRIVÉS
    // =========================================================================

    private function categorizePayeur(float $tauxRemboursement, int $retards): string
    {
        if ($tauxRemboursement >= 95 && $retards === 0) return 'Bon payeur';
        if ($tauxRemboursement >= 70) return 'Payeur régulier';
        if ($tauxRemboursement >= 30) return 'En retard';
        return 'Défaillant';
    }

    private function calculateRiskScore(float $taux, int $retards, int $echeancesNonPayees): int
    {
        // Score de 0 (aucun risque) à 100 (risque maximal)
        $score = 0;

        // Taux de remboursement manquant
        $score += max(0, (100 - $taux)) * 0.6;  // 60% du poids

        // Retards
        $score += min($retards * 5, 20);  // jusqu'à 20 pts

        // Échéances non payées
        $score += min($echeancesNonPayees * 5, 20);  // jusqu'à 20 pts

        return (int) min(100, round($score));
    }

    private function getRiskLevel(int $scoreRisque): string
    {
        if ($scoreRisque <= 20) return 'Faible';
        if ($scoreRisque <= 50) return 'Modéré';
        if ($scoreRisque <= 75) return 'Élevé';
        return 'Critique';
    }
}
