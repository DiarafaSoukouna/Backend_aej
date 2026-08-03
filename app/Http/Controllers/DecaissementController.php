<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Decaissement;
use Illuminate\Http\JsonResponse;

class DecaissementController extends Controller
{
    public function index()
    {
        $decaissements = Decaissement::with(['plan', 'agence'])->get();
        return new JsonResponse([
            'message' => 'Decaissements retrieved successfully',
            'data' => $decaissements
        ], 200);
    }

    public function show($id)
    {
        $decaissement = Decaissement::with(['plan', 'agence'])->findOrFail($id);
        return new JsonResponse([
            'message' => 'Decaissement retrieved successfully',
            'data' => $decaissement
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'nullable|exists:plan_decaissements,id',
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'montant_decaisse' => 'nullable|numeric',
            'date_decaissement' => 'nullable|date',
            'reference_banque' => 'nullable|string',
            'statut' => 'required|in:EN_ATTENTE,VALIDE,NON_VALIDE',
            'observations' => 'nullable|string',
        ]);

        $decaissement = Decaissement::create($validated);
        return new JsonResponse([
            'message' => 'Decaissement created successfully',
            'data' => $decaissement
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $decaissement = Decaissement::findOrFail($id);
        
        $validated = $request->validate([
            'plan_id' => 'nullable|exists:plan_decaissements,id',
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'montant_decaisse' => 'nullable|numeric',
            'date_decaissement' => 'nullable|date',
            'reference_banque' => 'nullable|string',
            'statut' => 'required|in:EN_ATTENTE,VALIDE,NON_VALIDE',
            'observations' => 'nullable|string',
        ]);

        $decaissement->update($validated);
        return new JsonResponse([
            'message' => 'Decaissement updated successfully',
            'data' => $decaissement
        ], 200);
    }

    public function destroy($id)
    {
        $decaissement = Decaissement::findOrFail($id);
        $decaissement->delete();
        return new JsonResponse([
            'message' => 'Decaissement deleted successfully'
        ], 200);
    }
}
