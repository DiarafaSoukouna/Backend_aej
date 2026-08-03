<?php

namespace App\Http\Controllers;

use App\Models\DecaissementsDeclaration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DecaissementsDeclarationController extends Controller
{
    public function index()
    {
        return DecaissementsDeclaration::with(['plan', 'promoteur'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plan_decaissements,id',
            'promoteur_id' => 'required|exists:promoteurs,id',
            'montant' => 'required|numeric',
            'date_declaree' => 'required|date',
            'reference_banque' => 'nullable|string|max:100',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'statut' => 'in:BROUILLON,SOUMIS,TRAITE',
        ]);

        $declaration = DecaissementsDeclaration::create($validated);
        return new JsonResponse([
            'message' => 'Déclaration de décaissement created successfully',
            'data' => $declaration
        ], 201);
    }

    public function show($id)
    {
        $declaration = DecaissementsDeclaration::with(['plan', 'promoteur'])->findOrFail($id);
        return new JsonResponse([
            'message' => 'Déclaration de décaissement retrieved successfully',
            'data' => $declaration
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $declaration = DecaissementsDeclaration::findOrFail($id);
        
        $validated = $request->validate([
            'plan_id' => 'sometimes|exists:plan_decaissements,id',
            'promoteur_id' => 'sometimes|exists:promoteurs,id',
            'montant' => 'sometimes|numeric',
            'date_declaree' => 'sometimes|date',
            'reference_banque' => 'nullable|string|max:100',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'statut' => 'in:BROUILLON,SOUMIS,TRAITE',
        ]);

        $declaration->update($validated);
        return new JsonResponse([
            'message' => 'Déclaration de décaissement updated successfully',
            'data' => $declaration
        ], 200);
    }

    public function destroy($id)
    {
        $declaration = DecaissementsDeclaration::findOrFail($id);
        $declaration->delete();
        return new JsonResponse([
            'message' => 'Déclaration de décaissement deleted successfully'
        ], 200);
    }
}
