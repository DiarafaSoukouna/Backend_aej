<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\LotTransmission;

class LotTransmissionController extends Controller
{
    public function index(): JsonResponse
    {
        $lots = LotTransmission::with(['organisme'])->get();
        return new JsonResponse(['message' => 'Lots transmission retrieved successfully', 'data' => $lots], 200);
    }

    public function show($id): JsonResponse
    {
        $lot = LotTransmission::with(['organisme'])->find($id);
        if (!$lot) {
            return new JsonResponse(['message' => 'Lot transmission not found'], 404);
        }
        return new JsonResponse(['message' => 'Lot transmission retrieved successfully', 'data' => $lot], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'code' => 'nullable|string|max:50',
            'titre' => 'nullable|string|max:255',
            'fichier_repartition' => 'nullable|string|max:255',
            'fichier_courrier' => 'nullable|string|max:255',
            'reference_courrier' => 'nullable|string|max:100',
            'reference_convention' => 'nullable|string|max:100',
            'date_transmission' => 'nullable|date',
            'taux_recouvrement' => 'nullable|numeric',
            'duree_differee' => 'nullable|integer',
            'duree_remboursement' => 'nullable|integer',
            'dossiers' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $lot = LotTransmission::create($validation->validated());
            return new JsonResponse(['message' => 'Lot transmission created successfully', 'data' => $lot], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating lot transmission', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $lot = LotTransmission::find($id);
        if (!$lot) {
            return new JsonResponse(['message' => 'Lot transmission not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'organisme_id' => 'nullable|exists:organisme_financements,id',
            'code' => 'nullable|string|max:50',
            'titre' => 'nullable|string|max:255',
            'fichier_repartition' => 'nullable|string|max:255',
            'fichier_courrier' => 'nullable|string|max:255',
            'reference_courrier' => 'nullable|string|max:100',
            'reference_convention' => 'nullable|string|max:100',
            'date_transmission' => 'nullable|date',
            'taux_recouvrement' => 'nullable|numeric',
            'duree_differee' => 'nullable|integer',
            'duree_remboursement' => 'nullable|integer',
            'dossiers' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $lot->update($validation->validated());
            return new JsonResponse(['message' => 'Lot transmission updated successfully', 'data' => $lot], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating lot transmission', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $lot = LotTransmission::find($id);
        if (!$lot) {
            return new JsonResponse(['message' => 'Lot transmission not found'], 404);
        }

        try {
            $lot->delete();
            return new JsonResponse(['message' => 'Lot transmission deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting lot transmission', 'error' => $e->getMessage()], 500);
        }
    }
}
