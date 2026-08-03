<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanDecaissement;
use Illuminate\Http\JsonResponse;

class PlanDecaissementController extends Controller
{
    public function index()
    {
        $plans = PlanDecaissement::with(['budget'])->get();
        return new JsonResponse([
            'message' => 'Plans de décaissement retrieved successfully',
            'data' => $plans
        ], 200);
    }

    public function show($id)
    {
        $plan = PlanDecaissement::with(['budget'])->findOrFail($id);
        return new JsonResponse([
            'message' => 'Plan de décaissement retrieved successfully',
            'data' => $plan
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'code' => 'required|string|max:50',
            'intitule' => 'required|string|max:200',
            'montant_planifie' => 'required|numeric',
            'date_prevue' => 'nullable|date',
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
            'budget_id' => 'required|exists:budgets,id',
            'code' => 'required|string|max:50',
            'intitule' => 'required|string|max:200',
            'montant_planifie' => 'required|numeric',
            'date_prevue' => 'nullable|date',
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
