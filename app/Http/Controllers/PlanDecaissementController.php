<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanDecaissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PlanDecaissementController extends Controller
{
    public function index(Request $request)
    {
        $plans = PlanDecaissement::with(['microProjet', 'budget', 'compteFinancement', 'ligneDecaissements'])->get();

        if ($request->has('micro_projet_id') && !empty($request->micro_projet_id))
            $plans = $plans->where('micro_projet_id', $request->micro_projet_id);
        if ($request->has('compte_financement_id') && !empty($request->compte_financement_id))
            $plans = $plans->where('compte_financement_id', $request->compte_financement_id);
        if ($request->has('budget_id') && !empty($request->budget_id))
            $plans = $plans->where('budget_id', $request->budget_id);
        if ($request->has('statut') && !empty($request->statut))
            $plans = $plans->where('statut', $request->statut);

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
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'compte_financement_id' => 'nullable|exists:compte_financements,id',
            'montant_planifie' => 'required|numeric',
            'date_prevue' => 'nullable|date',
            'statut' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
            'justificatif_path' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $plan = PlanDecaissement::create($validation->validated());
            return new JsonResponse([
                'message' => 'Plan de décaissement created successfully',
                'data' => $plan
            ], 201);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Plan de décaissement creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $plan = PlanDecaissement::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'compte_financement_id' => 'nullable|exists:compte_financements,id',
            'montant_planifie' => 'nullable|numeric',
            'date_prevue' => 'nullable|date',
            'statut' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
            'justificatif_path' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $plan->update($validation->validated());
            return new JsonResponse([
                'message' => 'Plan de décaissement updated successfully',
                'data' => $plan
            ], 200);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Plan de décaissement update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $ligne = PlanDecaissement::find($id);
        if (!$ligne) {
            return new JsonResponse(['message' => 'Plan decaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'compte_financement_id' => 'nullable|exists:compte_financements,id',
            'montant_planifie' => 'nullable|numeric',
            'date_prevue' => 'nullable|date',
            'statut' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
            'justificatif_path' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $ligne->update(array_filter($validation->validated()));
            return new JsonResponse(['message' => 'Plan decaissement patched successfully', 'data' => $ligne], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error patching plan decaissement', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $plan = PlanDecaissement::findOrFail($id);
        try {
            $plan->delete();
            return new JsonResponse([
                'message' => 'Plan de décaissement deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Plan de décaissement deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
