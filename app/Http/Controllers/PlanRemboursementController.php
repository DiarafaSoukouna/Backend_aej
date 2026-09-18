<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\PlanRemboursement;

class PlanRemboursementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plans = PlanRemboursement::with(['microProjet', 'budget'])->get();

        if ($request->has('micro_projet_id') && !empty($request->micro_projet_id)) 
            $plans = $plans->where('micro_projet_id', $request->micro_projet_id);
        if ($request->has('budget_id') && !empty($request->budget_id)) 
            $plans = $plans->where('budget_id', $request->budget_id);
        
        return new JsonResponse(['message' => 'Plans remboursement retrieved successfully', 'data' => $plans], 200);
    }

    public function show($id): JsonResponse
    {
        $plan = PlanRemboursement::with(['microProjet', 'budget'])->find($id);
        if (!$plan) {
            return new JsonResponse(['message' => 'Plan remboursement not found'], 404);
        }
        return new JsonResponse(['message' => 'Plan remboursement retrieved successfully', 'data' => $plan], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'echeance_mensuelle' => 'nullable|date',
            'montant_echeance' => 'nullable|numeric',
            'periode' => 'nullable|integer',
            'capital_rembourse' => 'nullable|numeric',
            'capital_restant' => 'nullable|numeric',
            'interets' => 'nullable|numeric',
            'amortissement_capital' => 'nullable|numeric',
            'justificatif_path' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $plan = PlanRemboursement::create($validation->validated());
            return new JsonResponse(['message' => 'Plan remboursement created successfully', 'data' => $plan], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating plan remboursement', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $plan = PlanRemboursement::find($id);
        if (!$plan) {
            return new JsonResponse(['message' => 'Plan remboursement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'echeance_mensuelle' => 'nullable|date',
            'montant_echeance' => 'nullable|numeric',
            'periode' => 'nullable|integer',
            'capital_rembourse' => 'nullable|numeric',
            'capital_restant' => 'nullable|numeric',
            'interets' => 'nullable|numeric',
            'amortissement_capital' => 'nullable|numeric',
            'justificatif_path' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $plan->update($validation->validated());
            return new JsonResponse(['message' => 'Plan remboursement updated successfully', 'data' => $plan], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating plan remboursement', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $plan = PlanRemboursement::find($id);
        if (!$plan) {
            return new JsonResponse(['message' => 'Plan remboursement not found'], 404);
        }

        try {
            $plan->delete();
            return new JsonResponse(['message' => 'Plan remboursement deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting plan remboursement', 'error' => $e->getMessage()], 500);
        }
    }
}
