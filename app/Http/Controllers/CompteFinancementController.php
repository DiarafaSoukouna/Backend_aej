<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompteFinancement;
use Illuminate\Http\JsonResponse;

class CompteFinancementController extends Controller
{
    public function index(Request $request)
    {
        $comptes = CompteFinancement::with(['organisme', 'microProjet', 'budget'])->get();

        if ($request->has('micro_projet_id') && !empty($request->micro_projet_id))
            $comptes = $comptes->where('micro_projet_id', $request->micro_projet_id);
        if ($request->has('organisme_id') && !empty($request->organisme_id))
            $comptes = $comptes->where('organisme_id', $request->organisme_id);
        if ($request->has('budget_id') && !empty($request->budget_id))
            $comptes = $comptes->where('budget_id', $request->budget_id);
        if ($request->has('etat_ouverture') && !empty($request->etat_ouverture))
            $comptes = $comptes->where('etat_ouverture', $request->etat_ouverture);
        if ($request->has('avis_partenaire') && !empty($request->avis_partenaire))
            $comptes = $comptes->where('avis_partenaire', $request->avis_partenaire);

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
            'etat_ouverture' => 'nullable|in:OUVERT,FERME,NON_OUVERT',
            'avis_partenaire' => 'nullable|in:ACCORDE,AJOURNE,REJETE',
            'montant_accorde' => 'nullable|numeric',
            'duree_pret' => 'nullable|integer',
            'duree_remboursement' => 'nullable|integer',
            'taux_interet' => 'nullable|numeric',
            'date_ouverture' => 'nullable|date',
            'lieu_ouverture' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
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
            'etat_ouverture' => 'nullable|in:OUVERT,FERME,NON_OUVERT',
            'avis_partenaire' => 'nullable|in:ACCORDE,AJOURNE,REJETE',
            'montant_accorde' => 'nullable|numeric',
            'duree_pret' => 'nullable|integer',
            'duree_remboursement' => 'nullable|integer',
            'taux_interet' => 'nullable|numeric',
            'date_ouverture' => 'nullable|date',
            'lieu_ouverture' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
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
