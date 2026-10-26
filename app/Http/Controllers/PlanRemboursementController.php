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
        $query = PlanRemboursement::with(['microProjet', 'budget', 'tableauAmortissements']);

        if ($request->filled('micro_projet_id')) {
            $query = $query->where('micro_projet_id', (int) $request->micro_projet_id);
        }
        if ($request->filled('budget_id')) {
            $query = $query->where('budget_id', (int) $request->budget_id);
        }
        if ($request->filled('decision')) {
            $query = $query->where('decision', $request->decision);
        }

        $plans = $query->paginate($request->get('per_page', 20));

        return new JsonResponse([
            'message' => 'Plans remboursement retrieved successfully',
            'data' => $plans->items(),
            'pagination' => [
                'current_page' => $plans->currentPage(),
                'last_page' => $plans->lastPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
            ],
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $plan = PlanRemboursement::with(['microProjet', 'budget', 'tableauAmortissements'])->find($id);
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
            'date_ouverture' => 'nullable|date',
            'decision' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
            'montant_credit' => 'nullable|numeric',
            'interets' => 'nullable|numeric',
            'duree_pret' => 'nullable|integer',
            'duree_remboursement' => 'nullable|integer',
            'fichier_amortissement' => 'nullable|string',
            'fichier_convention' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $plan = PlanRemboursement::create($validation->validated());

            return new JsonResponse([
                'message' => 'Plan remboursement created successfully',
                'data' => $plan->load(['microProjet', 'budget', 'tableauAmortissements']),
            ], 201);
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
            'date_ouverture' => 'nullable|date',
            'decision' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
            'montant_credit' => 'nullable|numeric',
            'interets' => 'nullable|numeric',
            'duree_pret' => 'nullable|integer',
            'duree_remboursement' => 'nullable|integer',
            'fichier_amortissement' => 'nullable|string',
            'fichier_convention' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $plan->update($validation->validated());

            return new JsonResponse([
                'message' => 'Plan remboursement updated successfully',
                'data' => $plan->fresh(['microProjet', 'budget', 'tableauAmortissements']),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating plan remboursement', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function patch(Request $request, $id): JsonResponse
    {
        $plan = PlanRemboursement::find($id);
        if (!$plan) {
            return new JsonResponse(['message' => 'Plan remboursement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'date_ouverture' => 'nullable|date',
            'decision' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
            'montant_credit' => 'nullable|numeric',
            'interets' => 'nullable|numeric',
            'duree_pret' => 'nullable|integer',
            'duree_remboursement' => 'nullable|integer',
            'fichier_amortissement' => 'nullable|string',
            'fichier_convention' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $plan->update(array_filter($validation->validated()));
            return new JsonResponse(['message' => 'Plan remboursement patched successfully', 'data' => $plan], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error patching plan remboursement', 'error' => $e->getMessage()], 500);
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
