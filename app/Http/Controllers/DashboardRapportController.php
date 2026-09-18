<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\AgenceRegionale;
use App\Models\OrganismeFinancement;
use App\Models\Guichet;
use App\Models\Secteur;
use App\Models\SousSecteur;
use App\Models\MicroProjet;
use App\Models\Remboursement;
use Illuminate\Support\Facades\DB;

class DashboardRapportController extends Controller
{
    // =========================================================================
    // MÉTHODES PRIVÉES — HELPERS
    // =========================================================================

    /**
     * Applique les filtres communs sur une query MicroProjet
     * qui a déjà un JOIN sur `promoteurs`.
     *
     * Filtres supportés :
     *   annee        → année de création du micro-projet
     *   agence_id    → promoteurs.agenceregionale_id
     *   organisme_id → micro_projets.organisme_id
     *   guichet_id   → micro_projets.guichet_id
     *   statut       → micro_projets.statut
     *   genre        → "femme" ou "homme" (via sexes.libelle)
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('annee')) {
            $query->whereYear('micro_projets.created_at', $request->annee);
        }
        if ($request->filled('agence_id')) {
            $query->where('promoteurs.agenceregionale_id', $request->agence_id);
        }
        if ($request->filled('organisme_id')) {
            $query->where('micro_projets.organisme_id', $request->organisme_id);
        }
        if ($request->filled('guichet_id')) {
            $query->where('micro_projets.guichet_id', $request->guichet_id);
        }
        if ($request->filled('statut')) {
            $query->where('micro_projets.statut', $request->statut);
        }
        if ($request->filled('genre')) {
            $genre = strtolower($request->genre);
            if ($genre === 'femme') {
                $query->where(function ($q) {
                    $q->where(DB::raw('LOWER(sexes.libelle)'), 'like', '%femme%')
                      ->orWhere(DB::raw('LOWER(sexes.libelle)'), '=', 'f');
                });
            } elseif ($genre === 'homme') {
                $query->where(function ($q) {
                    $q->where(DB::raw('LOWER(sexes.libelle)'), 'not like', '%femme%')
                      ->where(DB::raw('LOWER(sexes.libelle)'), '!=', 'f');
                });
            }
        }
    }

    /**
     * Retourne le montant total financé (issu de budgets.montant_accorde)
     * groupé par micro_projet_id, filtré par les mêmes critères.
     * Utilisé pour les rapports financiers.
     */
    private function getMontantRembourse(array $microProjetIds): array
    {
        if (empty($microProjetIds)) {
            return ['montant_rembourse' => 0, 'montant_impaye' => 0, 'montant_echu' => 0];
        }

        $row = Remboursement::whereIn('plan_remboursement_id', function ($q) use ($microProjetIds) {
            $q->select('id')
              ->from('plan_remboursements')
              ->whereIn('micro_projet_id', $microProjetIds);
        })->selectRaw('
            COALESCE(SUM(montant_paye), 0)   as montant_rembourse,
            COALESCE(SUM(montant_impaye), 0) as montant_impaye,
            COALESCE(SUM(montant_echu), 0)   as montant_echu
        ')->first();

        return [
            'montant_rembourse' => (float) $row->montant_rembourse,
            'montant_impaye'    => (float) $row->montant_impaye,
            'montant_echu'      => (float) $row->montant_echu,
        ];
    }

    // =========================================================================
    // 1. RAPPORT INDIVIDUEL PAR ORGANISME
    // =========================================================================

    /**
     * GET /api/rapport/organisme/{organisme_id}
     *
     * Rapport détaillé pour un organisme de financement spécifique.
     * Champs : nombre_projets, nombre_beneficiaires, nombre_femmes,
     *          montant_total_financement, nombre_emplois_crees,
     *          montant_rembourse, taux_remboursement, taux_impayes
     */
    public function rapportOrganisme(Request $request, int $organisme_id): JsonResponse
    {
        $organisme = OrganismeFinancement::with('typeOrganisme')->find($organisme_id);

        if (! $organisme) {
            return response()->json(['message' => 'Organisme introuvable.'], 404);
        }

        $query = MicroProjet::query()
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->where('micro_projets.organisme_id', $organisme_id)
            ->selectRaw('
                COUNT(DISTINCT micro_projets.id) as nombre_projets,
                COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires,
                COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes,
                COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement
            ');

        $this->applyFilters($query, $request);

        $stats = $query->first();

        // Emplois créés via la table embauches liée aux micro-projets de cet organisme
        $microProjetIds = MicroProjet::where('organisme_id', $organisme_id)->pluck('id')->toArray();

        $nombre_emplois = DB::table('embauches')
            ->whereIn('micro_projet_id', $microProjetIds)
            ->count();

        $remb = $this->getMontantRembourse($microProjetIds);

        $montant_financement  = (float) $stats->montant_total_financement;
        $nombre_beneficiaires = (int) $stats->nombre_beneficiaires;
        $nombre_femmes        = (int) $stats->nombre_femmes;

        $pourcentage_femmes   = $nombre_beneficiaires > 0
            ? round(($nombre_femmes / $nombre_beneficiaires) * 100, 2)
            : 0;

        $taux_remboursement = $montant_financement > 0
            ? round(($remb['montant_rembourse'] / $montant_financement) * 100, 2)
            : 0;

        $taux_impayes = $remb['montant_echu'] > 0
            ? round(($remb['montant_impaye'] / $remb['montant_echu']) * 100, 2)
            : 0;

        return response()->json([
            'organisme' => [
                'id'    => $organisme->id,
                'nom'   => $organisme->nom,
                'sigle' => $organisme->sigle,
                'type'  => $organisme->typeOrganisme?->libelle ?? null,
            ],
            'nombre_projets'             => (int) $stats->nombre_projets,
            'nombre_beneficiaires'       => $nombre_beneficiaires,
            'nombre_femmes'              => $nombre_femmes,
            'pourcentage_femmes'         => $pourcentage_femmes,
            'montant_total_financement'  => $montant_financement,
            'nombre_emplois_crees'       => $nombre_emplois,
            'montant_rembourse'          => $remb['montant_rembourse'],
            'montant_impaye'             => $remb['montant_impaye'],
            'taux_remboursement'         => $taux_remboursement,
            'taux_impayes'               => $taux_impayes,
        ]);
    }

    // =========================================================================
    // 2. RAPPORT PAR ORGANISME (liste — ancienne statParOrganisme corrigée)
    // =========================================================================

    /**
     * GET /api/rapport/organismes
     *
     * Filtres : annee, agence_id, statut
     * Correction : suppression de la colonne inexistante plan_remboursements.interets
     *              + filtre organisme_id déplacé dans applyFilters
     */
    public function statParOrganisme(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'organisme_financements.id as organisme_id',
            'organisme_financements.nom as organisme',
            'organisme_financements.sigle',
            DB::raw('COUNT(DISTINCT micro_projets.id) as nombre_projets'),
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_promoteurs'),
            DB::raw('COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('organisme_financements', 'micro_projets.organisme_id', '=', 'organisme_financements.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->whereNotNull('micro_projets.organisme_id')
            ->groupBy('organisme_financements.id', 'organisme_financements.nom', 'organisme_financements.sigle')
            ->orderBy('montant_total_financement', 'desc');

        $this->applyFilters($query, $request);

        $organismes = $query->get();

        $montant_global = $organismes->sum('montant_total_financement');

        // Emplois créés par organisme
        $emplois_par_organisme = DB::table('embauches')
            ->join('micro_projets', 'embauches.micro_projet_id', '=', 'micro_projets.id')
            ->whereNotNull('micro_projets.organisme_id')
            ->select('micro_projets.organisme_id', DB::raw('COUNT(embauches.id) as total_emplois'))
            ->groupBy('micro_projets.organisme_id')
            ->get()
            ->keyBy('organisme_id');

        $data = $organismes->map(function ($organisme) use ($montant_global, $emplois_par_organisme) {
            $montant_financement = (float) $organisme->montant_total_financement;
            $nombre_promoteurs   = (int) $organisme->nombre_promoteurs;
            $nombre_femmes       = (int) $organisme->nombre_femmes;
            $nombre_emplois      = isset($emplois_par_organisme[$organisme->organisme_id])
                ? (int) $emplois_par_organisme[$organisme->organisme_id]->total_emplois
                : 0;

            $pourcentage_femmes  = $nombre_promoteurs > 0
                ? round(($nombre_femmes / $nombre_promoteurs) * 100, 2)
                : 0;
            $pourcentage_montant = $montant_global > 0
                ? round(($montant_financement / $montant_global) * 100, 2)
                : 0;

            return [
                'organisme_id'              => $organisme->organisme_id,
                'organisme'                 => $organisme->organisme,
                'sigle'                     => $organisme->sigle,
                'nombre_projets'            => (int) $organisme->nombre_projets,
                'nombre_beneficiaires'      => $nombre_promoteurs,
                'nombre_femmes'             => $nombre_femmes,
                'pourcentage_femmes'        => $pourcentage_femmes,
                'montant_total_financement' => $montant_financement,
                'nombre_emplois_crees'      => $nombre_emplois,
                'pourcentage_montant'       => $pourcentage_montant,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_projets'            => $data->sum('nombre_projets'),
                'nombre_beneficiaires'      => $data->sum('nombre_beneficiaires'),
                'nombre_femmes'             => $data->sum('nombre_femmes'),
                'montant_total_financement' => $data->sum('montant_total_financement'),
                'nombre_emplois_crees'      => $data->sum('nombre_emplois_crees'),
            ],
        ]);
    }

    // =========================================================================
    // 3. RAPPORT PAR AGENCE (corrigé)
    // =========================================================================

    /**
     * GET /api/rapport/agences
     *
     * Filtres : annee, agence_id, statut
     * Ajout : nombre_emplois_crees
     * Correction : calcul du décaissé via ligne_decaissements (inchangé, correct)
     */
    public function statParAgence(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'agences_regionales.id as agence_id',
            'agences_regionales.nom as agence',
            DB::raw('COUNT(DISTINCT micro_projets.id) as nombre_projets'),
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_promoteurs'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('agences_regionales', 'promoteurs.agenceregionale_id', '=', 'agences_regionales.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->groupBy('agences_regionales.id', 'agences_regionales.nom')
            ->orderBy('montant_total_financement', 'desc');

        $this->applyFilters($query, $request);

        $agences = $query->get();

        // Montant décaissé validé par agence
        $decaisse_par_agence = collect();
        try {
            $decaisse_par_agence = MicroProjet::select(
                'promoteurs.agenceregionale_id as agence_id',
                DB::raw('COALESCE(SUM(ligne_decaissements.montant_ligne), 0) as montant_decaisse')
            )
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->join('plan_decaissements', 'micro_projets.id', '=', 'plan_decaissements.micro_projet_id')
                ->join('ligne_decaissements', 'plan_decaissements.id', '=', 'ligne_decaissements.plan_decaissement_id')
                ->where('ligne_decaissements.statut', 'VALIDE')
                ->groupBy('promoteurs.agenceregionale_id')
                ->get()
                ->keyBy('agence_id');
        } catch (\Exception $e) {
            $decaisse_par_agence = collect();
        }

        // Emplois créés par agence (via promoteurs.agenceregionale_id)
        $emplois_par_agence = DB::table('embauches')
            ->join('micro_projets', 'embauches.micro_projet_id', '=', 'micro_projets.id')
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->select('promoteurs.agenceregionale_id as agence_id', DB::raw('COUNT(embauches.id) as total_emplois'))
            ->groupBy('promoteurs.agenceregionale_id')
            ->get()
            ->keyBy('agence_id');

        $data = $agences->map(function ($agence) use ($decaisse_par_agence, $emplois_par_agence) {
            $montant_financement = (float) $agence->montant_total_financement;
            $montant_decaisse    = isset($decaisse_par_agence[$agence->agence_id])
                ? (float) $decaisse_par_agence[$agence->agence_id]->montant_decaisse
                : 0;
            $nombre_emplois = isset($emplois_par_agence[$agence->agence_id])
                ? (int) $emplois_par_agence[$agence->agence_id]->total_emplois
                : 0;

            return [
                'agence_id'                 => $agence->agence_id,
                'agence'                    => $agence->agence,
                'nombre_projets'            => (int) $agence->nombre_projets,
                'nombre_beneficiaires'      => (int) $agence->nombre_promoteurs,
                'montant_total_financement' => $montant_financement,
                'montant_decaisse'          => $montant_decaisse,
                'arriere_credit'            => max(0, $montant_financement - $montant_decaisse),
                'nombre_emplois_crees'      => $nombre_emplois,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_projets'            => $data->sum('nombre_projets'),
                'nombre_beneficiaires'      => $data->sum('nombre_beneficiaires'),
                'montant_total_financement' => $data->sum('montant_total_financement'),
                'montant_decaisse'          => $data->sum('montant_decaisse'),
                'arriere_credit'            => $data->sum('arriere_credit'),
                'nombre_emplois_crees'      => $data->sum('nombre_emplois_crees'),
            ],
        ]);
    }

    // =========================================================================
    // 4. RAPPORT PAR GUICHET
    // =========================================================================

    /**
     * GET /api/rapport/guichets
     *
     * Filtres : annee, agence_id, organisme_id, statut, guichet_id
     */
    public function statParGuichet(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'guichets.id as guichet_id',
            'guichets.libelle as guichet',
            'guichets.code',
            DB::raw('COUNT(DISTINCT micro_projets.id) as nombre_projets'),
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires'),
            DB::raw('COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('guichets', 'micro_projets.guichet_id', '=', 'guichets.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->whereNotNull('micro_projets.guichet_id')
            ->groupBy('guichets.id', 'guichets.libelle', 'guichets.code')
            ->orderBy('montant_total_financement', 'desc');

        $this->applyFilters($query, $request);

        $guichets = $query->get();

        // Emplois créés par guichet
        $emplois_par_guichet = DB::table('embauches')
            ->join('micro_projets', 'embauches.micro_projet_id', '=', 'micro_projets.id')
            ->whereNotNull('micro_projets.guichet_id')
            ->select('micro_projets.guichet_id', DB::raw('COUNT(embauches.id) as total_emplois'))
            ->groupBy('micro_projets.guichet_id')
            ->get()
            ->keyBy('guichet_id');

        $data = $guichets->map(function ($guichet) use ($emplois_par_guichet) {
            $nombre_beneficiaires = (int) $guichet->nombre_beneficiaires;
            $nombre_femmes        = (int) $guichet->nombre_femmes;
            $nombre_emplois       = isset($emplois_par_guichet[$guichet->guichet_id])
                ? (int) $emplois_par_guichet[$guichet->guichet_id]->total_emplois
                : 0;

            $pourcentage_femmes = $nombre_beneficiaires > 0
                ? round(($nombre_femmes / $nombre_beneficiaires) * 100, 2)
                : 0;

            return [
                'guichet_id'                => $guichet->guichet_id,
                'guichet'                   => $guichet->guichet,
                'code'                      => $guichet->code,
                'nombre_projets'            => (int) $guichet->nombre_projets,
                'nombre_beneficiaires'      => $nombre_beneficiaires,
                'nombre_femmes'             => $nombre_femmes,
                'pourcentage_femmes'        => $pourcentage_femmes,
                'montant_total_financement' => (float) $guichet->montant_total_financement,
                'nombre_emplois_crees'      => $nombre_emplois,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_projets'            => $data->sum('nombre_projets'),
                'nombre_beneficiaires'      => $data->sum('nombre_beneficiaires'),
                'nombre_femmes'             => $data->sum('nombre_femmes'),
                'montant_total_financement' => $data->sum('montant_total_financement'),
                'nombre_emplois_crees'      => $data->sum('nombre_emplois_crees'),
            ],
        ]);
    }

    // =========================================================================
    // 5. RAPPORT PAR ANNÉE
    // =========================================================================

    /**
     * GET /api/rapport/annees
     *
     * Filtres : annee (pour cibler une année), agence_id, organisme_id,
     *           guichet_id, statut, genre
     */
    public function statParAnnee(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            DB::raw('YEAR(micro_projets.created_at) as annee'),
            DB::raw('COUNT(DISTINCT micro_projets.id) as nombre_projets'),
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires'),
            DB::raw('COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->groupBy(DB::raw('YEAR(micro_projets.created_at)'))
            ->orderBy('annee', 'desc');

        $this->applyFilters($query, $request);

        $annees = $query->get();

        // Emplois créés par année (basé sur l'année de création du micro-projet)
        $emplois_par_annee = DB::table('embauches')
            ->join('micro_projets', 'embauches.micro_projet_id', '=', 'micro_projets.id')
            ->select(DB::raw('YEAR(micro_projets.created_at) as annee'), DB::raw('COUNT(embauches.id) as total_emplois'))
            ->groupBy(DB::raw('YEAR(micro_projets.created_at)'))
            ->get()
            ->keyBy('annee');

        $data = $annees->map(function ($row) use ($emplois_par_annee) {
            $nombre_beneficiaires = (int) $row->nombre_beneficiaires;
            $nombre_femmes        = (int) $row->nombre_femmes;
            $nombre_emplois       = isset($emplois_par_annee[$row->annee])
                ? (int) $emplois_par_annee[$row->annee]->total_emplois
                : 0;

            $pourcentage_femmes = $nombre_beneficiaires > 0
                ? round(($nombre_femmes / $nombre_beneficiaires) * 100, 2)
                : 0;

            return [
                'annee'                     => (int) $row->annee,
                'nombre_projets'            => (int) $row->nombre_projets,
                'nombre_beneficiaires'      => $nombre_beneficiaires,
                'nombre_femmes'             => $nombre_femmes,
                'pourcentage_femmes'        => $pourcentage_femmes,
                'montant_total_financement' => (float) $row->montant_total_financement,
                'nombre_emplois_crees'      => $nombre_emplois,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_projets'            => $data->sum('nombre_projets'),
                'nombre_beneficiaires'      => $data->sum('nombre_beneficiaires'),
                'nombre_femmes'             => $data->sum('nombre_femmes'),
                'montant_total_financement' => $data->sum('montant_total_financement'),
                'nombre_emplois_crees'      => $data->sum('nombre_emplois_crees'),
            ],
        ]);
    }

    // =========================================================================
    // 6. RAPPORT PAR SECTEUR (corrigé)
    // =========================================================================

    /**
     * GET /api/rapport/secteurs
     *
     * Filtres : annee, agence_id, organisme_id, guichet_id, statut, secteur_id
     * Correction : suppression de plan_remboursements.interets + ajout emplois
     * Note : secteur porté par le promoteur (promoteurs.secteuractivite_id)
     */
    public function statParSecteur(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'secteurs.id as secteur_id',
            'secteurs.nom as secteur',
            'secteurs.libelle as secteur_libelle',
            DB::raw('COUNT(DISTINCT micro_projets.id) as nombre_projets'),
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires'),
            DB::raw('COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->join('secteurs', 'promoteurs.secteuractivite_id', '=', 'secteurs.id')
            ->whereNotNull('promoteurs.secteuractivite_id')
            ->groupBy('secteurs.id', 'secteurs.nom', 'secteurs.libelle')
            ->orderBy('montant_total_financement', 'desc');

        $this->applyFilters($query, $request);

        if ($request->filled('secteur_id')) {
            $query->where('promoteurs.secteuractivite_id', $request->secteur_id);
        }

        $secteurs = $query->get();
        $montant_global = $secteurs->sum('montant_total_financement');

        // Emplois créés par secteur
        $emplois_par_secteur = DB::table('embauches')
            ->join('micro_projets', 'embauches.micro_projet_id', '=', 'micro_projets.id')
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->whereNotNull('promoteurs.secteuractivite_id')
            ->select('promoteurs.secteuractivite_id as secteur_id', DB::raw('COUNT(embauches.id) as total_emplois'))
            ->groupBy('promoteurs.secteuractivite_id')
            ->get()
            ->keyBy('secteur_id');

        $data = $secteurs->map(function ($secteur) use ($montant_global, $emplois_par_secteur) {
            $montant_financement  = (float) $secteur->montant_total_financement;
            $nombre_beneficiaires = (int) $secteur->nombre_beneficiaires;
            $nombre_femmes        = (int) $secteur->nombre_femmes;
            $nombre_emplois       = isset($emplois_par_secteur[$secteur->secteur_id])
                ? (int) $emplois_par_secteur[$secteur->secteur_id]->total_emplois
                : 0;

            $pourcentage_femmes  = $nombre_beneficiaires > 0
                ? round(($nombre_femmes / $nombre_beneficiaires) * 100, 2)
                : 0;
            $pourcentage_montant = $montant_global > 0
                ? round(($montant_financement / $montant_global) * 100, 2)
                : 0;

            return [
                'secteur_id'                => $secteur->secteur_id,
                'secteur'                   => $secteur->secteur,
                'secteur_libelle'           => $secteur->secteur_libelle,
                'nombre_projets'            => (int) $secteur->nombre_projets,
                'nombre_beneficiaires'      => $nombre_beneficiaires,
                'nombre_femmes'             => $nombre_femmes,
                'pourcentage_femmes'        => $pourcentage_femmes,
                'montant_total_financement' => $montant_financement,
                'pourcentage_montant'       => $pourcentage_montant,
                'nombre_emplois_crees'      => $nombre_emplois,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_projets'            => $data->sum('nombre_projets'),
                'nombre_beneficiaires'      => $data->sum('nombre_beneficiaires'),
                'nombre_femmes'             => $data->sum('nombre_femmes'),
                'montant_total_financement' => $data->sum('montant_total_financement'),
                'nombre_emplois_crees'      => $data->sum('nombre_emplois_crees'),
            ],
        ]);
    }

    // =========================================================================
    // 7. RAPPORT PAR SOUS-SECTEUR (corrigé)
    // =========================================================================

    /**
     * GET /api/rapport/sous-secteurs
     *
     * Filtres : annee, agence_id, organisme_id, guichet_id, statut,
     *           secteur_id, sous_secteur_id
     * Correction : suppression de plan_remboursements.interets + ajout emplois
     */
    public function statParSousSecteur(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'sous_secteurs.id as sous_secteur_id',
            'sous_secteurs.libelle as sous_secteur',
            'secteurs.id as secteur_id',
            'secteurs.nom as secteur',
            DB::raw('COUNT(DISTINCT micro_projets.id) as nombre_projets'),
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires'),
            DB::raw('COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->join('sous_secteurs', 'promoteurs.soussecteuractivite_id', '=', 'sous_secteurs.id')
            ->join('secteurs', 'sous_secteurs.secteur_id', '=', 'secteurs.id')
            ->whereNotNull('promoteurs.soussecteuractivite_id')
            ->groupBy('sous_secteurs.id', 'sous_secteurs.libelle', 'secteurs.id', 'secteurs.nom')
            ->orderBy('montant_total_financement', 'desc');

        $this->applyFilters($query, $request);

        if ($request->filled('secteur_id')) {
            $query->where('sous_secteurs.secteur_id', $request->secteur_id);
        }
        if ($request->filled('sous_secteur_id')) {
            $query->where('sous_secteurs.id', $request->sous_secteur_id);
        }

        $sous_secteurs = $query->get();
        $montant_global = $sous_secteurs->sum('montant_total_financement');

        // Emplois créés par sous-secteur
        $emplois_par_ss = DB::table('embauches')
            ->join('micro_projets', 'embauches.micro_projet_id', '=', 'micro_projets.id')
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->whereNotNull('promoteurs.soussecteuractivite_id')
            ->select('promoteurs.soussecteuractivite_id as sous_secteur_id', DB::raw('COUNT(embauches.id) as total_emplois'))
            ->groupBy('promoteurs.soussecteuractivite_id')
            ->get()
            ->keyBy('sous_secteur_id');

        $data = $sous_secteurs->map(function ($ss) use ($montant_global, $emplois_par_ss) {
            $montant_financement  = (float) $ss->montant_total_financement;
            $nombre_beneficiaires = (int) $ss->nombre_beneficiaires;
            $nombre_femmes        = (int) $ss->nombre_femmes;
            $nombre_emplois       = isset($emplois_par_ss[$ss->sous_secteur_id])
                ? (int) $emplois_par_ss[$ss->sous_secteur_id]->total_emplois
                : 0;

            $pourcentage_femmes  = $nombre_beneficiaires > 0
                ? round(($nombre_femmes / $nombre_beneficiaires) * 100, 2)
                : 0;
            $pourcentage_montant = $montant_global > 0
                ? round(($montant_financement / $montant_global) * 100, 2)
                : 0;

            return [
                'sous_secteur_id'           => $ss->sous_secteur_id,
                'sous_secteur'              => $ss->sous_secteur,
                'secteur_id'                => $ss->secteur_id,
                'secteur'                   => $ss->secteur,
                'nombre_projets'            => (int) $ss->nombre_projets,
                'nombre_beneficiaires'      => $nombre_beneficiaires,
                'nombre_femmes'             => $nombre_femmes,
                'pourcentage_femmes'        => $pourcentage_femmes,
                'montant_total_financement' => $montant_financement,
                'pourcentage_montant'       => $pourcentage_montant,
                'nombre_emplois_crees'      => $nombre_emplois,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_projets'            => $data->sum('nombre_projets'),
                'nombre_beneficiaires'      => $data->sum('nombre_beneficiaires'),
                'nombre_femmes'             => $data->sum('nombre_femmes'),
                'montant_total_financement' => $data->sum('montant_total_financement'),
                'nombre_emplois_crees'      => $data->sum('nombre_emplois_crees'),
            ],
        ]);
    }

    // =========================================================================
    // 8. SYNTHÈSE CROISÉE
    // =========================================================================

    /**
     * GET /api/rapport/synthese
     *
     * Filtres (tous combinables) :
     *   annee, agence_id, organisme_id, guichet_id, secteur_id,
     *   sous_secteur_id, statut, genre
     */
    public function synthese(Request $request): JsonResponse
    {
        // --- Agrégat principal ---
        $query = DB::table('micro_projets')
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->selectRaw('
                COUNT(DISTINCT micro_projets.id) as nombre_projets,
                COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires,
                COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes,
                COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement
            ');

        $this->applyRawFilters($query, $request);

        if ($request->filled('secteur_id')) {
            $query->where('promoteurs.secteuractivite_id', $request->secteur_id);
        }
        if ($request->filled('sous_secteur_id')) {
            $query->where('promoteurs.soussecteuractivite_id', $request->sous_secteur_id);
        }

        $stats = $query->first();

        // --- Emplois créés : même filtres, comptage direct via embauches ---
        $emploisQuery = DB::table('embauches')
            ->join('micro_projets', 'embauches.micro_projet_id', '=', 'micro_projets.id')
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id');

        $this->applyRawFilters($emploisQuery, $request);

        if ($request->filled('secteur_id')) {
            $emploisQuery->where('promoteurs.secteuractivite_id', $request->secteur_id);
        }
        if ($request->filled('sous_secteur_id')) {
            $emploisQuery->where('promoteurs.soussecteuractivite_id', $request->sous_secteur_id);
        }

        $nombre_emplois       = $emploisQuery->count();
        $nombre_beneficiaires = (int) ($stats->nombre_beneficiaires ?? 0);
        $nombre_femmes        = (int) ($stats->nombre_femmes ?? 0);

        $pourcentage_femmes = $nombre_beneficiaires > 0
            ? round(($nombre_femmes / $nombre_beneficiaires) * 100, 2)
            : 0;

        return response()->json([
            'filtres_appliques' => [
                'annee'           => $request->annee,
                'agence_id'       => $request->agence_id,
                'organisme_id'    => $request->organisme_id,
                'guichet_id'      => $request->guichet_id,
                'secteur_id'      => $request->secteur_id,
                'sous_secteur_id' => $request->sous_secteur_id,
                'statut'          => $request->statut,
                'genre'           => $request->genre,
            ],
            'resultats' => [
                'nombre_projets'            => (int) ($stats->nombre_projets ?? 0),
                'nombre_beneficiaires'      => $nombre_beneficiaires,
                'nombre_femmes'             => $nombre_femmes,
                'pourcentage_femmes'        => $pourcentage_femmes,
                'montant_total_financement' => (float) ($stats->montant_total_financement ?? 0),
                'nombre_emplois_crees'      => $nombre_emplois,
            ],
        ]);
    }

    // =========================================================================
    // 9. RAPPORT FINANCIER AGRÉGÉ
    // =========================================================================

    /**
     * GET /api/rapport/financier
     *
     * Filtres : annee, agence_id, organisme_id, guichet_id, statut
     * Source remboursements : table `remboursements` (montant_paye, montant_impaye, montant_echu)
     *   via plan_remboursements → micro_projets
     */
    public function rapportFinancier(Request $request): JsonResponse
    {
        // --- Agrégat financement en SQL (évite de charger toutes les lignes en mémoire) ---
        $finQuery = DB::table('micro_projets')
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->selectRaw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement');

        $this->applyRawFilters($finQuery, $request);

        $financement = $finQuery->first();
        $montant_total_financement = (float) ($financement->montant_total_financement ?? 0);

        // --- Remboursements agrégés via SQL (via plan_remboursements → micro_projets) ---
        $rembQuery = DB::table('remboursements')
            ->join('plan_remboursements', 'remboursements.plan_remboursement_id', '=', 'plan_remboursements.id')
            ->join('micro_projets', 'plan_remboursements.micro_projet_id', '=', 'micro_projets.id')
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->selectRaw('
                COALESCE(SUM(remboursements.montant_paye), 0)   as montant_rembourse,
                COALESCE(SUM(remboursements.montant_impaye), 0) as montant_impaye,
                COALESCE(SUM(remboursements.montant_echu), 0)   as montant_echu
            ');

        $this->applyRawFilters($rembQuery, $request);

        $remb = $rembQuery->first();
        $montant_rembourse = (float) ($remb->montant_rembourse ?? 0);
        $montant_impaye    = (float) ($remb->montant_impaye ?? 0);
        $montant_echu      = (float) ($remb->montant_echu ?? 0);

        $taux_remboursement = $montant_total_financement > 0
            ? round(($montant_rembourse / $montant_total_financement) * 100, 2)
            : 0;
        $taux_impayes = $montant_echu > 0
            ? round(($montant_impaye / $montant_echu) * 100, 2)
            : 0;

        return response()->json([
            'montant_total_financement' => $montant_total_financement,
            'montant_rembourse'         => $montant_rembourse,
            'montant_impaye'            => $montant_impaye,
            'montant_echu'              => $montant_echu,
            'taux_remboursement'        => $taux_remboursement,
            'taux_impayes'              => $taux_impayes,
        ]);
    }

    // =========================================================================
    // 10. DÉCLINAISON DES INDICATEURS DE REMBOURSEMENT
    // =========================================================================

    /**
     * GET /api/rapport/remboursements/declinaison
     *
     * Param obligatoire : dimension = agence | organisme | annee | secteur | genre
     * Filtres optionnels : annee, agence_id, organisme_id, guichet_id, statut
     *
     * Retourne : pour chaque valeur de la dimension, les indicateurs de remboursement.
     */
    public function declinaisonRemboursements(Request $request): JsonResponse
    {
        $dimension          = $request->input('dimension', 'agence');
        $dimensions_valides = ['agence', 'organisme', 'annee', 'secteur', 'genre'];

        if (! in_array($dimension, $dimensions_valides)) {
            return response()->json([
                'message'            => 'Dimension invalide.',
                'dimensions_valides' => $dimensions_valides,
            ], 422);
        }

        // --- Agrégat projets par dimension ---
        $projetsQuery = DB::table('micro_projets')
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id');

        switch ($dimension) {
            case 'agence':
                $projetsQuery
                    ->join('agences_regionales', 'promoteurs.agenceregionale_id', '=', 'agences_regionales.id')
                    ->selectRaw('
                        agences_regionales.nom as label,
                        COUNT(DISTINCT micro_projets.id) as nombre_projets,
                        COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires,
                        COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement
                    ')
                    ->groupBy('agences_regionales.nom');
                break;

            case 'organisme':
                $projetsQuery
                    ->join('organisme_financements', 'micro_projets.organisme_id', '=', 'organisme_financements.id')
                    ->whereNotNull('micro_projets.organisme_id')
                    ->selectRaw('
                        organisme_financements.nom as label,
                        COUNT(DISTINCT micro_projets.id) as nombre_projets,
                        COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires,
                        COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement
                    ')
                    ->groupBy('organisme_financements.nom');
                break;

            case 'annee':
                $projetsQuery
                    ->selectRaw('
                        YEAR(micro_projets.created_at) as label,
                        COUNT(DISTINCT micro_projets.id) as nombre_projets,
                        COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires,
                        COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement
                    ')
                    ->groupByRaw('YEAR(micro_projets.created_at)')
                    ->orderByRaw('YEAR(micro_projets.created_at) DESC');
                break;

            case 'secteur':
                $projetsQuery
                    ->join('secteurs', 'promoteurs.secteuractivite_id', '=', 'secteurs.id')
                    ->whereNotNull('promoteurs.secteuractivite_id')
                    ->selectRaw('
                        secteurs.nom as label,
                        COUNT(DISTINCT micro_projets.id) as nombre_projets,
                        COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires,
                        COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement
                    ')
                    ->groupBy('secteurs.nom');
                break;

            case 'genre':
                $projetsQuery
                    ->selectRaw('
                        COALESCE(sexes.libelle, "Non renseigné") as label,
                        COUNT(DISTINCT micro_projets.id) as nombre_projets,
                        COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires,
                        COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement
                    ')
                    ->groupByRaw('COALESCE(sexes.libelle, "Non renseigné")');
                break;
        }

        $this->applyRawFilters($projetsQuery, $request);
        $rows = $projetsQuery->get();

        // --- Remboursements par dimension (même logique, même filtres) ---
        $rembQuery = DB::table('remboursements')
            ->join('plan_remboursements', 'remboursements.plan_remboursement_id', '=', 'plan_remboursements.id')
            ->join('micro_projets', 'plan_remboursements.micro_projet_id', '=', 'micro_projets.id')
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->leftJoin('sexes', 'promoteurs.sexe_id', '=', 'sexes.id');

        switch ($dimension) {
            case 'agence':
                $rembQuery
                    ->join('agences_regionales', 'promoteurs.agenceregionale_id', '=', 'agences_regionales.id')
                    ->selectRaw('
                        agences_regionales.nom as label,
                        COALESCE(SUM(remboursements.montant_paye), 0)   as montant_rembourse,
                        COALESCE(SUM(remboursements.montant_impaye), 0) as montant_impaye,
                        COALESCE(SUM(remboursements.montant_echu), 0)   as montant_echu
                    ')
                    ->groupBy('agences_regionales.nom');
                break;

            case 'organisme':
                $rembQuery
                    ->join('organisme_financements', 'micro_projets.organisme_id', '=', 'organisme_financements.id')
                    ->whereNotNull('micro_projets.organisme_id')
                    ->selectRaw('
                        organisme_financements.nom as label,
                        COALESCE(SUM(remboursements.montant_paye), 0)   as montant_rembourse,
                        COALESCE(SUM(remboursements.montant_impaye), 0) as montant_impaye,
                        COALESCE(SUM(remboursements.montant_echu), 0)   as montant_echu
                    ')
                    ->groupBy('organisme_financements.nom');
                break;

            case 'annee':
                $rembQuery
                    ->selectRaw('
                        YEAR(micro_projets.created_at) as label,
                        COALESCE(SUM(remboursements.montant_paye), 0)   as montant_rembourse,
                        COALESCE(SUM(remboursements.montant_impaye), 0) as montant_impaye,
                        COALESCE(SUM(remboursements.montant_echu), 0)   as montant_echu
                    ')
                    ->groupByRaw('YEAR(micro_projets.created_at)');
                break;

            case 'secteur':
                $rembQuery
                    ->join('secteurs', 'promoteurs.secteuractivite_id', '=', 'secteurs.id')
                    ->whereNotNull('promoteurs.secteuractivite_id')
                    ->selectRaw('
                        secteurs.nom as label,
                        COALESCE(SUM(remboursements.montant_paye), 0)   as montant_rembourse,
                        COALESCE(SUM(remboursements.montant_impaye), 0) as montant_impaye,
                        COALESCE(SUM(remboursements.montant_echu), 0)   as montant_echu
                    ')
                    ->groupBy('secteurs.nom');
                break;

            case 'genre':
                $rembQuery
                    ->selectRaw('
                        COALESCE(sexes.libelle, "Non renseigné") as label,
                        COALESCE(SUM(remboursements.montant_paye), 0)   as montant_rembourse,
                        COALESCE(SUM(remboursements.montant_impaye), 0) as montant_impaye,
                        COALESCE(SUM(remboursements.montant_echu), 0)   as montant_echu
                    ')
                    ->groupByRaw('COALESCE(sexes.libelle, "Non renseigné")');
                break;
        }

        $this->applyRawFilters($rembQuery, $request);
        $remb_data = $rembQuery->get()->keyBy('label');

        // --- Fusion projets + remboursements par label ---
        $data = $rows->map(function ($row) use ($remb_data) {
            $montant_financement = (float) $row->montant_total_financement;
            $remb                = $remb_data[$row->label] ?? null;

            $montant_rembourse = $remb ? (float) $remb->montant_rembourse : 0;
            $montant_impaye    = $remb ? (float) $remb->montant_impaye : 0;
            $montant_echu      = $remb ? (float) $remb->montant_echu : 0;

            $taux_remboursement = $montant_financement > 0
                ? round(($montant_rembourse / $montant_financement) * 100, 2)
                : 0;
            $taux_impayes = $montant_echu > 0
                ? round(($montant_impaye / $montant_echu) * 100, 2)
                : 0;

            return [
                'label'                     => $row->label,
                'nombre_projets'            => (int) $row->nombre_projets,
                'nombre_beneficiaires'      => (int) $row->nombre_beneficiaires,
                'montant_total_financement' => $montant_financement,
                'montant_rembourse'         => $montant_rembourse,
                'montant_impaye'            => $montant_impaye,
                'taux_remboursement'        => $taux_remboursement,
                'taux_impayes'              => $taux_impayes,
            ];
        });

        return response()->json([
            'dimension' => $dimension,
            'data'      => $data,
            'total'     => [
                'nombre_projets'            => $data->sum('nombre_projets'),
                'nombre_beneficiaires'      => $data->sum('nombre_beneficiaires'),
                'montant_total_financement' => $data->sum('montant_total_financement'),
                'montant_rembourse'         => $data->sum('montant_rembourse'),
                'montant_impaye'            => $data->sum('montant_impaye'),
            ],
        ]);
    }

    // =========================================================================
    // HELPERS PRIVÉS
    // =========================================================================

    /**
     * Applique les filtres communs sur une query DB::table (stdClass).
     * Identique à applyFilters mais utilisable sur les queries DB::table()
     * qui ne passent pas par Eloquent.
     */
    private function applyRawFilters($query, Request $request): void
    {
        if ($request->filled('annee')) {
            $query->whereYear('micro_projets.created_at', $request->annee);
        }
        if ($request->filled('agence_id')) {
            $query->where('promoteurs.agenceregionale_id', $request->agence_id);
        }
        if ($request->filled('organisme_id')) {
            $query->where('micro_projets.organisme_id', $request->organisme_id);
        }
        if ($request->filled('guichet_id')) {
            $query->where('micro_projets.guichet_id', $request->guichet_id);
        }
        if ($request->filled('statut')) {
            $query->where('micro_projets.statut', $request->statut);
        }
        if ($request->filled('genre')) {
            $genre = strtolower($request->genre);
            if ($genre === 'femme') {
                $query->where(function ($q) {
                    $q->where(DB::raw('LOWER(sexes.libelle)'), 'like', '%femme%')
                      ->orWhere(DB::raw('LOWER(sexes.libelle)'), '=', 'f');
                });
            } elseif ($genre === 'homme') {
                $query->where(function ($q) {
                    $q->where(DB::raw('LOWER(sexes.libelle)'), 'not like', '%femme%')
                      ->where(DB::raw('LOWER(sexes.libelle)'), '!=', 'f');
                });
            }
        }
    }
}
