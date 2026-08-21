<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Budget;

class BudgetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $budgets = Budget::with(['microProjet', 'validePar', 'planDecaissements', 'remboursements'])->get();

        if ($request->has('micro_projet_id') && !empty($request->micro_projet_id))
            $budgets = $budgets->where('micro_projet_id', $request->micro_projet_id);
        if ($request->has('statut') && !empty($request->statut))
            $budgets = $budgets->where('statut', $request->statut);
        if ($request->has('deblocage') && !empty($request->deblocage))
            $budgets = $budgets->where('deblocage', $request->deblocage);
        if ($request->has('signature_convention') && !empty($request->signature_convention))
            $budgets = $budgets->where('signature_convention', $request->signature_convention);
        if ($request->has('reception_acte_credit') && !empty($request->reception_acte_credit))
            $budgets = $budgets->where('reception_acte_credit', $request->reception_acte_credit);

        return new JsonResponse(['Message' => 'Budget list retrieved successfully', 'data' => $budgets], 200);
    }

    public function show($id): JsonResponse
    {
        $budget = Budget::with(['microProjet', 'validePar', 'planDecaissements', 'remboursements'])->find($id);
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

    public function patch(Request $request, $id): JsonResponse
    {
        $budget = Budget::find($id);
        if (!$budget) {
            return new JsonResponse(['Message' => 'Budget not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'intitule' => 'nullable|string|max:100',
            'montant_accorde' => 'nullable|numeric',
            'date_accord' => 'nullable|date',
            'source' => 'nullable|string|max:100',
            'devise' => 'nullable|string|max:10',
            'statut' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
            'deblocage' => 'nullable|in:OUI,NON',
            'date_deblocage' => 'nullable|date',
            'signature_convention' => 'nullable|in:SIGNEE,NON_SIGNEE',
            'date_signature' => 'nullable|date',
            'reception_acte_credit' => 'nullable|in:OUI,NON,PARTIEL',
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
            $budget->update(array_filter($validation->validated()));

            return new JsonResponse([
                'message' => 'Budget patched successfully',
                'data' => $budget
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching budget',
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
