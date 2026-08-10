<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Budget;

class BudgetController extends Controller
{
    public function index(): JsonResponse
    {
        $budgets = Budget::with(['microProjet', 'validePar', 'planDecaissements', 'compteRemboursement', 'remboursements'])->get();
        return new JsonResponse(['Message' => 'Budget list retrieved successfully', 'data' => $budgets], 200);
    }

    public function show($id): JsonResponse
    {
        $budget = Budget::with(['microProjet', 'validePar', 'planDecaissements', 'compteRemboursement', 'remboursements'])->find($id);
        if (!$budget) {
            return new JsonResponse(['Message' => 'Budget not found'], 404);
        }
        return new JsonResponse(['Message' => 'Budget retrieved successfully', 'data' => $budget], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'intitule' => 'required|string|max:100',
            'montant_accorde' => 'required|numeric',
            'date_accord' => 'nullable|date',
            'source' => 'nullable|string|max:100',
            'devise' => 'required|string|max:10',
            'statut' => 'required|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
            'deblocage' => 'required|in:OUI,NON',
            'date_deblocage' => 'nullable|date',
            'signature_convention' => 'required|in:SIGNEE,NON_SIGNEE',
            'date_signature' => 'nullable|date',
            'reception_acte_credit' => 'required|in:OUI,NON,PARTIEL',
            'date_reception' => 'nullable|date',
            'observations' => 'nullable|string',
            'valide_par' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $budget = Budget::create($validation->validated());

            return new JsonResponse([
                'message' => 'Budget created successfully',
                'data' => $budget
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating budget',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $budget = Budget::find($id);
        if (!$budget) {
            return new JsonResponse(['Message' => 'Budget not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'sometimes|required|exists:micro_projets,id',
            'intitule' => 'sometimes|required|string|max:100',
            'montant_accorde' => 'sometimes|required|numeric',
            'date_accord' => 'nullable|date',
            'source' => 'nullable|string|max:100',
            'devise' => 'sometimes|required|string|max:10',
            'statut' => 'sometimes|required|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
            'deblocage' => 'sometimes|required|in:OUI,NON',
            'date_deblocage' => 'nullable|date',
            'signature_convention' => 'sometimes|required|in:SIGNEE,NON_SIGNEE',
            'date_signature' => 'nullable|date',
            'reception_acte_credit' => 'sometimes|required|in:OUI,NON,PARTIEL',
            'date_reception' => 'nullable|date',
            'observations' => 'nullable|string',
            'valide_par' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $budget->update($validation->validated());

            return new JsonResponse([
                'message' => 'Budget updated successfully',
                'data' => $budget
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating budget',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $budget = Budget::find($id);
        if (!$budget) {
            return new JsonResponse(['Message' => 'Budget not found'], 404);
        }

        try {
            $budget->delete();
            return new JsonResponse(['Message' => 'Budget deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting budget',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
