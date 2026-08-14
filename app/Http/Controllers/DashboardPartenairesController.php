<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\OrganismeFinancement;
use App\Models\MicroProjet;
use App\Models\PlanDecaissement;
use App\Models\PlanRemboursement;
use App\Models\Recouvrement;
use Illuminate\Support\Facades\DB;

class DashboardPartenairesController extends Controller
{
    /**
     * Get KPIs for Partenaires Dashboard
     */
    public function getKPIs(Request $request): JsonResponse
    {
        $query = MicroProjet::query();
        
        $this->applyFilters($query, $request);

        // Calculate KPIs using joins to avoid too many placeholders
        $montant_accorde = (clone $query)->sum('montant_total');
        
        $montant_decaisse = 0;
        try {
            $montant_decaisseQuery = PlanDecaissement::select(DB::raw('SUM(ligne_decaissements.montant_ligne) as total'))
                ->join('ligne_decaissements', 'plan_decaissements.id', '=', 'ligne_decaissements.plan_decaissement_id')
                ->join('micro_projets', 'plan_decaissements.micro_projet_id', '=', 'micro_projets.id')
                ->where('ligne_decaissements.statut', 'VALIDE');

            // Apply same filters to decaissement query
            if ($request->has('annee')) {
                $montant_decaisseQuery->whereYear('micro_projets.created_at', $request->annee);
            }
            if ($request->has('region_id')) {
                $montant_decaisseQuery->whereHas('microProjets.promoteur', function ($q) use ($request) {
                    $q->where('region_id', $request->region_id);
                });
            }
            if ($request->has('agence_id')) {
                $montant_decaisseQuery->where('micro_projets.agence_id', $request->agence_id);
            }
            if ($request->has('secteur_id')) {
                $montant_decaisseQuery->where('micro_projets.secteur_id', $request->secteur_id);
            }
            if ($request->has('statut')) {
                $montant_decaisseQuery->where('micro_projets.statut', $request->statut);
            }
            if ($request->has('partenaire_id')) {
                $montant_decaisseQuery->where('micro_projets.organisme_id', $request->partenaire_id);
            }

            $montant_decaisse = $montant_decaisseQuery->value('total') ?? 0;
        } catch (\Exception $e) {
            // Table might not exist yet, return 0
            $montant_decaisse = 0;
        }

        $montant_recupere = 0;
        try {
            $montant_recupereQuery = Recouvrement::select(DB::raw('SUM(montant_recouvre) as total'))
                ->join('micro_projets', 'recouvrements.micro_projet_id', '=', 'micro_projets.id');

            // Apply same filters to recouvrement query
            if ($request->has('annee')) {
                $montant_recupereQuery->whereYear('micro_projets.created_at', $request->annee);
            }
            if ($request->has('region_id')) {
                $montant_recupereQuery->whereHas('microProjets.promoteur', function ($q) use ($request) {
                    $q->where('region_id', $request->region_id);
                });
            }
            if ($request->has('agence_id')) {
                $montant_recupereQuery->where('micro_projets.agence_id', $request->agence_id);
            }
            if ($request->has('secteur_id')) {
                $montant_recupereQuery->where('micro_projets.secteur_id', $request->secteur_id);
            }
            if ($request->has('statut')) {
                $montant_recupereQuery->where('micro_projets.statut', $request->statut);
            }
            if ($request->has('partenaire_id')) {
                $montant_recupereQuery->where('micro_projets.organisme_id', $request->partenaire_id);
            }

            $montant_recupere = $montant_recupereQuery->value('total') ?? 0;
        } catch (\Exception $e) {
            // Table might not exist yet, return 0
            $montant_recupere = 0;
        }

        $encours = $montant_accorde - $montant_recupere;
        
        $taux_recouvrement = $montant_accorde > 0 
            ? round(($montant_recupere / $montant_accorde) * 100, 2) 
            : 0;

        $kpis = [
            'nombre_partenaires' => OrganismeFinancement::count(),
            'projets_finances' => (clone $query)->where('statut', '!=', 'BROUILLON')->count(),
            'montant_accorde' => $montant_accorde,
            'montant_decaisse' => $montant_decaisse,
            'encours' => $encours,
            'taux_recouvrement' => $taux_recouvrement,
        ];

        return response()->json(['data' => $kpis]);
    }

