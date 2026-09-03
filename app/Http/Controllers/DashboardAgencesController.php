<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\AgenceRegionale;
use App\Models\MicroProjet;
use App\Models\Promoteur;
use App\Models\LigneDecaissement;
use App\Models\Recouvrement;
use Illuminate\Support\Facades\DB;

class DashboardAgencesController extends Controller
{
    /**
     * Get KPIs for Agences Dashboard
     * L'agence est portée par le promoteur (promoteurs.agenceregionale_id)
     */
    public function getKPIs(Request $request): JsonResponse
    {
        // Base : micro_projets → promoteurs → agences_regionales
        $query = MicroProjet::join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id');

        $this->applyFilters($query, $request);

        $nombreProjets    = (clone $query)->count('micro_projets.id');
        $montantFinance   = (clone $query)->sum('micro_projets.montant_total');
        $nombrePromoteurs = (clone $query)->distinct()->count('micro_projets.promoteur_id');

        // Montant décaissé (lignes validées)
        $montantDecaisse = 0;
        try {
            $decaisseQuery = DB::table('micro_projets')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->join('plan_decaissements', 'micro_projets.id', '=', 'plan_decaissements.micro_projet_id')
                ->join('ligne_decaissements', 'plan_decaissements.id', '=', 'ligne_decaissements.plan_decaissement_id')
                ->where('ligne_decaissements.statut', 'VALIDE')
                ->select(DB::raw('COALESCE(SUM(ligne_decaissements.montant_ligne), 0) as total'));

            $this->applyRawFilters($decaisseQuery, $request);

            $montantDecaisse = $decaisseQuery->value('total') ?? 0;
        } catch (\Exception $e) {
            $montantDecaisse = 0;
        }

        // Emplois créés
        $emploisCrees = 0;
        try {
            $emploisQuery = DB::table('micro_projets')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->join('embauches', 'micro_projets.id', '=', 'embauches.micro_projet_id')
                ->select(DB::raw('COUNT(embauches.id) as total'));

            $this->applyRawFilters($emploisQuery, $request);

            $emploisCrees = $emploisQuery->value('total') ?? 0;
        } catch (\Exception $e) {
            $emploisCrees = 0;
        }

        $kpis = [
            'nombre_agences'    => AgenceRegionale::count(),
            'nombre_projets'    => $nombreProjets,
            'nombre_promoteurs' => $nombrePromoteurs,
            'montant_financé'   => $montantFinance,
            'montant_décaissé'  => $montantDecaisse,
            'emplois_créés'     => $emploisCrees,
        ];

        return response()->json(['data' => $kpis]);
    }

    /**
     * Get projects by agence
     * Joint via promoteurs.agenceregionale_id
     */
    public function getProjetsParAgence(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'agences_regionales.id as agence_id',
            'agences_regionales.nom as agence',
            DB::raw('COUNT(micro_projets.id) as count')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('agences_regionales', 'promoteurs.agenceregionale_id', '=', 'agences_regionales.id')
            ->groupBy('agences_regionales.id', 'agences_regionales.nom')
            ->orderBy('count', 'desc');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get projects by status
     */
    public function getProjetsParStatut(Request $request): JsonResponse
    {
        $query = MicroProjet::select('micro_projets.statut', DB::raw('COUNT(*) as count'))
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->groupBy('micro_projets.statut');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get financing by agence
     * Joint via promoteurs.agenceregionale_id
     */
    public function getFinancementParAgence(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'agences_regionales.id as agence_id',
            'agences_regionales.nom as agence',
            DB::raw('SUM(micro_projets.montant_total) as montant_total'),
            DB::raw('COUNT(micro_projets.id) as nombre_projets')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('agences_regionales', 'promoteurs.agenceregionale_id', '=', 'agences_regionales.id')
            ->groupBy('agences_regionales.id', 'agences_regionales.nom')
            ->orderBy('montant_total', 'desc');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get agence ranking
     * Joint via promoteurs.agenceregionale_id
     */
    public function getClassementAgences(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'agences_regionales.id',
            'agences_regionales.nom as agence',
            DB::raw('COUNT(micro_projets.id) as nombre_projets'),
            DB::raw('SUM(micro_projets.montant_total) as montant_total'),
            DB::raw('SUM(CASE WHEN micro_projets.statut = "EN_FINANCEMENT" THEN 1 ELSE 0 END) as projets_en_financement'),
            DB::raw('SUM(CASE WHEN micro_projets.statut = "EN_DECAISSEMENT" THEN 1 ELSE 0 END) as projets_en_decaissement'),
            DB::raw('SUM(CASE WHEN micro_projets.statut = "TERMINE" THEN 1 ELSE 0 END) as projets_termines')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('agences_regionales', 'promoteurs.agenceregionale_id', '=', 'agences_regionales.id')
            ->groupBy('agences_regionales.id', 'agences_regionales.nom')
            ->orderBy('nombre_projets', 'desc');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get alerts
     */
    public function getAlertes(Request $request): JsonResponse
    {
        $query = MicroProjet::join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id');

        $this->applyFilters($query, $request);

        $alertes = [
            'dossiers_en_attente' => (clone $query)->where('micro_projets.statut', 'BROUILLON')->count(),
        ];

        // Try to get financements_non_decaisses - table might not exist yet
        try {
            $alertes['financements_non_decaisses'] = (clone $query)
                ->where('micro_projets.statut', 'EN_FINANCEMENT')
                ->whereDoesntHave('ligneDecaissements', function ($q) {
                    $q->where('statut', 'VALIDE');
                })
                ->count();
        } catch (\Exception $e) {
            $alertes['financements_non_decaisses'] = 0;
        }

        // Try to get projets_en_retard - table might not exist yet
        try {
            $alertes['projets_en_retard'] = (clone $query)
                ->where('micro_projets.statut', 'EN_REMBOURSEMENT')
                ->whereHas('recouvrements', function ($q) {
                    $q->where('statut', 'EN_RETARD');
                })
                ->count();
        } catch (\Exception $e) {
            $alertes['projets_en_retard'] = 0;
        }

        return response()->json(['data' => $alertes]);
    }

    /**
     * Apply common filters (pour les queries Eloquent avec join promoteurs déjà fait)
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->has('annee')) {
            $query->whereYear('micro_projets.created_at', $request->annee);
        }
        if ($request->has('agence_id')) {
            $query->where('promoteurs.agenceregionale_id', $request->agence_id);
        }
        if ($request->has('secteur_id')) {
            $query->where('micro_projets.secteur_id', $request->secteur_id);
        }
        if ($request->has('statut')) {
            $query->where('micro_projets.statut', $request->statut);
        }
    }

    /**
     * Apply common filters (pour les queries DB::table brutes)
     */
    private function applyRawFilters($query, Request $request): void
    {
        if ($request->has('annee')) {
            $query->whereYear('micro_projets.created_at', $request->annee);
        }
        if ($request->has('agence_id')) {
            $query->where('promoteurs.agenceregionale_id', $request->agence_id);
        }
        if ($request->has('secteur_id')) {
            $query->where('micro_projets.secteur_id', $request->secteur_id);
        }
        if ($request->has('statut')) {
            $query->where('micro_projets.statut', $request->statut);
        }
    }
}
