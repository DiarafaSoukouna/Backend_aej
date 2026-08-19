<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\LigneDecaissement;

class LigneDecaissementController extends Controller
{
    public function index(): JsonResponse
    {
        $lignes = LigneDecaissement::with(['planDecaissement'])->get();
        return new JsonResponse(['message' => 'Lignes decaissement retrieved successfully', 'data' => $lignes], 200);
    }

    public function show($id): JsonResponse
    {
        $ligne = LigneDecaissement::with(['planDecaissement'])->find($id);
        if (!$ligne) {
            return new JsonResponse(['message' => 'Ligne decaissement not found'], 404);
        }
        return new JsonResponse(['message' => 'Ligne decaissement retrieved successfully', 'data' => $ligne], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'plan_decaissement_id' => 'nullable|exists:plan_decaissements,id',
            'numero_ligne' => 'nullable|integer',
            'object_ligne' => 'nullable|string|max:100',
            'montant_ligne' => 'nullable|numeric',
            'mode_decaisse' => 'nullable|in:CHEQUE,VIREMENT',
            'date_prevue' => 'nullable|date',
            'intitule_prestataire' => 'nullable|string|max:100',
            'numero_compte' => 'nullable|string|max:100',
            'contact' => 'nullable|string|max:100',
            'statut' => 'nullable|in:VALIDE,NON_VALIDE',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $ligne = LigneDecaissement::create($validation->validated());
            return new JsonResponse(['message' => 'Ligne decaissement created successfully', 'data' => $ligne], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating ligne decaissement', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $ligne = LigneDecaissement::find($id);
        if (!$ligne) {
            return new JsonResponse(['message' => 'Ligne decaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'plan_decaissement_id' => 'nullable|exists:plan_decaissements,id',
            'numero_ligne' => 'nullable|integer',
            'object_ligne' => 'nullable|string|max:100',
            'montant_ligne' => 'nullable|numeric',
            'mode_decaisse' => 'nullable|in:CHEQUE,VIREMENT',
            'date_prevue' => 'nullable|date',
            'intitule_prestataire' => 'nullable|string|max:100',
            'numero_compte' => 'nullable|string|max:100',
            'contact' => 'nullable|string|max:100',
            'statut' => 'nullable|in:VALIDE,NON_VALIDE',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $ligne->update($validation->validated());
            return new JsonResponse(['message' => 'Ligne decaissement updated successfully', 'data' => $ligne], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating ligne decaissement', 'error' => $e->getMessage()], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $ligne = LigneDecaissement::find($id);
        if (!$ligne) {
            return new JsonResponse(['message' => 'Ligne decaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'numero_ligne' => 'nullable|integer',
            'object_ligne' => 'nullable|string|max:100',
            'montant_ligne' => 'nullable|numeric',
            'mode_decaisse' => 'nullable|in:CHEQUE,VIREMENT',
            'date_prevue' => 'nullable|date',
            'intitule_prestataire' => 'nullable|string|max:100',
            'numero_compte' => 'nullable|string|max:100',
            'contact' => 'nullable|string|max:100',
            'statut' => 'nullable|in:VALIDE,NON_VALIDE',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $ligne->update(array_filter($validation->validated()));
            return new JsonResponse(['message' => 'Ligne decaissement patched successfully', 'data' => $ligne], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error patching ligne decaissement', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $ligne = LigneDecaissement::find($id);
        if (!$ligne) {
            return new JsonResponse(['message' => 'Ligne decaissement not found'], 404);
        }

        try {
            $ligne->delete();
            return new JsonResponse(['message' => 'Ligne decaissement deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting ligne decaissement', 'error' => $e->getMessage()], 500);
        }
    }
}
