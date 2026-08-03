<?php

namespace App\Http\Controllers;

use App\Models\RemboursementsDeclaration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RemboursementsDeclarationController extends Controller
{
    public function index()
    {
        $declarations = RemboursementsDeclaration::with(['promoteur', 'budget'])->get();
        return new JsonResponse([
            'message' => 'Declarations retrieved successfully',
            'data' => $declarations
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'promoteur_id' => 'required|exists:promoteurs,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'montant_paye' => 'required|numeric',
            'date_paiement' => 'required|date',
            'reference_banque' => 'nullable|string|max:100',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'statut' => 'in:BROUILLON,SOUMIS,TRAITE',
        ]);

        $declaration = RemboursementsDeclaration::create($validated);
        return new JsonResponse([
            'message' => 'Declaration created successfully',
            'data' => $declaration
        ], 201);
    }

    public function show($id)
    {
        $declaration = RemboursementsDeclaration::with(['promoteur', 'budget'])->findOrFail($id);
        return new JsonResponse([
            'message' => 'Declaration retrieved successfully',
            'data' => $declaration
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $declaration = RemboursementsDeclaration::findOrFail($id);
        
        $validated = $request->validate([
            'promoteur_id' => 'sometimes|exists:promoteurs,id',
            'budget_id' => 'nullable|sometimes|exists:budgets,id',
            'montant_paye' => 'sometimes|numeric',
            'date_paiement' => 'sometimes|date',
            'reference_banque' => 'nullable|string|max:100',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'statut' => 'in:BROUILLON,SOUMIS,TRAITE',
        ]);

        $declaration->update($validated);
        return new JsonResponse([
            'message' => 'Declaration updated successfully',
            'data' => $declaration
        ], 200);
    }

    public function destroy($id)
    {
        $declaration = RemboursementsDeclaration::findOrFail($id);
        $declaration->delete();
        return new JsonResponse([
            'message' => 'Declaration deleted successfully'
        ], 200);
    }
}
