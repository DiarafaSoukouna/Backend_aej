<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Decaissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DecaissementController extends Controller
{
    public function index(): JsonResponse
    {
        $decaissements = Decaissement::with(['planDecaissement', 'ligneDecaissement', 'agence'])->get();
        return new JsonResponse([
            'message' => 'Decaissements retrieved successfully',
            'data' => $decaissements
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $decaissement = Decaissement::with(['planDecaissement', 'ligneDecaissement', 'agence'])->find($id);
        if (!$decaissement) {
            return new JsonResponse(['Message' => 'Decaissement not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Decaissement retrieved successfully',
            'data' => $decaissement
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'plan_decaissement_id' => 'nullable|exists:plan_decaissements,id',
            'ligne_decaissement_id' => 'nullable|exists:ligne_decaissements,id',
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'montant_decaisse' => 'nullable|numeric',
            'date_decaissement' => 'nullable|date',
            'reference_banque' => 'nullable|string',
            'statut' => 'nullable|in:EN_ATTENTE,VALIDE,NON_VALIDE',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $decaissement = Decaissement::create($validation->validated());
            return new JsonResponse([
                'message' => 'Decaissement created successfully',
                'data' => $decaissement
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $decaissement = Decaissement::find($id);
        if (!$decaissement) {
            return new JsonResponse(['Message' => 'Decaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'plan_decaissement_id' => 'nullable|exists:plan_decaissements,id',
            'ligne_decaissement_id' => 'nullable|exists:ligne_decaissements,id',
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'montant_decaisse' => 'nullable|numeric',
            'date_decaissement' => 'nullable|date',
            'reference_banque' => 'nullable|string',
            'statut' => 'nullable|in:EN_ATTENTE,VALIDE,NON_VALIDE',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $decaissement->update($validation->validated());
            return new JsonResponse([
                'message' => 'Decaissement updated successfully',
                'data' => $decaissement
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $decaissement = Decaissement::find($id);
        if (!$decaissement) {
            return new JsonResponse(['Message' => 'Decaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'montant_decaisse' => 'nullable|numeric',
            'date_decaissement' => 'nullable|date',
            'reference_banque' => 'nullable|string',
            'statut' => 'nullable|in:EN_ATTENTE,VALIDE,NON_VALIDE',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $decaissement->update(array_filter($validation->validated()));
            return new JsonResponse([
                'message' => 'Decaissement patched successfully',
                'data' => $decaissement
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $decaissement = Decaissement::find($id);
        if (!$decaissement) {
            return new JsonResponse(['Message' => 'Decaissement not found'], 404);
        }

        try {
            $decaissement->delete();
            return new JsonResponse([
                'message' => 'Decaissement deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
