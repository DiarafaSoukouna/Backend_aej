<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompteFinancement;
use Illuminate\Http\JsonResponse;

class CompteFinancementController extends Controller
{
    public function index()
    {
        $comptes = CompteFinancement::with(['organisme', 'microProjet', 'budget'])->get();
        return new JsonResponse([
            'message' => 'Comptes financement retrieved successfully',
            'data' => $comptes
        ], 200);
    }

    public function show($id)
    {
        return CompteFinancement::with(['organisme', 'microProjet', 'budget'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'budget_id' => 'nullable|exists:budgets,id|unique:compte_financements,budget_id',
            'etat_compte' => 'nullable|in:OUVERT,FERME,NON_OUVERT',
            'avis_partenaire' => 'nullable|in:ACCORDE,AJOURNE,REJETE',
            'montant_accorde' => 'nullable|numeric',
            'duree_pret' => 'nullable|integer',
            'duree_remboursement' => 'nullable|integer',
            'taux_interet' => 'nullable|numeric',
            'date_ouverture' => 'nullable|date',
            'lieu_ouverture' => 'nullable|string|max:100',
            'observation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $compte = CompteFinancement::create($validator->validated());
        return new JsonResponse([
            'message' => 'Compte financement created successfully',
            'data' => $compte
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $compte = CompteFinancement::findOrFail($id);

        $validated = $request->validate([
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'budget_id' => 'nullable|exists:budgets,id|unique:compte_financements,budget_id,' . $id,
            'etat_compte' => 'nullable|in:OUVERT,FERME,NON_OUVERT',
            'avis_partenaire' => 'nullable|in:ACCORDE,AJOURNE,REJETE',
            'montant_accorde' => 'nullable|numeric',
            'duree_pret' => 'nullable|integer',
            'duree_remboursement' => 'nullable|integer',
            'taux_interet' => 'nullable|numeric',
            'date_ouverture' => 'nullable|date',
            'lieu_ouverture' => 'nullable|string|max:100',
            'observation' => 'nullable|string',
        ]);

        $compte->update($validated);
        return new JsonResponse([
            'message' => 'Compte financement updated successfully',
            'data' => $compte
        ], 200);
    }

    public function destroy($id)
    {
        $compte = CompteFinancement::findOrFail($id);
        $compte->delete();
        return new JsonResponse([
            'message' => 'Compte financement deleted successfully'
        ], 200);
    }
}
