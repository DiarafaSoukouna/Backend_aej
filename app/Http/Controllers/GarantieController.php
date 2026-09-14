<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Garantie;
use App\Services\MailService;

class GarantieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Garantie::with(['microProjet.promoteur', 'organisme']);

        if ($request->has('micro_projet_id')) {
            $query->where('micro_projet_id', $request->input('micro_projet_id'));
        }

        if ($request->has('organisme_id')) {
            $query->where('organisme_id', $request->input('organisme_id'));
        }

        if ($request->has('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        $garanties = $query->paginate($request->input('per_page', 15));

        return new JsonResponse([
            'message' => 'Garanties récupérées avec succès',
            'data' => $garanties
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'organisme_id' => 'required|exists:organisme_financements,id',
            'montant_garantie' => 'required|numeric|min:0',
            'date_rappel' => 'nullable|date',
            'statut' => 'nullable|in:EN_ATTENTE,PAYE,PARTIEL,NON_PAYE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $garantie = Garantie::create([
                'micro_projet_id' => $request->input('micro_projet_id'),
                'organisme_id' => $request->input('organisme_id'),
                'montant_garantie' => $request->input('montant_garantie'),
                'date_rappel' => $request->input('date_rappel'),
                'statut' => $request->input('statut', 'EN_ATTENTE'),
            ]);

            $garantie->load(['microProjet.promoteur', 'organisme']);

            // Envoyer email de rappel au promoteur
            if ($garantie->microProjet && $garantie->microProjet->promoteur && $garantie->microProjet->promoteur->email) {
                try {
                    $mailService = new MailService();
                    $mailService->sendGarantieRappelEmail($garantie->microProjet->promoteur->email, [
                        'montant' => $garantie->montant_garantie,
                        'date_rappel' => $garantie->date_rappel,
                        'statut' => $garantie->statut,
                        'micro_projet_code' => $garantie->microProjet->code,
                        'organisme' => $garantie->organisme->nom ?? 'Non spécifié',
                    ]);
                } catch (\Exception $mailException) {
                    Log::error('Erreur lors de l\'envoi de l\'email de rappel de garantie', [
                        'error' => $mailException->getMessage(),
                        'garantie_id' => $garantie->id
                    ]);
                }
            }

            return new JsonResponse([
                'message' => 'Garantie créée avec succès',
                'data' => $garantie
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la création de la garantie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $garantie = Garantie::with(['microProjet.promoteur', 'organisme'])->findOrFail($id);

        return new JsonResponse([
            'message' => 'Garantie récupérée avec succès',
            'data' => $garantie
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $garantie = Garantie::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'montant_garantie' => 'nullable|numeric|min:0',
            'date_rappel' => 'nullable|date',
            'statut' => 'nullable|in:EN_ATTENTE,PAYE,PARTIEL,NON_PAYE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $garantie->update($request->only([
                'micro_projet_id',
                'organisme_id',
                'montant_garantie',
                'date_rappel',
                'statut',
            ]));

            $garantie->load(['microProjet.promoteur', 'organisme']);

            // Envoyer email de rappel au promoteur
            if ($garantie->microProjet && $garantie->microProjet->promoteur && $garantie->microProjet->promoteur->email) {
                try {
                    $mailService = new MailService();
                    $mailService->sendGarantieRappelEmail($garantie->microProjet->promoteur->email, [
                        'montant' => $garantie->montant_garantie,
                        'date_rappel' => $garantie->date_rappel,
                        'statut' => $garantie->statut,
                        'micro_projet_code' => $garantie->microProjet->code,
                        'organisme' => $garantie->organisme->nom ?? 'Non spécifié',
                    ]);
                } catch (\Exception $mailException) {
                    Log::error('Erreur lors de l\'envoi de l\'email de rappel de garantie', [
                        'error' => $mailException->getMessage(),
                        'garantie_id' => $garantie->id
                    ]);
                }
            }

            return new JsonResponse([
                'message' => 'Garantie mise à jour avec succès',
                'data' => $garantie
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la mise à jour de la garantie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $garantie = Garantie::with(['microProjet.promoteur', 'organisme'])->findOrFail($id);

        try {
            $garantie->delete();

            return new JsonResponse([
                'message' => 'Garantie supprimée avec succès'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la suppression de la garantie',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
