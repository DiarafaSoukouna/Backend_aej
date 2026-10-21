<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuiviDecaissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SuiviDecaissementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $suiviDecaissements = SuiviDecaissement::with(['decaissement']);

        if ($request->has('decaissement_id') && !empty($request->decaissement_id))
            $suiviDecaissements = $suiviDecaissements->where('decaissement_id', $request->decaissement_id);

        $suiviDecaissementData = $suiviDecaissements->paginate($request->get('per_page', 20));

        return new JsonResponse([
            'message' => 'Suivi decaissements retrieved successfully',
            'data' => $suiviDecaissementData,
            'pagination' => [
                'current_page' => $suiviDecaissementData->currentPage(),
                'last_page' => $suiviDecaissementData->lastPage(),
                'per_page' => $suiviDecaissementData->perPage(),
                'total' => $suiviDecaissementData->total(),
            ]
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $suiviDecaissement = SuiviDecaissement::with(['decaissement'])->find($id);
        if (!$suiviDecaissement) {
            return new JsonResponse(['Message' => 'Suivi decaissement not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Suivi decaissement retrieved successfully',
            'data' => $suiviDecaissement
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'decaissement_id' => 'required|exists:decaissements,id',
            'montant_decaisse' => 'nullable|numeric',
            'date_decaisse' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $suiviDecaissement = SuiviDecaissement::create($validation->validated());
            return new JsonResponse([
                'message' => 'Suivi decaissement created successfully',
                'data' => $suiviDecaissement
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating suivi decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $suiviDecaissement = SuiviDecaissement::find($id);
        if (!$suiviDecaissement) {
            return new JsonResponse(['Message' => 'Suivi decaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'decaissement_id' => 'nullable|exists:decaissements,id',
            'montant_decaisse' => 'nullable|numeric',
            'date_decaisse' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $suiviDecaissement->update($validation->validated());
            return new JsonResponse([
                'message' => 'Suivi decaissement updated successfully',
                'data' => $suiviDecaissement
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating suivi decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $suiviDecaissement = SuiviDecaissement::find($id);
        if (!$suiviDecaissement) {
            return new JsonResponse(['Message' => 'Suivi decaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'decaissement_id' => 'nullable|exists:decaissements,id',
            'montant_decaisse' => 'nullable|numeric',
            'date_decaisse' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $suiviDecaissement->update(array_filter($validation->validated()));
            return new JsonResponse([
                'message' => 'Suivi decaissement patched successfully',
                'data' => $suiviDecaissement
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching suivi decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $suiviDecaissement = SuiviDecaissement::find($id);
        if (!$suiviDecaissement) {
            return new JsonResponse(['Message' => 'Suivi decaissement not found'], 404);
        }

        try {
            $suiviDecaissement->delete();
            return new JsonResponse([
                'message' => 'Suivi decaissement deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting suivi decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
