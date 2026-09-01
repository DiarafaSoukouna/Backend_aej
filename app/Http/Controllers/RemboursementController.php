<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Remboursement;

class RemboursementController extends Controller
{
    public function index(): JsonResponse
    {
        $remboursements = Remboursement::with(['planRemboursement', 'promoteur'])->get();
        return new JsonResponse(['Message' => 'Remboursement list retrieved successfully', 'data' => $remboursements], 200);
    }

    public function show($id): JsonResponse
    {
        $remboursement = Remboursement::with(['planRemboursement', 'promoteur'])->find($id);
        if (!$remboursement) {
            return new JsonResponse(['Message' => 'Remboursement not found'], 404);
        }
        return new JsonResponse(['Message' => 'Remboursement retrieved successfully', 'data' => $remboursement], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'plan_remboursement_id' => 'nullable|exists:plan_remboursements,id',
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'montant_echu' => 'nullable|numeric',
            'montant_paye' => 'nullable|numeric',
            'montant_impaye' => 'nullable|numeric',
            'penalites' => 'nullable|numeric',
            'date_paiement' => 'nullable|date',
            'observations' => 'nullable|string',
            'statut' => 'nullable|in:EN_ATTENTE,PAYE,PARTIEL,NON_PAYE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $remboursement = Remboursement::create($validation->validated());

            return new JsonResponse([
                'message' => 'Remboursement created successfully',
                'data' => $remboursement
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $remboursement = Remboursement::find($id);
        if (!$remboursement) {
            return new JsonResponse(['Message' => 'Remboursement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'plan_remboursement_id' => 'nullable|exists:plan_remboursements,id',
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'montant_echu' => 'nullable|numeric',
            'montant_paye' => 'nullable|numeric',
            'montant_impaye' => 'nullable|numeric',
            'penalites' => 'nullable|numeric',
            'date_paiement' => 'nullable|date',
            'observations' => 'nullable|string',
            'statut' => 'nullable|in:EN_ATTENTE,PAYE,PARTIEL,NON_PAYE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $remboursement->update($validation->validated());

            return new JsonResponse([
                'message' => 'Remboursement updated successfully',
                'data' => $remboursement
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $remboursement = Remboursement::find($id);
        if (!$remboursement) {
            return new JsonResponse(['Message' => 'Remboursement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'montant_echu' => 'nullable|numeric',
            'montant_paye' => 'nullable|numeric',
            'montant_impaye' => 'nullable|numeric',
            'penalites' => 'nullable|numeric',
            'date_paiement' => 'nullable|date',
            'observations' => 'nullable|string',
            'statut' => 'nullable|in:EN_ATTENTE,PAYE,PARTIEL,NON_PAYE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $remboursement->update(array_filter($validation->validated()));

            return new JsonResponse([
                'message' => 'Remboursement patched successfully',
                'data' => $remboursement
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $remboursement = Remboursement::find($id);
        if (!$remboursement) {
            return new JsonResponse(['Message' => 'Remboursement not found'], 404);
        }

        try {
            $remboursement->delete();
            return new JsonResponse(['Message' => 'Remboursement deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