    /**
     * Get portfolio by partner
     */
    public function getPortefeuilleParPartenaire(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'organisme_financements.id',
            'organisme_financements.nom as partenaire',
            'organisme_financements.sigle',
            DB::raw('COUNT(*) as nombre_projets'),
            DB::raw('SUM(montant_total) as montant_total')
        )
            ->join('organisme_financements', 'micro_projets.organisme_id', '=', 'organisme_financements.id')
            ->whereNotNull('organisme_id')
            ->groupBy('organisme_financements.id', 'organisme_financements.nom', 'organisme_financements.sigle')
            ->orderBy('montant_total', 'desc');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get accorded vs disbursed by partner
     */
    public function getAccordeVsDecaisse(Request $request): JsonResponse
    {
        try {
            $query = MicroProjet::select(
                'organisme_financements.nom as partenaire',
                DB::raw('SUM(montant_total) as montant_accorde'),
                DB::raw('COALESCE(SUM(
                    CASE WHEN EXISTS (
                        SELECT 1 FROM plan_decaissements pd 
                        JOIN ligne_decaissements ld ON pd.id = ld.plan_decaissement_id 
                        WHERE pd.micro_projet_id = micro_projets.id AND ld.statut = "VALIDE"
                    ) THEN montant_total ELSE 0 END
                ), 0) as montant_decaisse')
            )
                ->join('organisme_financements', 'micro_projets.organisme_id', '=', 'organisme_financements.id')
                ->whereNotNull('organisme_id')
                ->groupBy('organisme_financements.id', 'organisme_financements.nom');

            $this->applyFilters($query, $request);

            $data = $query->get();

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            // Table ligne_decaissements might not exist yet, return only montant_accorde
            $query = MicroProjet::select(
                'organisme_financements.nom as partenaire',
                DB::raw('SUM(montant_total) as montant_accorde'),
                DB::raw('0 as montant_decaisse')
            )
                ->join('organisme_financements', 'micro_projets.organisme_id', '=', 'organisme_financements.id')
                ->whereNotNull('organisme_id')
                ->groupBy('organisme_financements.id', 'organisme_financements.nom');

            $this->applyFilters($query, $request);

            $data = $query->get();

            return response()->json(['data' => $data]);
        }
    }

    /**
     * Get financing status by partner
     */
    public function getEtatFinancements(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'organisme_financements.nom as partenaire',
            'statut',
            DB::raw('COUNT(*) as count')
        )
            ->join('organisme_financements', 'micro_projets.organisme_id', '=', 'organisme_financements.id')
            ->whereNotNull('organisme_id')
            ->groupBy('organisme_financements.id', 'organisme_financements.nom', 'statut');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get repayment evolution
     */
    public function getEvolutionRemboursements(Request $request): JsonResponse
    {
        try {
            $query = Recouvrement::select(
                DB::raw('DATE_FORMAT(date_recouvrement, "%Y-%m") as mois'),
                DB::raw('SUM(montant_recouvre) as montant_recupere'),
                DB::raw('COUNT(*) as nombre_operations')
            )
                ->whereNotNull('date_recouvrement')
                ->groupBy(DB::raw('DATE_FORMAT(date_recouvrement, "%Y-%m")'))
                ->orderBy('mois');

            if ($request->has('annee')) {
                $query->whereYear('date_recouvrement', $request->annee);
            }
            if ($request->has('partenaire_id')) {
                $query->whereHas('microProjet', function ($q) use ($request) {
                    $q->where('organisme_id', $request->partenaire_id);
                });
            }

            $data = $query->get();

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            // Table recouvrements might not exist yet, return empty array
            return response()->json(['data' => []]);
        }
    }

    /**
     * Get partner ranking
     */
    public function getClassementPartenaires(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'organisme_financements.id',
            'organisme_financements.nom as partenaire',
            'organisme_financements.sigle',
            DB::raw('COUNT(*) as nombre_projets'),
            DB::raw('SUM(montant_total) as montant_total'),
            DB::raw('SUM(CASE WHEN statut = "EN_FINANCEMENT" THEN 1 ELSE 0 END) as projets_en_financement'),
            DB::raw('SUM(CASE WHEN statut = "EN_DECAISSEMENT" THEN 1 ELSE 0 END) as projets_en_decaissement'),
            DB::raw('SUM(CASE WHEN statut = "EN_REMBOURSEMENT" THEN 1 ELSE 0 END) as projets_en_remboursement'),
            DB::raw('SUM(CASE WHEN statut = "TERMINE" THEN 1 ELSE 0 END) as projets_termines')
        )
            ->join('organisme_financements', 'micro_projets.organisme_id', '=', 'organisme_financements.id')
            ->whereNotNull('organisme_id')
            ->groupBy('organisme_financements.id', 'organisme_financements.nom', 'organisme_financements.sigle')
            ->orderBy('montant_total', 'desc');

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

        $alertes = [];

        // Try to get financements_non_decaisses - table might not exist yet
        try {
            $alertes['financements_non_decaisses'] = (clone $query)
                ->where('statut', 'EN_FINANCEMENT')
                ->whereDoesntHave('planDecaissement', function ($q) {
                    $q->whereHas('ligneDecaissements', function ($sq) {
                        $sq->where('statut', 'VALIDE');
                    });
                })
                ->count();
        } catch (\Exception $e) {
            $alertes['financements_non_decaisses'] = 0;
        }

        // Try to get impayes - table might not exist yet
        try {
            $alertes['impayes'] = (clone $query)
                ->where('statut', 'EN_REMBOURSEMENT')
                ->whereHas('planRemboursement', function ($q) {
                    $q->where('statut', 'EN_RETARD');
                })
                ->count();
        } catch (\Exception $e) {
            $alertes['impayes'] = 0;
        }

        // Try to get remboursements_en_retard - table might not exist yet
        try {
            $remboursementsQuery = PlanRemboursement::where('statut', 'EN_RETARD')
                ->whereHas('microProjet', function ($q) use ($request) {
                    if ($request->has('partenaire_id')) {
                        $q->where('organisme_id', $request->partenaire_id);
                    }
                });
            $alertes['remboursements_en_retard'] = $remboursementsQuery->count();
        } catch (\Exception $e) {
            $alertes['remboursements_en_retard'] = 0;
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
            $query->whereHas('promoteur', function ($q) use ($request) {
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
        if ($request->has('partenaire_id')) {
            $query->where('micro_projets.organisme_id', $request->partenaire_id);
        }
    }
}
