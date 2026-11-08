<?php

namespace App\Http\Controllers;

use App\Models\MicroProjet;
use App\Models\RappelHistorique;
use App\Models\Personnel;
use App\Services\RappelRemboursementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RappelRemboursementController extends Controller
{
    public function __construct(
        protected RappelRemboursementService $rappelService
    ) {}

    /**
     * Liste tous les micro-projets présentant 3 échéances consécutives impayées et échues.
     *
     * GET /api/rappels/eligibles
     */
    public function getEligibles(Request $request): JsonResponse
    {
        try {
            $eligibles = $this->rappelService->getEligibleProjects();

            // Filtrage optionnel par agence ou recherche
            if ($request->has('search') && !empty($request->search)) {
                $term = mb_strtolower($request->search);
                $eligibles = $eligibles->filter(function ($item) use ($term) {
                    return str_contains(mb_strtolower($item['projet_code'] ?? ''), $term)
                        || str_contains(mb_strtolower($item['projet_intitule'] ?? ''), $term)
                        || str_contains(mb_strtolower($item['promoteur_nom'] ?? ''), $term);
                })->values();
            }

            return response()->json([
                'success' => true,
                'message' => 'Projets éligibles aux rappels récupérés avec succès.',
                'total'   => $eligibles->count(),
                'data'    => $eligibles,
            ], 200)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des projets éligibles : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envoie manuellement le rappel officiel au promoteur (Email + PDF + Historique).
     *
     * POST /api/rappels/projets/{id}/envoyer
     */
    public function envoyerRappel(int $id, Request $request): JsonResponse
    {
        $projet = MicroProjet::with(['promoteur', 'planRemboursement'])->find($id);

        if (!$projet) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Micro-projet introuvable.',
            ], 404);
        }

        $force = filter_var($request->input('force', false), FILTER_VALIDATE_BOOLEAN);

        // Récupérer l'utilisateur connecté via Sanctum si disponible
        $user = $request->user();
        $personnel = null;
        if ($user instanceof Personnel) {
            $personnel = $user;
        } elseif ($user && !empty($user->personnel_id)) {
            $personnel = Personnel::find($user->personnel_id);
        }

        $result = $this->rappelService->sendReminder($projet, $personnel, $force);

        $status = $result['success'] ? 200 : 422;

        return new JsonResponse($result, $status);
    }

    /**
     * Récupère l'historique complet des rappels et événements pour un projet.
     *
     * GET /api/rappels/projets/{id}/historique
     */
    public function getHistorique(int $id): JsonResponse
    {
        $projet = MicroProjet::find($id);

        if (!$projet) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Micro-projet introuvable.',
            ], 404);
        }

        $historiques = RappelHistorique::where('micro_projet_id', $id)
            ->with(['personnel:id,nom,prenom,email', 'promoteur:id,nom,prenom,telephone,email'])
            ->orderBy('date_action', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return new JsonResponse([
            'success'   => true,
            'projet'    => [
                'id'                       => $projet->id,
                'code'                     => $projet->code ?? $projet->matricule,
                'intitule'                 => $projet->intitule,
                'compteur_rappels_impayes' => (int)($projet->compteur_rappels_impayes ?? 0),
                'dernier_rappel_at'        => $projet->dernier_rappel_at,
            ],
            'total'     => $historiques->count(),
            'historique'=> $historiques,
        ], 200);
    }

    /**
     * Enregistre un événement de suivi manuel (Promesse de règlement, appel, visite terrain, note, etc.).
     *
     * POST /api/rappels/projets/{id}/historique
     */
    public function storeEvenement(int $id, Request $request): JsonResponse
    {
        $projet = MicroProjet::with('promoteur')->find($id);

        if (!$projet) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Micro-projet introuvable.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'type'            => 'required|string|max:100', // PROMESSE_REGLEMENT, APPEL_TELEPHONIQUE, VISITE_TERRAIN, NOTE, CONTENTIEUX, etc.
            'canal'           => 'nullable|string|max:50',  // TELEPHONE, EMAIL, SUR_SITE, WHATSAPP, SMS, COURRIER, SYSTEME
            'titre'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'statut'          => 'nullable|string|max:50',  // ENVOYE, EFFECTUE, EN_ATTENTE, RESPECTE, NON_RESPECTE, ECHOUE, ANNULE
            'date_action'     => 'nullable|date',
            'date_promesse'   => 'nullable|date',
            'montant_promis'  => 'nullable|numeric|min:0',
            'montant_concerne'=> 'nullable|numeric|min:0',
            'metadata'        => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Validation échouée.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = $request->user();
        $personnelId = null;
        if ($user instanceof Personnel) {
            $personnelId = $user->id;
        } elseif ($user && !empty($user->personnel_id)) {
            $personnelId = $user->personnel_id;
        } elseif ($request->filled('personnel_id')) {
            $personnelId = $request->input('personnel_id');
        }

        $historique = RappelHistorique::create([
            'micro_projet_id'  => $projet->id,
            'promoteur_id'     => $projet->promoteur?->id,
            'personnel_id'     => $personnelId,
            'type'             => strtoupper($validated['type']),
            'canal'            => isset($validated['canal']) ? strtoupper($validated['canal']) : 'SYSTEME',
            'titre'            => $validated['titre'],
            'description'      => $validated['description'] ?? null,
            'statut'           => isset($validated['statut']) ? strtoupper($validated['statut']) : 'EFFECTUE',
            'date_action'      => $validated['date_action'] ?? now(),
            'date_promesse'    => $validated['date_promesse'] ?? null,
            'montant_promis'   => $validated['montant_promis'] ?? null,
            'montant_concerne' => $validated['montant_concerne'] ?? null,
            'metadata'         => $validated['metadata'] ?? null,
        ]);

        // Si l'action est de type CONTENTIEUX, enregistrer également dans la table officielle de recouvrements
        if (strtoupper($validated['type']) === 'CONTENTIEUX' || strtoupper($validated['statut'] ?? '') === 'CONTENTIEUX') {
            \App\Models\Recouvrement::create([
                'micro_projet_id'       => $projet->id,
                'agent_id'              => $personnelId,
                'plan_remboursement_id' => $projet->planRemboursement?->id,
                'type_action'           => 'CONTENTIEUX',
                'date_recouvrement'     => $validated['date_action'] ?? now(),
                'observations'          => $validated['titre'] . (!empty($validated['description']) ? ' — ' . $validated['description'] : ''),
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Événement consigné avec succès dans l\'historique du projet.',
            'data'    => $historique->load('personnel:id,nom,prenom'),
        ], 201);
    }

    /**
     * Génère et télécharge / prévisualise le rapport PDF officiel pour le projet.
     *
     * GET /api/rappels/projets/{id}/pdf
     */
    public function previewPdf(int $id): BinaryFileResponse|JsonResponse
    {
        $projet = MicroProjet::with([
            'promoteur.agenceRegionale',
            'dispositif',
            'organisme',
            'guichet',
            'agence',
            'planRemboursement.tableauAmortissements',
        ])->find($id);

        if (!$projet) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Micro-projet introuvable.',
            ], 404);
        }

        $echeances = $this->rappelService->findThreeConsecutiveOverdueUnpaid($projet);

        if (!$echeances || $echeances->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ce projet ne présente pas 3 échéances consécutives impayées et échues pour générer un rapport de rappel.',
            ], 422);
        }

        $numeroRappel = max(1, (int)($projet->compteur_rappels_impayes ?? 0));

        try {
            $pdfPath = $this->rappelService->generatePdfReport($projet, $echeances, $numeroRappel);

            $filename = sprintf(
                'Rapport_Rappel_%s_n%d.pdf',
                preg_replace('/[^A-Za-z0-9_\-]/', '_', $projet->code ?? (string)$projet->id),
                $numeroRappel
            );

            return response()->file($pdfPath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la génération du PDF : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Statistiques globales sur les impayés et la procédure des rappels.
     *
     * GET /api/rappels/statistiques
     */
    public function getStatistiques(): JsonResponse
    {
        try {
            $eligibles = $this->rappelService->getEligibleProjects();

            $totalEligibles = $eligibles->count();
            $totalMontantImpaye = $eligibles->sum('total_impaye');

            $projetsAuMax = $eligibles->filter(fn($p) => ($p['compteur_rappels'] ?? 0) >= RappelRemboursementService::MAX_RAPPELS)->count();
            $projetsEnCoursDeRappel = $totalEligibles - $projetsAuMax;

            $totalRappelsEnvoyes = RappelHistorique::where('type', 'RAPPEL_AUTOMATIQUE')
                ->where('statut', 'ENVOYE')
                ->count();

            $totalPromesses = RappelHistorique::where('type', 'PROMESSE_REGLEMENT')->count();

            return new JsonResponse([
                'success' => true,
                'data'    => [
                    'projets_en_defaut_3_echeances' => $totalEligibles,
                    'montant_total_impaye_cumule'   => $totalMontantImpaye,
                    'projets_en_cours_de_rappel'    => $projetsEnCoursDeRappel,
                    'projets_atteint_quota_5_max'   => $projetsAuMax,
                    'total_emails_rappels_envoyes'  => $totalRappelsEnvoyes,
                    'total_promesses_consignees'    => $totalPromesses,
                    'max_rappels_autorises'         => RappelRemboursementService::MAX_RAPPELS,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Déclenche manuellement le traitement hebdomadaire en masse.
     *
     * POST /api/rappels/hebdomadaire
     */
    public function traiterRappelsHebdomadaires(Request $request): JsonResponse
    {
        $dryRun = filter_var($request->input('dry_run', false), FILTER_VALIDATE_BOOLEAN);

        try {
            $stats = $this->rappelService->processWeeklyReminders($dryRun);

            return new JsonResponse([
                'success' => true,
                'message' => $dryRun ? 'Simulation du traitement hebdomadaire exécutée.' : 'Traitement hebdomadaire des rappels terminé.',
                'stats'   => $stats,
            ], 200);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de l\'exécution du traitement hebdomadaire : ' . $e->getMessage(),
            ], 500);
        }
    }
}

