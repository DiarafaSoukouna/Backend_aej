<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompteFinancement;
use Illuminate\Http\JsonResponse;

class CompteFinancementController extends Controller
{
    public function index()
    {
        $comptes = CompteFinancement::with(['organisme', 'microProjet'])->get();
        return new JsonResponse([
            'message' => 'Comptes financement retrieved successfully',
            'data' => $comptes
        ], 200);
    }

    public function show($id)
    {
        return CompteFinancement::with(['organisme', 'microProjet'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'etat_ouverture' => 'required|in:OUVERT,FERME,NON_OUVERT',
            'localite_ouverture' => 'nullable|string|max:100',
            'date_ouverture' => 'nullable|date',
            'avis_partenaire' => 'nullable|in:ACCORDE,AJOURNE,REJETE',
            'observation' => 'nullable|string',
        ]);

        $compte = CompteFinancement::create($validated);
        return new JsonResponse([
            'message' => 'Compte financement created successfully',
            'data' => $compte
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $compte = CompteFinancement::findOrFail($id);

        $validated = $request->validate([
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'etat_ouverture' => 'required|in:OUVERT,FERME,NON_OUVERT',
            'localite_ouverture' => 'nullable|string|max:100',
            'date_ouverture' => 'nullable|date',
            'avis_partenaire' => 'nullable|in:ACCORDE,AJOURNE,REJETE',
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
