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
     */
    public function getKPIs(Request $request): JsonResponse
    {
        $query = MicroProjet::query();
        
        // Apply filters
        if ($request->has('annee')) {
            $query->whereYear('created_at', $request->annee);
        }
        if ($request->has('region_id')) {
            $query->whereHas('agence', function ($q) use ($request) {
                $q->where('region_id', $request->region_id);
            });
        }
        if ($request->has('agence_id')) {
            $query->where('agence_id', $request->agence_id);
        }
        if ($request->has('secteur_id')) {
            $query->where('secteur_id', $request->secteur_id);
        }
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        // KPIs - use joins to avoid too many placeholders
        $montantDecaisse = 0;
        try {
            $montantDecaisseQuery = LigneDecaissement::select(DB::raw('SUM(ligne_decaissements.montant_ligne) as total'))
                ->join('plan_decaissements', 'ligne_decaissements.plan_decaissement_id', '=', 'plan_decaissements.id')
                ->join('micro_projets', 'plan_decaissements.micro_projet_id', '=', 'micro_projets.id')
                ->where('ligne_decaissements.statut', 'VALIDE');

            // Apply same filters to decaissement query
            if ($request->has('annee')) {
                $montantDecaisseQuery->whereYear('micro_projets.created_at', $request->annee);
            }
            if ($request->has('region_id')) {
                $montantDecaisseQuery->whereHas('microProjets.agence', function ($q) use ($request) {
                    $q->where('region_id', $request->region_id);
                });
            }
            if ($request->has('agence_id')) {
                $montantDecaisseQuery->where('micro_projets.agence_id', $request->agence_id);
            }
            if ($request->has('secteur_id')) {
                $montantDecaisseQuery->where('micro_projets.secteur_id', $request->secteur_id);
            }
            if ($request->has('statut')) {
                $montantDecaisseQuery->where('micro_projets.statut', $request->statut);
            }

            $montantDecaisse = $montantDecaisseQuery->value('total') ?? 0;
        } catch (\Exception $e) {
            // Table might not exist yet, return 0
            $montantDecaisse = 0;
        }

        $kpis = [
            'nombre_agences' => AgenceRegionale::count(),
            'nombre_projets' => (clone $query)->count(),
            'nombre_promoteurs' => Promoteur::count(),
            'montant_financé' => (clone $query)->sum('montant_total'),
            'montant_décaissé' => $montantDecaisse,
            'emplois_créés' => (clone $query)->withCount('embauches')->get()->sum('embauches_count'),
        ];

        return response()->json(['data' => $kpis]);
    }

    /**
     * Get projects by agence
     */
    public function getProjetsParAgence(Request $request): JsonResponse
    {
        $query = MicroProjet::select('agences_regionales.nom as agence', DB::raw('COUNT(*) as count'))
            ->join('agences_regionales', 'micro_projets.agence_id', '=', 'agences_regionales.id')
            ->groupBy('agences_regionales.id', 'agences_regionales.nom');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get projects by status
     */
    public function getProjetsParStatut(Request $request): JsonResponse
    {
        $query = MicroProjet::select('statut', DB::raw('COUNT(*) as count'))
            ->groupBy('statut');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get financing by agence
     */
    public function getFinancementParAgence(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'agences_regionales.nom as agence',
            DB::raw('SUM(montant_total) as montant_total'),
            DB::raw('COUNT(*) as nombre_projets')
        )
            ->join('agences_regionales', 'micro_projets.agence_id', '=', 'agences_regionales.id')
            ->groupBy('agences_regionales.id', 'agences_regionales.nom');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get agence ranking
     */
    public function getClassementAgences(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'agences_regionales.id',
            'agences_regionales.nom as agence',
            DB::raw('COUNT(*) as nombre_projets'),
            DB::raw('SUM(montant_total) as montant_total'),
            DB::raw('SUM(CASE WHEN statut = "EN_FINANCEMENT" THEN 1 ELSE 0 END) as projets_en_financement'),
            DB::raw('SUM(CASE WHEN statut = "EN_DECAISSEMENT" THEN 1 ELSE 0 END) as projets_en_decaissement'),
            DB::raw('SUM(CASE WHEN statut = "TERMINE" THEN 1 ELSE 0 END) as projets_termines')
        )
            ->join('agences_regionales', 'micro_projets.agence_id', '=', 'agences_regionales.id')
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
        $query = MicroProjet::query();

        $this->applyFilters($query, $request);

        $alertes = [
            'dossiers_en_attente' => (clone $query)->where('statut', 'BROUILLON')->count(),
        ];

        // Try to get financements_non_decaisses - table might not exist yet
        try {
            $alertes['financements_non_decaisses'] = (clone $query)
                ->where('statut', 'EN_FINANCEMENT')
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
                ->where('statut', 'EN_REMBOURSEMENT')
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
     * Apply common filters
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->has('annee')) {
            $query->whereYear('micro_projets.created_at', $request->annee);
        }
        if ($request->has('region_id')) {
            $query->whereHas('agence', function ($q) use ($request) {
                $q->where('region_id', $request->region_id);
            });
        }
        if ($request->has('agence_id')) {
            $query->where('micro_projets.agence_id', $request->agence_id);
        }
        if ($request->has('secteur_id')) {
            $query->where('micro_projets.secteur_id', $request->secteur_id);
        }
        if ($request->has('statut')) {
            $query->where('micro_projets.statut', $request->statut);
        }
    }
}
