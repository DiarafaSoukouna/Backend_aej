<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\TableauAmortissement;

class TableauAmortissementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lignes = TableauAmortissement::with(['planRemboursement'])->get();

        if ($request->filled('plan_remboursement_id')) {
            $lignes = $lignes->where('plan_remboursement_id', (int) $request->plan_remboursement_id);
        }
        if ($request->filled('statut')) {
            $lignes = $lignes->where('statut', $request->statut);
        }
        if ($request->filled('periode')) {
            $lignes = $lignes->where('periode', (int) $request->periode);
        }

        return new JsonResponse([
            'message' => 'Tableau amortissements retrieved successfully',
            'data' => $lignes->values(),
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $ligne = TableauAmortissement::with(['planRemboursement'])->find($id);
        if (!$ligne) {
            return new JsonResponse(['message' => 'Tableau amortissement not found'], 404);
        }

        return new JsonResponse([
            'message' => 'Tableau amortissement retrieved successfully',
            'data' => $ligne,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'plan_remboursement_id' => 'nullable|exists:plan_remboursements,id',
            'periode' => 'nullable|integer',
            'date_echeance' => 'nullable|date',
            'montant_echeance' => 'nullable|numeric',
            'capital_rembourse' => 'nullable|numeric',
            'capital_restant' => 'nullable|numeric',
            'interets' => 'nullable|numeric',
            'amortissement_capital' => 'nullable|numeric',
            'statut' => 'nullable|in:PAYE,PARTIEL,NON_PAYE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $ligne = TableauAmortissement::create($validation->validated());

            return new JsonResponse([
                'message' => 'Tableau amortissement created successfully',
                'data' => $ligne->load('planRemboursement'),
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating tableau amortissement', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $ligne = TableauAmortissement::find($id);
        if (!$ligne) {
            return new JsonResponse(['message' => 'Tableau amortissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'plan_remboursement_id' => 'nullable|exists:plan_remboursements,id',
            'periode' => 'nullable|integer',
            'date_echeance' => 'nullable|date',
            'montant_echeance' => 'nullable|numeric',
            'capital_rembourse' => 'nullable|numeric',
            'capital_restant' => 'nullable|numeric',
            'interets' => 'nullable|numeric',
            'amortissement_capital' => 'nullable|numeric',
            'statut' => 'nullable|in:PAYE,PARTIEL,NON_PAYE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $ligne->update($validation->validated());

            return new JsonResponse([
                'message' => 'Tableau amortissement updated successfully',
                'data' => $ligne->fresh(['planRemboursement']),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating tableau amortissement', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $ligne = TableauAmortissement::find($id);
        if (!$ligne) {
            return new JsonResponse(['message' => 'Tableau amortissement not found'], 404);
        }

        try {
            $ligne->delete();

            return new JsonResponse(['message' => 'Tableau amortissement deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting tableau amortissement', 'error' => $e->getMessage()], 500);
        }
    }
}
