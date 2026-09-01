<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Entreprise;
use App\Models\MicroProjet;
use App\Models\Embauche;
use App\Models\Secteur;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class DashboardEntreprisesController extends Controller
{
    /**
     * Get KPIs for Entreprises Dashboard
     */
    public function getKPIs(Request $request): JsonResponse
    {
        $query = Entreprise::query();
        
        $this->applyFilters($query, $request);

        // Calculate KPIs using joins to avoid too many placeholders
        $entreprisesRecruteuses = Entreprise::whereHas('embauches')->count();
        
        $emploisCreesQuery = Embauche::select(DB::raw('COUNT(*) as total'))
            ->join('entreprises', 'embauches.entreprise_id', '=', 'entreprises.id');

        // Apply same filters to emplois query
        if ($request->has('annee')) {
            $emploisCreesQuery->whereYear('entreprises.created_at', $request->annee);
        }
        if ($request->has('region_id')) {
            $emploisCreesQuery->where('entreprises.region_id', $request->region_id);
        }
        if ($request->has('secteur_id')) {
            $emploisCreesQuery->whereHas('microProjet', function ($q) use ($request) {
                $q->where('secteur_id', $request->secteur_id);
            });
        }

        $emploisCrees = $emploisCreesQuery->value('total') ?? 0;
        
        $projetsAssociesQuery = MicroProjet::select(DB::raw('COUNT(*) as total'))
            ->join('embauches', 'micro_projets.promoteur_id', '=', 'embauches.promoteur_id')
            ->join('entreprises', 'embauches.entreprise_id', '=', 'entreprises.id');

        // Apply same filters to projets query
        if ($request->has('annee')) {
            $projetsAssociesQuery->whereYear('entreprises.created_at', $request->annee);
        }
        if ($request->has('region_id')) {
            $projetsAssociesQuery->where('entreprises.region_id', $request->region_id);
        }
        if ($request->has('secteur_id')) {
            $projetsAssociesQuery->where('micro_projets.secteur_id', $request->secteur_id);
        }

        $projetsAssocies = $projetsAssociesQuery->value('total') ?? 0;

        $kpis = [
            'nombre_entreprises' => (clone $query)->count(),
            'entreprises_recruteuses' => $entreprisesRecruteuses,
            'emplois_crees' => $emploisCrees,
            'projets_associes' => $projetsAssocies,
            'secteurs_representes' => (clone $query)->whereNotNull('region_id')->count(),
            'regions_representees' => (clone $query)->whereNotNull('region_id')
                ->pluck('region_id')->unique()->count(),
        ];

        return response()->json(['data' => $kpis]);
    }

    /**
     * Get enterprises by region
     */
    public function getEntreprisesParRegion(Request $request): JsonResponse
    {
        $query = Entreprise::select(
            'regions.nom as region',
            DB::raw('COUNT(*) as nombre_entreprises')
        )
            ->leftJoin('regions', 'entreprises.region_id', '=', 'regions.id')
            ->groupBy('regions.id', 'regions.nom')
            ->orderBy('nombre_entreprises', 'desc');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get jobs by sector
     */
    public function getEmploisParSecteur(Request $request): JsonResponse
    {
        $query = Embauche::select(
            'secteurs.libelle as secteur',
            DB::raw('COUNT(*) as nombre_emplois')
        )
            ->join('micro_projets', 'embauches.micro_projet_id', '=', 'micro_projets.id')
            ->leftJoin('secteurs', 'micro_projets.secteur_id', '=', 'secteurs.id')
            ->groupBy('secteurs.id', 'secteurs.libelle')
            ->orderBy('nombre_emplois', 'desc');

        if ($request->has('annee')) {
            $query->whereYear('embauches.created_at', $request->annee);
        }
        if ($request->has('region_id')) {
            $query->whereHas('entreprise', function ($q) use ($request) {
                $q->where('region_id', $request->region_id);
            });
        }

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get job types
     */
    public function getTypesEmplois(Request $request): JsonResponse
    {
        $query = Embauche::select(
            'type_emplois.libelle as type_emploi',
            DB::raw('COUNT(*) as nombre')
        )
            ->leftJoin('type_emplois', 'embauches.type_emploi_id', '=', 'type_emplois.id')
            ->groupBy('type_emplois.id', 'type_emplois.libelle')
            ->orderBy('nombre', 'desc');

        if ($request->has('annee')) {
            $query->whereYear('embauches.created_at', $request->annee);
        }

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get top recruiting enterprises
     */
    public function getTopEntreprisesRecruteuses(Request $request): JsonResponse
    {
        $query = Entreprise::select(
            'entreprises.id',
            'entreprises.raison_sociale',
            'entreprises.sigle',
            DB::raw('COUNT(embauches.id) as nombre_emplois')
        )
            ->leftJoin('embauches', 'entreprises.id', '=', 'embauches.entreprise_id')
            ->groupBy('entreprises.id', 'entreprises.raison_sociale', 'entreprises.sigle')
            ->orderBy('nombre_emplois', 'desc')
            ->limit(20);

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get enterprises by sector
     */
    public function getEntreprisesParSecteur(Request $request): JsonResponse
    {
        $query = Entreprise::select(
            'type_entreprises.libelle as type_entreprise',
            DB::raw('COUNT(*) as nombre_entreprises')
        )
            ->leftJoin('type_entreprises', 'entreprises.type_entreprise_id', '=', 'type_entreprises.id')
            ->groupBy('type_entreprises.id', 'type_entreprises.libelle')
            ->orderBy('nombre_entreprises', 'desc');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get enterprise ranking
     */
    public function getClassementEntreprises(Request $request): JsonResponse
    {
        $query = Entreprise::select(
            'entreprises.id',
            'entreprises.raison_sociale',
            'entreprises.sigle',
            'regions.nom as region',
            DB::raw('COUNT(embauches.id) as nombre_emplois'),
            DB::raw('COUNT(DISTINCT micro_projets.id) as nombre_projets_associes')
        )
            ->leftJoin('embauches', 'entreprises.id', '=', 'embauches.entreprise_id')
            ->leftJoin('micro_projets', 'embauches.micro_projet_id', '=', 'micro_projets.id')
            ->leftJoin('regions', 'entreprises.region_id', '=', 'regions.id')
            ->groupBy('entreprises.id', 'entreprises.raison_sociale', 'entreprises.sigle', 'regions.nom')
            ->orderBy('nombre_emplois', 'desc');

        $this->applyFilters($query, $request);

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Get alerts
     */
    public function getAlertes(Request $request): JsonResponse
    {
        $query = Entreprise::query();

        $this->applyFilters($query, $request);

        // Use joins to avoid too many placeholders
        $entreprisesSansProjets = Entreprise::leftJoin('embauches', 'entreprises.id', '=', 'embauches.entreprise_id')
            ->whereNull('embauches.id')
            ->count();

        $entreprisesInactives = Entreprise::leftJoin('embauches', 'entreprises.id', '=', 'embauches.entreprise_id')
            ->whereNull('embauches.id')
            ->count();

        $projetsSansEmbauches = MicroProjet::leftJoin('embauches', 'micro_projets.promoteur_id', '=', 'embauches.promoteur_id')
            ->whereNull('embauches.id')
            ->count();

        $alertes = [
            'entreprises_sans_projets' => $entreprisesSansProjets,
            'entreprises_inactives' => $entreprisesInactives,
            'projets_sans_embauches' => $projetsSansEmbauches,
        ];

        return response()->json(['data' => $alertes]);
    }

    /**
     * Apply common filters
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->has('annee')) {
            $query->whereYear('entreprises.created_at', $request->annee);
        }
        if ($request->has('region_id')) {
            $query->where('entreprises.region_id', $request->region_id);
        }
        if ($request->has('secteur_id')) {
            $query->whereHas('embauches', function ($q) use ($request) {
                $q->whereHas('microProjet', function ($sq) use ($request) {
                    $sq->where('secteur_id', $request->secteur_id);
                });
            });
        }
    }
}
