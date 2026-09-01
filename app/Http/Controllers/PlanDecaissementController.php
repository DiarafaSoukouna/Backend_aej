<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanDecaissement;
use Illuminate\Http\JsonResponse;

class PlanDecaissementController extends Controller
{
    public function index()
    {
        $plans = PlanDecaissement::with(['microProjet', 'budget', 'compteFinancement', 'ligneDecaissements'])->get();
        return new JsonResponse([
            'message' => 'Plans de décaissement retrieved successfully',
            'data' => $plans
        ], 200);
    }

    public function show($id)
    {
        $plan = PlanDecaissement::with(['microProjet', 'budget', 'compteFinancement', 'ligneDecaissements'])->findOrFail($id);
        return new JsonResponse([
            'message' => 'Plan de décaissement retrieved successfully',
            'data' => $plan
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'compte_financement_id' => 'nullable|exists:compte_financements,id',
            'montant_planifie' => 'required|numeric',
            'date_prevue' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
        ]);

        $plan = PlanDecaissement::create($validated);
        return new JsonResponse([
            'message' => 'Plan de décaissement created successfully',
            'data' => $plan
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $plan = PlanDecaissement::findOrFail($id);

        $validated = $request->validate([
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'compte_financement_id' => 'nullable|exists:compte_financements,id',
            'montant_planifie' => 'required|numeric',
            'date_prevue' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
        ]);

        $plan->update($validated);
        return new JsonResponse([
            'message' => 'Plan de décaissement updated successfully',
            'data' => $plan
        ], 200);
    }

    public function destroy($id)
    {
        $plan = PlanDecaissement::findOrFail($id);
        $plan->delete();
        return new JsonResponse([
            'message' => 'Plan de décaissement deleted successfully'
        ], 200);
    }
}
