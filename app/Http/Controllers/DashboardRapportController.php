<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\AgenceRegionale;
use App\Models\OrganismeFinancement;
use App\Models\Secteur;
use App\Models\SousSecteur;
use App\Models\MicroProjet;
use App\Models\PlanRemboursement;
use Illuminate\Support\Facades\DB;

class DashboardRapportController extends Controller
{
    /**
     * Statistiques par agence régionale :
     * - Nombre de promoteurs rattachés à l'agence
     * - Montant total de financement
     * - Montant total de remboursement
     * - Arriéré de crédit (montant accordé - montant décaissé validé)
     * - Nombre d'emplois créés
     * - Taux d'impayés
     *
     * L'agence est portée par le promoteur (promoteurs.agenceregionale_id)
     * Filtres : annee, agence_id, statut
     */
    public function statParAgence(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'agences_regionales.id as agence_id',
            'agences_regionales.nom as agence',
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_promoteurs'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('agences_regionales', 'promoteurs.agenceregionale_id', '=', 'agences_regionales.id')
            ->groupBy('agences_regionales.id', 'agences_regionales.nom')
            ->orderBy('montant_total_financement', 'desc');

        $this->applyFilters($query, $request);

        $agences = $query->get();
        $emplois_par_agence = collect();
        try {
            $emplois_par_agence = MicroProjet::select(
                'promoteurs.agenceregionale_id as agence_id',
                DB::raw('COUNT(embauches.id) as nombre_emplois_crees')
            )
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->leftJoin('embauches', 'micro_projets.id', '=', 'embauches.micro_projet_id')
                ->groupBy('promoteurs.agenceregionale_id')
                ->get()
                ->keyBy('agence_id');
        } catch (\Exception $e) {
            $emplois_par_agence = collect();
        }

        $decaise_par_agence = collect();
        try {
            $decaise_par_agence = MicroProjet::select(
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
            $decaise_par_agence = collect();
        }

        $rembourse_par_agence = collect();
        try {
            $rembourse_par_agence = MicroProjet::select(
                'promoteurs.agenceregionale_id as agence_id',
                DB::raw('COALESCE(SUM(remboursements.montant_paye), 0) as montant_rembourse'),
                DB::raw('COALESCE(SUM(remboursements.montant_impaye), 0) as montant_impaye')
            )
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->leftJoin('remboursements', 'micro_projets.id', '=', 'remboursements.micro_projet_id')
                ->where('remboursements.statut', 'PAYE')
                ->groupBy('promoteurs.agenceregionale_id')
                ->get()
                ->keyBy('agence_id');
        } catch (\Exception $e) {
            $rembourse_par_agence = collect();
        }

        $total_a_rembourser_par_agence = collect();
        try {
            $total_a_rembourser_par_agence = MicroProjet::select(
                'promoteurs.agenceregionale_id as agence_id',
                DB::raw('COALESCE(SUM(micro_projets.montant_total + COALESCE(plan_remboursements.interets, 0)), 0) as total_a_rembourser')
            )
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->leftJoin('plan_remboursements', 'micro_projets.id', '=', 'plan_remboursements.micro_projet_id')
                ->groupBy('promoteurs.agenceregionale_id')
                ->get()
                ->keyBy('agence_id');
        } catch (\Exception $e) {
            $total_a_rembourser_par_agence = collect();
        }

        $data = $agences->map(function ($agence) use ($decaise_par_agence, $emplois_par_agence, $rembourse_par_agence, $total_a_rembourser_par_agence) {
            $montant_financement = (float) $agence->montant_total_financement;
            $montant_decaisse    = isset($decaise_par_agence[$agence->agence_id])
                ? (float) $decaise_par_agence[$agence->agence_id]->montant_decaisse
                : 0;
            $nombre_emplois_crees = isset($emplois_par_agence[$agence->agence_id])
                ? (int) $emplois_par_agence[$agence->agence_id]->nombre_emplois_crees
                : 0;
            $montant_rembourse = isset($rembourse_par_agence[$agence->agence_id])
                ? (float) $rembourse_par_agence[$agence->agence_id]->montant_rembourse
                : 0;
            $montant_impaye = isset($rembourse_par_agence[$agence->agence_id])
                ? (float) $rembourse_par_agence[$agence->agence_id]->montant_impaye
                : 0;
            $total_a_rembourser = isset($total_a_rembourser_par_agence[$agence->agence_id])
                ? (float) $total_a_rembourser_par_agence[$agence->agence_id]->total_a_rembourser
                : 0;

            $taux_impayes = $total_a_rembourser > 0
                ? round(($montant_impaye / $total_a_rembourser) * 100, 2)
                : 0;

            return [
                'agence_id'                 => $agence->agence_id,
                'agence'                    => $agence->agence,
                'nombre_promoteurs'         => (int) $agence->nombre_promoteurs,
                'montant_total_financement' => $montant_financement,
                'montant_decaisse'          => $montant_decaisse,
                'arriere_credit'            => max(0, $montant_financement - $montant_decaisse),
                'nombre_emplois_crees'      => $nombre_emplois_crees,
                'montant_total_remboursement' => $montant_rembourse,
                'taux_impayes'              => $taux_impayes,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_agences'            => AgenceRegionale::count(),
                'nombre_promoteurs'         => $data->sum('nombre_promoteurs'),
                'montant_total_financement' => $data->sum('montant_total_financement'),
                'montant_decaisse'          => $data->sum('montant_decaisse'),
                'arriere_credit'            => $data->sum('arriere_credit'),
                'nombre_emplois_crees'     => $data->sum('nombre_emplois_crees'),
                'montant_total_remboursement' => $data->sum('montant_total_remboursement'),
                'taux_impayes_moyen'       => $data->avg('taux_impayes'),
            ],
        ]);
    }

    /**
     * Statistiques par organisme de financement :
     * - Nombre de promoteurs (via micro-projets liés à l'organisme)
     * - Nombre de femmes parmi ces promoteurs + pourcentage
     * - Montant total de financement
     * - Montant total à rembourser (financement + intérêts)
     * - Pourcentage du montant par rapport au total global
     * - Nombre de micro-projets
     * - Nombre d'emplois créés
     *
     * Filtres : annee, organisme_id, agence_id, statut
     */
    public function statParOrganisme(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'organisme_financements.id as organisme_id',
            'organisme_financements.nom as organisme',
            'organisme_financements.sigle',
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_promoteurs'),
            DB::raw('COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes'),
            DB::raw('COUNT(micro_projets.id) as nombre_micro_projets'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('organisme_financements', 'micro_projets.organisme_id', '=', 'organisme_financements.id')
            ->join('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->whereNotNull('micro_projets.organisme_id')
            ->groupBy('organisme_financements.id', 'organisme_financements.nom', 'organisme_financements.sigle')
            ->orderBy('montant_total_financement', 'desc');

        $this->applyFilters($query, $request);

        if ($request->has('organisme_id')) {
            $query->where('micro_projets.organisme_id', $request->organisme_id);
        }

        $organismes = $query->get();
        $emplois_par_organisme = collect();
        try {
            $emplois_par_organisme = MicroProjet::select(
                'micro_projets.organisme_id',
                DB::raw('COUNT(embauches.id) as nombre_emplois_crees')
            )
                ->leftJoin('embauches', 'micro_projets.id', '=', 'embauches.micro_projet_id')
                ->whereNotNull('micro_projets.organisme_id')
                ->groupBy('micro_projets.organisme_id')
                ->get()
                ->keyBy('organisme_id');
        } catch (\Exception $e) {
            $emplois_par_organisme = collect();
        }

        $montant_global = $organismes->sum('montant_total_financement');
        $interets_par_organisme = collect();
        try {
            $interets_par_organisme = PlanRemboursement::select(
                'micro_projets.organisme_id',
                DB::raw('COALESCE(SUM(plan_remboursements.interets), 0) as total_interets')
            )
                ->join('micro_projets', 'plan_remboursements.micro_projet_id', '=', 'micro_projets.id')
                ->whereNotNull('micro_projets.organisme_id')
                ->groupBy('micro_projets.organisme_id')
                ->get()
                ->keyBy('organisme_id');
        } catch (\Exception $e) {
            $interets_par_organisme = collect();
        }

        $data = $organismes->map(function ($organisme) use ($montant_global, $interets_par_organisme, $emplois_par_organisme) {
            $montant_financement = (float) $organisme->montant_total_financement;
            $interets            = isset($interets_par_organisme[$organisme->organisme_id])
                ? (float) $interets_par_organisme[$organisme->organisme_id]->total_interets
                : 0;
            $nombre_emplois_crees = isset($emplois_par_organisme[$organisme->organisme_id])
                ? (int) $emplois_par_organisme[$organisme->organisme_id]->nombre_emplois_crees
                : 0;

            $nombre_promoteurs = (int) $organisme->nombre_promoteurs;
            $nombre_femmes     = (int) $organisme->nombre_femmes;
            $nombre_micro_projets = (int) $organisme->nombre_micro_projets;

            $pourcentage_femmes  = $nombre_promoteurs > 0
                ? round(($nombre_femmes / $nombre_promoteurs) * 100, 2)
                : 0;
            $pourcentage_montant = $montant_global > 0
                ? round(($montant_financement / $montant_global) * 100, 2)
                : 0;

            return [
                'organisme_id'               => $organisme->organisme_id,
                'organisme'                  => $organisme->organisme,
                'sigle'                      => $organisme->sigle,
                'nombre_promoteurs'          => $nombre_promoteurs,
                'nombre_femmes'              => $nombre_femmes,
                'pourcentage_femmes'         => $pourcentage_femmes,
                'nombre_micro_projets'       => $nombre_micro_projets,
                'nombre_emplois_crees'       => $nombre_emplois_crees,
                'montant_total_financement'  => $montant_financement,
                'total_interets'             => $interets,
                'montant_total_a_rembourser' => round($montant_financement + $interets, 2),
                'pourcentage_montant'        => $pourcentage_montant,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_organismes'          => OrganismeFinancement::count(),
                'nombre_promoteurs'          => $data->sum('nombre_promoteurs'),
                'nombre_femmes'              => $data->sum('nombre_femmes'),
                'nombre_micro_projets'       => $data->sum('nombre_micro_projets'),
                'nombre_emplois_crees'       => $data->sum('nombre_emplois_crees'),
                'montant_total_financement'  => $data->sum('montant_total_financement'),
                'total_interets'             => $data->sum('total_interets'),
                'montant_total_a_rembourser' => $data->sum('montant_total_a_rembourser'),
            ],
        ]);
    }

    /**
     * Statistiques par secteur d'activité :
     * - Nombre de promoteurs
     * - Nombre de femmes + pourcentage
     * - Montant total de financement
     * - Montant total à rembourser (financement + intérêts)
     * - Pourcentage du montant par rapport au total global
     * - Nombre de projets
     * - Nombre d'emplois créés
     *
     * Le secteur est porté par le promoteur (promoteurs.secteuractivite_id)
     * Filtres : annee, secteur_id, agence_id, statut
     */
    public function statParSecteur(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'secteurs.id as secteur_id',
            'secteurs.nom as secteur',
            'secteurs.libelle as secteur_libelle',
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_promoteurs'),
            DB::raw('COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes'),
            DB::raw('COUNT(micro_projets.id) as nombre_projets'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->join('secteurs', 'promoteurs.secteuractivite_id', '=', 'secteurs.id')
            ->whereNotNull('promoteurs.secteuractivite_id')
            ->groupBy('secteurs.id', 'secteurs.nom', 'secteurs.libelle')
            ->orderBy('montant_total_financement', 'desc');

        $this->applyFilters($query, $request);

        if ($request->has('secteur_id')) {
            $query->where('promoteurs.secteuractivite_id', $request->secteur_id);
        }

        $secteurs = $query->get();
        $emplois_par_secteur = collect();
        try {
            $emplois_par_secteur = MicroProjet::select(
                'promoteurs.secteuractivite_id as secteur_id',
                DB::raw('COUNT(embauches.id) as nombre_emplois_crees')
            )
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->leftJoin('embauches', 'micro_projets.id', '=', 'embauches.micro_projet_id')
                ->whereNotNull('promoteurs.secteuractivite_id')
                ->groupBy('promoteurs.secteuractivite_id')
                ->get()
                ->keyBy('secteur_id');
        } catch (\Exception $e) {
            $emplois_par_secteur = collect();
        }

        $montant_global = $secteurs->sum('montant_total_financement');
        $interets_par_secteur = collect();
        try {
            $interets_par_secteur = PlanRemboursement::select(
                'promoteurs.secteuractivite_id as secteur_id',
                DB::raw('COALESCE(SUM(plan_remboursements.interets), 0) as total_interets')
            )
                ->join('micro_projets', 'plan_remboursements.micro_projet_id', '=', 'micro_projets.id')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->whereNotNull('promoteurs.secteuractivite_id')
                ->groupBy('promoteurs.secteuractivite_id')
                ->get()
                ->keyBy('secteur_id');
        } catch (\Exception $e) {
            $interets_par_secteur = collect();
        }

        $data = $secteurs->map(function ($secteur) use ($montant_global, $interets_par_secteur, $emplois_par_secteur) {
            $montant_financement = (float) $secteur->montant_total_financement;
            $interets            = isset($interets_par_secteur[$secteur->secteur_id])
                ? (float) $interets_par_secteur[$secteur->secteur_id]->total_interets
                : 0;
            $nombre_emplois_crees = isset($emplois_par_secteur[$secteur->secteur_id])
                ? (int) $emplois_par_secteur[$secteur->secteur_id]->nombre_emplois_crees
                : 0;

            $nombre_promoteurs = (int) $secteur->nombre_promoteurs;
            $nombre_femmes     = (int) $secteur->nombre_femmes;
            $nombre_projets    = (int) $secteur->nombre_projets;

            $pourcentage_femmes  = $nombre_promoteurs > 0
                ? round(($nombre_femmes / $nombre_promoteurs) * 100, 2)
                : 0;
            $pourcentage_montant = $montant_global > 0
                ? round(($montant_financement / $montant_global) * 100, 2)
                : 0;

            return [
                'secteur_id'                 => $secteur->secteur_id,
                'secteur'                    => $secteur->secteur,
                'secteur_libelle'            => $secteur->secteur_libelle,
                'nombre_promoteurs'          => $nombre_promoteurs,
                'nombre_femmes'              => $nombre_femmes,
                'pourcentage_femmes'         => $pourcentage_femmes,
                'nombre_projets'             => $nombre_projets,
                'nombre_emplois_crees'       => $nombre_emplois_crees,
                'montant_total_financement'  => $montant_financement,
                'total_interets'             => $interets,
                'montant_total_a_rembourser' => round($montant_financement + $interets, 2),
                'pourcentage_montant'        => $pourcentage_montant,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_secteurs'            => Secteur::count(),
                'nombre_promoteurs'          => $data->sum('nombre_promoteurs'),
                'nombre_femmes'              => $data->sum('nombre_femmes'),
                'nombre_projets'             => $data->sum('nombre_projets'),
                'nombre_emplois_crees'       => $data->sum('nombre_emplois_crees'),
                'montant_total_financement'  => $data->sum('montant_total_financement'),
                'total_interets'             => $data->sum('total_interets'),
                'montant_total_a_rembourser' => $data->sum('montant_total_a_rembourser'),
            ],
        ]);
    }

    /**
     * Statistiques par sous-secteur d'activité :
     * - Nombre de promoteurs
     * - Nombre de femmes + pourcentage
     * - Montant total de financement
     * - Montant total à rembourser (financement + intérêts)
     * - Pourcentage du montant par rapport au total global
     * - Secteur parent inclus dans la réponse
     *
     * Le sous-secteur est porté par le promoteur (promoteurs.soussecteuractivite_id)
     * Filtres : annee, secteur_id, sous_secteur_id, agence_id, statut
     */
    public function statParSousSecteur(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            'sous_secteurs.id as sous_secteur_id',
            'sous_secteurs.libelle as sous_secteur',
            'secteurs.id as secteur_id',
            'secteurs.nom as secteur',
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_promoteurs'),
            DB::raw('COUNT(DISTINCT CASE WHEN LOWER(sexes.libelle) LIKE "%femme%" OR LOWER(sexes.libelle) = "f" THEN micro_projets.promoteur_id END) as nombre_femmes'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_total_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->join('sexes', 'promoteurs.sexe_id', '=', 'sexes.id')
            ->join('sous_secteurs', 'promoteurs.soussecteuractivite_id', '=', 'sous_secteurs.id')
            ->join('secteurs', 'sous_secteurs.secteur_id', '=', 'secteurs.id')
            ->whereNotNull('promoteurs.soussecteuractivite_id')
            ->groupBy('sous_secteurs.id', 'sous_secteurs.libelle', 'secteurs.id', 'secteurs.nom')
            ->orderBy('montant_total_financement', 'desc');

        $this->applyFilters($query, $request);

        if ($request->has('secteur_id')) {
            $query->where('sous_secteurs.secteur_id', $request->secteur_id);
        }
        if ($request->has('sous_secteur_id')) {
            $query->where('sous_secteurs.id', $request->sous_secteur_id);
        }

        $sous_secteurs = $query->get();
        $montant_global = $sous_secteurs->sum('montant_total_financement');
        $interets_par_sous_secteur = collect();
        try {
            $interets_par_sous_secteur = PlanRemboursement::select(
                'promoteurs.soussecteuractivite_id as sous_secteur_id',
                DB::raw('COALESCE(SUM(plan_remboursements.interets), 0) as total_interets')
            )
                ->join('micro_projets', 'plan_remboursements.micro_projet_id', '=', 'micro_projets.id')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->whereNotNull('promoteurs.soussecteuractivite_id')
                ->groupBy('promoteurs.soussecteuractivite_id')
                ->get()
                ->keyBy('sous_secteur_id');
        } catch (\Exception $e) {
            $interets_par_sous_secteur = collect();
        }

        $data = $sous_secteurs->map(function ($ss) use ($montant_global, $interets_par_sous_secteur) {
            $montant_financement = (float) $ss->montant_total_financement;
            $interets            = isset($interets_par_sous_secteur[$ss->sous_secteur_id])
                ? (float) $interets_par_sous_secteur[$ss->sous_secteur_id]->total_interets
                : 0;

            $nombre_promoteurs = (int) $ss->nombre_promoteurs;
            $nombre_femmes     = (int) $ss->nombre_femmes;

            $pourcentage_femmes  = $nombre_promoteurs > 0
                ? round(($nombre_femmes / $nombre_promoteurs) * 100, 2)
                : 0;
            $pourcentage_montant = $montant_global > 0
                ? round(($montant_financement / $montant_global) * 100, 2)
                : 0;

            return [
                'sous_secteur_id'            => $ss->sous_secteur_id,
                'sous_secteur'               => $ss->sous_secteur,
                'secteur_id'                 => $ss->secteur_id,
                'secteur'                    => $ss->secteur,
                'nombre_promoteurs'          => $nombre_promoteurs,
                'nombre_femmes'              => $nombre_femmes,
                'pourcentage_femmes'         => $pourcentage_femmes,
                'montant_total_financement'  => $montant_financement,
                'total_interets'             => $interets,
                'montant_total_a_rembourser' => round($montant_financement + $interets, 2),
                'pourcentage_montant'        => $pourcentage_montant,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_sous_secteurs'       => SousSecteur::count(),
                'nombre_promoteurs'          => $data->sum('nombre_promoteurs'),
                'nombre_femmes'              => $data->sum('nombre_femmes'),
                'montant_total_financement'  => $data->sum('montant_total_financement'),
                'total_interets'             => $data->sum('total_interets'),
                'montant_total_a_rembourser' => $data->sum('montant_total_a_rembourser'),
            ],
        ]);
    }

    /**
     * Synthèse des projets financés par année :
     * - Nombre de projets
     * - Montant de financement
     * - Nombre de bénéficiaires (promoteurs)
     * - Nombre d'emplois créés
     * - Montant total de remboursement
     * - Taux d'impayés
     *
     * Filtres : annee, agence_id, organisme_id, secteur_id, statut
     */
    public function statFinancementParAnnee(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            DB::raw('YEAR(micro_projets.created_at) as annee'),
            DB::raw('COUNT(micro_projets.id) as nombre_projets'),
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
            ->groupBy(DB::raw('YEAR(micro_projets.created_at)'))
            ->orderBy('annee', 'desc');

        $this->applyFilters($query, $request);
        
        if ($request->has('organisme_id')) {
            $query->where('micro_projets.organisme_id', $request->organisme_id);
        }
        if ($request->has('secteur_id')) {
            $query->where('promoteurs.secteuractivite_id', $request->secteur_id);
        }

        $results = $query->get();
        $emplois_par_annee = collect();
        try {
            $emplois_query = MicroProjet::select(
                DB::raw('YEAR(micro_projets.created_at) as annee'),
                DB::raw('COUNT(embauches.id) as nombre_emplois_crees')
            )
                ->leftJoin('embauches', 'micro_projets.id', '=', 'embauches.micro_projet_id')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id');
            
            $this->applyFilters($emplois_query, $request);
            
            if ($request->has('organisme_id')) {
                $emplois_query->where('micro_projets.organisme_id', $request->organisme_id);
            }
            if ($request->has('secteur_id')) {
                $emplois_query->where('promoteurs.secteuractivite_id', $request->secteur_id);
            }
            
            $emplois_par_annee = $emplois_query->groupBy(DB::raw('YEAR(micro_projets.created_at)'))
                ->get()
                ->keyBy('annee');
        } catch (\Exception $e) {
            $emplois_par_annee = collect();
        }

        $rembourse_par_annee = collect();
        try {
            $rembourse_query = MicroProjet::select(
                DB::raw('YEAR(micro_projets.created_at) as annee'),
                DB::raw('COALESCE(SUM(remboursements.montant_paye), 0) as montant_rembourse'),
                DB::raw('COALESCE(SUM(remboursements.montant_impaye), 0) as montant_impaye')
            )
                ->leftJoin('remboursements', 'micro_projets.id', '=', 'remboursements.micro_projet_id')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->where('remboursements.statut', 'PAYE');
            
            $this->applyFilters($rembourse_query, $request);
            
            if ($request->has('organisme_id')) {
                $rembourse_query->where('micro_projets.organisme_id', $request->organisme_id);
            }
            if ($request->has('secteur_id')) {
                $rembourse_query->where('promoteurs.secteuractivite_id', $request->secteur_id);
            }
            
            $rembourse_par_annee = $rembourse_query->groupBy(DB::raw('YEAR(micro_projets.created_at)'))
                ->get()
                ->keyBy('annee');
        } catch (\Exception $e) {
            $rembourse_par_annee = collect();
        }

        $total_a_rembourser_par_annee = collect();
        try {
            $total_query = MicroProjet::select(
                DB::raw('YEAR(micro_projets.created_at) as annee'),
                DB::raw('COALESCE(SUM(micro_projets.montant_total + COALESCE(plan_remboursements.interets, 0)), 0) as total_a_rembourser')
            )
                ->leftJoin('plan_remboursements', 'micro_projets.id', '=', 'plan_remboursements.micro_projet_id')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id');
            
            $this->applyFilters($total_query, $request);
            
            if ($request->has('organisme_id')) {
                $total_query->where('micro_projets.organisme_id', $request->organisme_id);
            }
            if ($request->has('secteur_id')) {
                $total_query->where('promoteurs.secteuractivite_id', $request->secteur_id);
            }
            
            $total_a_rembourser_par_annee = $total_query->groupBy(DB::raw('YEAR(micro_projets.created_at)'))
                ->get()
                ->keyBy('annee');
        } catch (\Exception $e) {
            $total_a_rembourser_par_annee = collect();
        }

        $data = $results->map(function ($result) use ($emplois_par_annee, $rembourse_par_annee, $total_a_rembourser_par_annee) {
            $nombre_emplois_crees = isset($emplois_par_annee[$result->annee])
                ? (int) $emplois_par_annee[$result->annee]->nombre_emplois_crees
                : 0;
            $montant_rembourse = isset($rembourse_par_annee[$result->annee])
                ? (float) $rembourse_par_annee[$result->annee]->montant_rembourse
                : 0;
            $montant_impaye = isset($rembourse_par_annee[$result->annee])
                ? (float) $rembourse_par_annee[$result->annee]->montant_impaye
                : 0;
            $total_a_rembourser = isset($total_a_rembourser_par_annee[$result->annee])
                ? (float) $total_a_rembourser_par_annee[$result->annee]->total_a_rembourser
                : 0;

            $taux_impayes = $total_a_rembourser > 0
                ? round(($montant_impaye / $total_a_rembourser) * 100, 2)
                : 0;

            return [
                'annee'                    => (int) $result->annee,
                'nombre_projets'           => (int) $result->nombre_projets,
                'nombre_beneficiaires'     => (int) $result->nombre_beneficiaires,
                'montant_financement'      => (float) $result->montant_financement,
                'nombre_emplois_crees'     => $nombre_emplois_crees,
                'montant_total_remboursement' => $montant_rembourse,
                'taux_impayes'             => $taux_impayes,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => [
                'nombre_projets'           => $data->sum('nombre_projets'),
                'nombre_beneficiaires'     => $data->sum('nombre_beneficiaires'),
                'montant_financement'      => $data->sum('montant_financement'),
                'nombre_emplois_crees'     => $data->sum('nombre_emplois_crees'),
                'montant_total_remboursement' => $data->sum('montant_total_remboursement'),
                'taux_impayes_moyen'       => $data->avg('taux_impayes'),
            ],
        ]);
    }

    /**
     * Synthèse croisée entre les variables (guichet, partenaire, année, localisation, secteur, genre, etc.) :
     * - Nombre de projets
     * - Montant de financement
     * - Nombre de bénéficiaires (promoteurs)
     * - Nombre d'emplois créés
     * - Montant total de remboursement
     *
     * Filtres disponibles : guichet_id, organisme_id, annee, agence_id, secteur_id, sexe_id, statut
     */
    public function statCroiseeVariables(Request $request): JsonResponse
    {
        $query = MicroProjet::select(
            DB::raw('COUNT(micro_projets.id) as nombre_projets'),
            DB::raw('COUNT(DISTINCT micro_projets.promoteur_id) as nombre_beneficiaires'),
            DB::raw('COALESCE(SUM(micro_projets.montant_total), 0) as montant_financement')
        )
            ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id');

        $this->applyExtendedFilters($query, $request);

        $result = $query->first();
        $nombre_emplois_crees = 0;
        try {
            $emplois_query = MicroProjet::leftJoin('embauches', 'micro_projets.id', '=', 'embauches.micro_projet_id')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id');
            
            $this->applyFilters($emplois_query, $request);

            $nombre_emplois_crees = (int) $emplois_query->count('embauches.id');
        } catch (\Exception $e) {
            // Table might not exist yet, keep 0
        }

        $montant_rembourse = 0;
        $montant_impaye = 0;
        try {
            $rembourse_query = MicroProjet::leftJoin('remboursements', 'micro_projets.id', '=', 'remboursements.micro_projet_id')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id')
                ->where('remboursements.statut', 'PAYE');
            
            $this->applyFilters($rembourse_query, $request);
            
            if ($request->has('guichet_id')) {
                $rembourse_query->where('micro_projets.guichet_id', $request->guichet_id);
            }
            if ($request->has('organisme_id')) {
                $rembourse_query->where('micro_projets.organisme_id', $request->organisme_id);
            }
            if ($request->has('secteur_id')) {
                $rembourse_query->where('promoteurs.secteuractivite_id', $request->secteur_id);
            }
            if ($request->has('sexe_id')) {
                $rembourse_query->where('promoteurs.sexe_id', $request->sexe_id);
            }

            $montant_rembourse = (float) $rembourse_query->sum('remboursements.montant_paye');
            $montant_impaye = (float) $rembourse_query->sum('remboursements.montant_impaye');
        } catch (\Exception $e) {
            // Table might not exist yet, keep 0
        }

        $total_a_rembourser = 0;
        try {
            $total_query = MicroProjet::leftJoin('plan_remboursements', 'micro_projets.id', '=', 'plan_remboursements.micro_projet_id')
                ->join('promoteurs', 'micro_projets.promoteur_id', '=', 'promoteurs.id');
            
            $this->applyFilters($total_query, $request);
            
            if ($request->has('guichet_id')) {
                $total_query->where('micro_projets.guichet_id', $request->guichet_id);
            }
            if ($request->has('organisme_id')) {
                $total_query->where('micro_projets.organisme_id', $request->organisme_id);
            }
            if ($request->has('secteur_id')) {
                $total_query->where('promoteurs.secteuractivite_id', $request->secteur_id);
            }
            if ($request->has('sexe_id')) {
                $total_query->where('promoteurs.sexe_id', $request->sexe_id);
            }

            $total_a_rembourser = (float) $total_query->sum(DB::raw('micro_projets.montant_total + COALESCE(plan_remboursements.interets, 0)'));
        } catch (\Exception $e) {
            // Table might not exist yet, keep 0
        }

        $taux_impayes = $total_a_rembourser > 0
            ? round(($montant_impaye / $total_a_rembourser) * 100, 2)
            : 0;

        $data = [
            'nombre_projets'           => (int) $result->nombre_projets,
            'nombre_beneficiaires'     => (int) $result->nombre_beneficiaires,
            'montant_financement'      => (float) $result->montant_financement,
            'nombre_emplois_crees'     => $nombre_emplois_crees,
            'montant_total_remboursement' => $montant_rembourse,
            'taux_impayes'             => $taux_impayes,
        ];

        return response()->json([
            'data'  => $data,
            'filters' => [
                'guichet_id'     => $request->get('guichet_id'),
                'organisme_id'   => $request->get('organisme_id'),
                'annee'          => $request->get('annee'),
                'agence_id'      => $request->get('agence_id'),
                'secteur_id'     => $request->get('secteur_id'),
                'sexe_id'        => $request->get('sexe_id'),
                'statut'         => $request->get('statut'),
            ],
        ]);
    }

    /**
     * Apply common filters
     * agence_id filtre via promoteurs.agenceregionale_id
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->has('annee')) {
            $query->whereYear('micro_projets.created_at', $request->annee);
        }
        if ($request->has('agence_id')) {
            $query->where('promoteurs.agenceregionale_id', $request->agence_id);
        }
        if ($request->has('statut')) {
            $query->where('micro_projets.statut', $request->statut);
        }
    }

    /**
     * Apply extended filters for cross-variable queries
     */
    private function applyExtendedFilters($query, Request $request): void
    {
        $this->applyFilters($query, $request);
        
        if ($request->has('guichet_id')) {
            $query->where('micro_projets.guichet_id', $request->guichet_id);
        }
        if ($request->has('organisme_id')) {
            $query->where('micro_projets.organisme_id', $request->organisme_id);
        }
        if ($request->has('secteur_id')) {
            $query->where('promoteurs.secteuractivite_id', $request->secteur_id);
        }
        if ($request->has('sexe_id')) {
            $query->where('promoteurs.sexe_id', $request->sexe_id);
        }
    }
}
