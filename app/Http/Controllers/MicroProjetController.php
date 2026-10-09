<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MicroProjet;
use App\Models\Guichet;
use App\Models\Secteur;
use App\Models\Commune;
use App\Models\AgenceRegionale;
use App\Models\Promoteur;
use App\Models\Dispositif;
use App\Models\OrganismeFinancement;
use App\Models\WorkflowEtape;
use App\Models\WorkflowInstance;
use App\Models\WorkflowVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\MailService;

class MicroProjetController extends Controller
{
    public function index(Request $request)
    {
        $query = MicroProjet::with([
            'dispositif',
            'organisme',
            'guichet',
            'secteur',
            'commune',
            'agence',
            'agenceImputation',
            'promoteur',
            'workflowInstance',
            'budget',
            'compteFinancement',
            'planDecaissement',
            'planRemboursement',
            'lotTransmission',
            'lotMicroProjet'
        ]);

        $filters = [
            'dispositif_id',
            'organisme_id',
            'guichet_id',
            'secteur_id',
            'commune_id',
            'agence_id',
            'agence_imputation_id',
            'promoteur_id',
            'stade_projet',
            'type_projet',
            'statut'
        ];

        foreach ($filters as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        if ($request->filled('current_etape_code')) {
            $query->whereHas('workflowInstance', function ($q) use ($request) {
                $q->where('current_etape_code', $request->input('current_etape_code'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('intitule', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        $microProjets = $query->paginate($perPage);

        return new JsonResponse([
            'message' => 'Micro Projets retrieved successfully',
            'data' => $microProjets->items(),
            'pagination' => [
                'current_page' => $microProjets->currentPage(),
                'per_page' => $microProjets->perPage(),
                'total' => $microProjets->total(),
                'last_page' => $microProjets->lastPage(),
                'from' => $microProjets->firstItem(),
                'to' => $microProjets->lastItem(),
            ],
        ], 200);
    }

    public function show($id)
    {
        $microProjet = MicroProjet::with([
            'dispositif',
            'organisme',
            'guichet',
            'secteur',
            'commune',
            'agence',
            'agenceImputation',
            'promoteur',
            'workflowInstance',
            'budget',
            'compteFinancement',
            'planDecaissement',
            'planRemboursement',
            'recouvrements',
            'garanties',
            'lotTransmission',
            'lotMicroProjet',
            'transactions',
            'exploitations',
            'indicateurs',
            'suivis',
            'embauches',
            'formulaireEvaluation',
            'documents',
            'observations'
        ])->findOrFail($id);

        return new JsonResponse([
            'message' => 'Micro Projet retrieved successfully',
            'data' => $microProjet
        ], 200);
    }

    public function store(Request $request, MailService $mailService): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:micro_projets',
            'intitule' => 'required|string|max:200',
            'matricule' => 'required|string|max:50|unique:micro_projets',
            'description' => 'nullable|string',
            'montant_total' => 'nullable|numeric',
            'dispositif_id' => 'nullable|exists:dispositifs,id',
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'guichet_id' => 'nullable|exists:guichets,id',
            'secteur_id' => 'nullable|exists:secteurs,id',
            'commune_id' => 'nullable|exists:communes,id',
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'agence_imputation_id' => 'nullable|exists:agences_regionales,id',
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'stade_projet' => 'nullable|in:CREATION,DEVELOPPEMENT',
            'type_projet' => 'nullable|in:INDIVIDUEL,COLLECTIF',
            'statut' => 'nullable|in:BROUILLON,EN_SOUMISSION,EN_COURS,EN_ANALYSE,EN_ATTENTE,ANNULE,NON_APPROUVE,APPROUVE,EN_FORMATION,EN_FINANCEMENT,EN_DECAISSEMENT,EN_SUIVI,EN_REMBOURSEMENT,TERMINE',
            'localisation' => 'nullable|string|max:50',
            'geolocalisation' => 'nullable|string',
            'date_certification' => 'nullable|date',
            'date_transmission_partenaire' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $microProjet = MicroProjet::create($validation->validated());

            if ($microProjet->promoteur_id && $microProjet->promoteur) {
                $promoteur = $microProjet->promoteur;
                if ($promoteur->personnel && $promoteur->personnel->email) {
                    $mailService->sendProjetAlertEmail($promoteur->personnel->email, [
                        'intitule' => $microProjet->intitule,
                        'matricule' => $microProjet->matricule,
                        'montant_total' => $microProjet->montant_total,
                    ]);
                }
            }

            $guichetProjet = Guichet::where('code', $microProjet->guichet_code)->first();
            $workflowVersion = WorkflowVersion::where('workflow_code', $guichetProjet->workflow_code)->where('is_default', true)->first();
            $startEtape = WorkflowEtape::where('workflow_version', $workflowVersion->code)->first();
            $workflowInstance = new WorkflowInstance();
            $workflowInstance->micro_projet_id = $microProjet->id;
            $workflowInstance->workflow_version = $workflowVersion->code;
            $workflowInstance->current_etape_code = $startEtape->code;
            $workflowInstance->statut = 'EN_COURS';
            $workflowInstance->save();

            return new JsonResponse([
                'message' => 'Micro Projet created successfully',
                'data' => $microProjet
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating micro projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projets' => 'required|array|min:1',
            'micro_projets.*.code' => 'required|string|max:50',
            'micro_projets.*.intitule' => 'required|string|max:200',
            'micro_projets.*.matricule' => 'required|string|max:50',
            'micro_projets.*.description' => 'nullable|string',
            'micro_projets.*.montant_total' => 'nullable|numeric',
            'micro_projets.*.dispositif_code' => 'nullable|string',
            'micro_projets.*.organisme_sigle' => 'nullable|string',
            'micro_projets.*.guichet_code' => 'nullable|string',
            'micro_projets.*.commune_code' => 'nullable|string',
            'micro_projets.*.agence_code' => 'nullable|string',
            'micro_projets.*.secteur_libelle' => 'nullable|string',
            'micro_projets.*.agence_imputation_code' => 'nullable|string',
            'micro_projets.*.promoteur_email' => 'nullable|string',
            'micro_projets.*.stade_projet' => 'nullable|in:CREATION,DEVELOPPEMENT',
            'micro_projets.*.type_projet' => 'nullable|in:INDIVIDUEL,COLLECTIF',
            'micro_projets.*.statut' => 'nullable|in:BROUILLON,EN_SOUMISSION,EN_COURS,EN_ANALYSE,EN_ATTENTE,ANNULE,NON_APPROUVE,APPROUVE,EN_FORMATION,EN_FINANCEMENT,EN_DECAISSEMENT,EN_SUIVI,EN_REMBOURSEMENT,TERMINE',
            'micro_projets.*.localisation' => 'nullable|string|max:50',
            'micro_projets.*.geolocalisation' => 'nullable|string',
            'micro_projets.*.date_certification' => 'nullable|date',
            'micro_projets.*.date_transmission_partenaire' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $dispositifCodes = array_filter(array_unique(array_column($request->micro_projets, 'dispositif_code')));
            $organismeSigles = array_filter(array_unique(array_column($request->micro_projets, 'organisme_sigle')));
            $guichetCodes = array_filter(array_unique(array_column($request->micro_projets, 'guichet_code')));
            $communeCodes = array_filter(array_unique(array_column($request->micro_projets, 'commune_code')));
            $agenceCodes = array_filter(array_unique(array_column($request->micro_projets, 'agence_code')));
            $secteurLibelles = array_filter(array_unique(array_column($request->micro_projets, 'secteur_libelle')));
            $agenceImputationCodes = array_filter(array_unique(array_column($request->micro_projets, 'agence_imputation_code')));
            $promoteurMatricules = array_filter(array_unique(array_column($request->micro_projets, 'promoteur_email')));

            $dispositifs = Dispositif::whereIn('code', $dispositifCodes)->pluck('id', 'code')->toArray();
            $organismes = OrganismeFinancement::whereIn('sigle', $organismeSigles)->pluck('id', 'sigle')->toArray();
            $guichets = Guichet::whereIn('code', $guichetCodes)->pluck('id', 'code')->toArray();
            $communes = Commune::whereIn('code', $communeCodes)->pluck('id', 'code')->toArray();
            $agences = AgenceRegionale::whereIn('code', $agenceCodes)->pluck('id', 'code')->toArray();
            $secteurLibelles = Secteur::whereIn('libelle', $secteurLibelles)->pluck('id', 'libelle')->toArray();
            $agenceImputations = AgenceRegionale::whereIn('code', $agenceImputationCodes)->pluck('id', 'code')->toArray();
            $promoteurs = Promoteur::whereIn('email', $promoteurMatricules)->pluck('id', 'email')->toArray();
            $existingCodes = MicroProjet::whereIn('code', array_column($request->micro_projets, 'code'))->pluck('id', 'code')->toArray();
            $existingMatricules = MicroProjet::whereIn('matricule', array_column($request->micro_projets, 'matricule'))->pluck('id', 'matricule')->toArray();

            $microProjets = [];
            foreach ($request->micro_projets as $microProjetData) {
                $microProjetData['dispositif_id'] = $dispositifs[$microProjetData['dispositif_code']] ?? null;
                $microProjetData['organisme_id'] = $organismes[$microProjetData['organisme_sigle']] ?? null;
                $microProjetData['guichet_id'] = $guichets[$microProjetData['guichet_code']] ?? null;
                $microProjetData['commune_id'] = $communes[$microProjetData['commune_code']] ?? null;
                $microProjetData['agence_id'] = $agences[$microProjetData['agence_code']] ?? null;
                $microProjetData['secteur_id'] = $secteurLibelles[$microProjetData['secteur_libelle']] ?? null;
                $microProjetData['agence_imputation_id'] = $agenceImputations[$microProjetData['agence_imputation_code']] ?? null;
                $microProjetData['promoteur_id'] = $promoteurs[$microProjetData['promoteur_email']] ?? null;

                unset(
                    $microProjetData['dispositif_code'],
                    $microProjetData['organisme_sigle'],
                    $microProjetData['guichet_code'],
                    $microProjetData['commune_code'],
                    $microProjetData['agence_code'],
                    $microProjetData['secteur_libelle'],
                    $microProjetData['agence_imputation_code'],
                    $microProjetData['promoteur_email']
                );

                if (!empty($microProjetData['code']) && isset($existingCodes[$microProjetData['code']])) {
                    MicroProjet::where('id', $existingCodes[$microProjetData['code']])->update($microProjetData);
                    continue;
                }

                if (!empty($microProjetData['matricule']) && isset($existingMatricules[$microProjetData['matricule']])) {
                    MicroProjet::where('id', $existingMatricules[$microProjetData['matricule']])->update($microProjetData);
                    continue;
                }

                $microProjet = MicroProjet::create($microProjetData);
                $microProjets[] = $microProjet;

                $guichetProjet = Guichet::where('code', $microProjetData['guichet_code'])->first();
                $workflowVersion = WorkflowVersion::where('workflow_code', $guichetProjet->workflow_code)->where('is_default', true)->first();
                $startEtape = WorkflowEtape::where('workflow_version', $workflowVersion->code)->first();
                $workflowInstance = new WorkflowInstance();
                $workflowInstance->micro_projet_id = $microProjet->id;
                $workflowInstance->workflow_version = $workflowVersion->code;
                $workflowInstance->current_etape_code = $startEtape->code;
                $workflowInstance->statut = 'EN_COURS';
                $workflowInstance->save();
            }

            return new JsonResponse([
                'message' => 'Micro Projets created successfully',
                'data' => $microProjets,
                'total' => count($microProjets)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating micro projets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $microProjet = MicroProjet::find($id);
        if (!$microProjet) {
            return new JsonResponse(['message' => 'Micro Projet not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|nullable|string|max:50|unique:micro_projets,code,' . $id,
            'intitule' => 'sometimes|required|string|max:200',
            'matricule' => 'sometimes|nullable|string|max:50|unique:micro_projets,matricule,' . $id,
            'description' => 'nullable|string',
            'montant_total' => 'nullable|numeric',
            'dispositif_id' => 'nullable|exists:dispositifs,id',
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'guichet_id' => 'nullable|exists:guichets,id',
            'secteur_id' => 'nullable|exists:secteurs,id',
            'commune_id' => 'nullable|exists:communes,id',
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'agence_imputation_id' => 'nullable|exists:agences_regionales,id',
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'stade_projet' => 'nullable|in:CREATION,DEVELOPPEMENT',
            'type_projet' => 'nullable|in:INDIVIDUEL,COLLECTIF',
            'statut' => 'nullable|in:BROUILLON,EN_SOUMISSION,EN_COURS,EN_ANALYSE,EN_ATTENTE,ANNULE,NON_APPROUVE,APPROUVE,EN_FORMATION,EN_FINANCEMENT,EN_DECAISSEMENT,EN_SUIVI,EN_REMBOURSEMENT,TERMINE',
            'localisation' => 'nullable|string|max:50',
            'geolocalisation' => 'nullable|string',
            'date_certification' => 'nullable|date',
            'date_transmission_partenaire' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $microProjet->update($validation->validated());

            return new JsonResponse([
                'message' => 'Micro Projet updated successfully',
                'data' => $microProjet
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating micro projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $microProjet = MicroProjet::find($id);
        if (!$microProjet) {
            return new JsonResponse(['message' => 'Micro Projet not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|nullable|string|max:50|unique:micro_projets,code,' . $id,
            'intitule' => 'sometimes|required|string|max:200',
            'matricule' => 'sometimes|nullable|string|max:50|unique:micro_projets,matricule,' . $id,
            'description' => 'nullable|string',
            'montant_total' => 'nullable|numeric',
            'dispositif_id' => 'nullable|exists:dispositifs,id',
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'guichet_id' => 'nullable|exists:guichets,id',
            'secteur_id' => 'nullable|exists:secteurs,id',
            'commune_id' => 'nullable|exists:communes,id',
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'agence_imputation_id' => 'nullable|exists:agences_regionales,id',
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'stade_projet' => 'nullable|in:CREATION,DEVELOPPEMENT',
            'type_projet' => 'nullable|in:INDIVIDUEL,COLLECTIF',
            'statut' => 'nullable|in:BROUILLON,EN_SOUMISSION,EN_COURS,EN_ANALYSE,EN_ATTENTE,ANNULE,NON_APPROUVE,APPROUVE,EN_FORMATION,EN_FINANCEMENT,EN_DECAISSEMENT,EN_SUIVI,EN_REMBOURSEMENT,TERMINE',
            'localisation' => 'nullable|string|max:50',
            'geolocalisation' => 'nullable|string',
            'date_certification' => 'nullable|date',
            'date_transmission_partenaire' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $microProjet->update(array_filter($validation->validated()));
            return new JsonResponse([
                'message' => 'Micro Projet patched successfully',
                'data' => $microProjet
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching micro projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $microProjet = MicroProjet::find($id);
        if (!$microProjet) {
            return new JsonResponse(['message' => 'Micro Projet not found'], 404);
        }

        try {
            $microProjet->delete();
            return new JsonResponse(['message' => 'Micro Projet deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting micro projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
