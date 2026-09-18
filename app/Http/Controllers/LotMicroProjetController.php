<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\LotMicroProjet;
use App\Models\LotImportation;

class LotMicroProjetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = ['lot_id', 'statut'];
        $query = LotMicroProjet::with(['lot', 'microProjet']);

        foreach ($filters as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        $perPage = $request->get('per_page', 20);
        $lotMicroProjets = $query->paginate($perPage);

        return new JsonResponse([
            'message' => 'Lot micro projets retrieved successfully',
            'data' => $lotMicroProjets->items(),
            'pagination' => [
                'current_page' => $lotMicroProjets->currentPage(),
                'per_page' => $lotMicroProjets->perPage(),
                'total' => $lotMicroProjets->total(),
                'last_page' => $lotMicroProjets->lastPage(),
                'from' => $lotMicroProjets->firstItem(),
                'to' => $lotMicroProjets->lastItem(),
            ],
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $lotMicroProjet = LotMicroProjet::with(['lot', 'microProjet'])->find($id);
        if (!$lotMicroProjet) {
            return new JsonResponse(['message' => 'Lot micro projet not found'], 404);
        }

        return new JsonResponse([
            'message' => 'Lot micro projet retrieved successfully',
            'data' => $lotMicroProjet
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'lot_id' => 'required|exists:lots_transmission,id',
            'micro_projet_ids' => 'required|array',
            'micro_projet_ids.*' => 'exists:micro_projets,id',
            'statut' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $lotId = $request->input('lot_id');
            $microProjetIds = $request->input('micro_projet_ids');
            $statut = $request->input('statut', 'EN_ATTENTE');

            $createdRecords = [];
            foreach ($microProjetIds as $microProjetId) {
                $lotMicroProjet = LotMicroProjet::create([
                    'lot_id' => $lotId,
                    'micro_projet_id' => $microProjetId,
                    'statut' => $statut,
                ]);
                $createdRecords[] = $lotMicroProjet->load(['lot', 'microProjet']);
                LotImportation::where('micro_projet_id', $microProjetId)->delete();
            }

            return new JsonResponse([
                'message' => count($createdRecords) . ' micro projets ajoutés au lot avec succès et supprimés du lot d\'importation',
                'data' => $createdRecords
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating lot micro projets', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $lotMicroProjet = LotMicroProjet::find($id);
        if (!$lotMicroProjet) {
            return new JsonResponse(['message' => 'Lot micro projet not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'lot_id' => 'sometimes|required|exists:lots_transmission,id',
            'micro_projet_id' => 'sometimes|required|exists:micro_projets,id',
            'statut' => 'sometimes|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $lotMicroProjet->update($validation->validated());
            return new JsonResponse(['message' => 'Lot micro projet updated successfully', 'data' => $lotMicroProjet], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating lot micro projet', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $lotMicroProjet = LotMicroProjet::find($id);
        if (!$lotMicroProjet) {
            return new JsonResponse(['message' => 'Lot micro projet not found'], 404);
        }

        try {
            $lotMicroProjet->delete();
            return new JsonResponse(['message' => 'Lot micro projet deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting lot micro projet', 'error' => $e->getMessage()], 500);
        }
    }
}
