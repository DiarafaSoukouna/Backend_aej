<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\ImputationAgence;
use App\Models\AgenceRegionale;
use App\Models\MicroProjet;

class ImputationAgenceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ImputationAgence::with(['agence', 'microProjet']);
        $filters = ['agence_id', 'micro_projet_id', 'statut'];

        foreach ($filters as $filter) {
            if ($request->has($filter)) $query->where($filter, $request->$filter);
        }

        $imputations = $query->paginate($request->get('per_page', 20));
        return new JsonResponse(['message' => 'Imputations retrieved successfully', 'data' => $imputations], 200);
    }

    public function show($id): JsonResponse
    {
        $imputation = ImputationAgence::with(['agence', 'microProjet'])->findOrFail($id);
        return new JsonResponse(['message' => 'Imputation retrieved successfully', 'data' => $imputation], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'agence_id' => 'required|exists:agences_regionales,id',
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'statut' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $imputation = ImputationAgence::create($validation->validated());
            return new JsonResponse(['message' => 'Imputation created successfully', 'data' => $imputation], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating imputation', 'error' => $e->getMessage()], 500);
        }
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'imputations' => 'required|array|min:1',
            'imputations.*.agence_id' => 'required|exists:agences_regionales,id',
            'imputations.*.micro_projet_id' => 'required|exists:micro_projets,id',
            'imputations.*.statut' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $agenceIds = array_filter(array_unique(array_column($request->imputations, 'agence_id')));
            $microProjetIds = array_filter(array_unique(array_column($request->imputations, 'micro_projet_id')));
            $agences = !empty($agenceIds) ? AgenceRegionale::whereIn('id', $agenceIds)->pluck('id', 'id')->toArray() : [];
            $microProjets = !empty($microProjetIds) ? MicroProjet::whereIn('id', $microProjetIds)->pluck('id', 'id')->toArray() : [];

            $existingCombinations = [];
            foreach ($request->imputations as $imputationData) {
                $key = $imputationData['agence_id'] . '_' . $imputationData['micro_projet_id'];
                $existingCombinations[$key] = true;
            }

            $existingImputations = ImputationAgence::whereIn('agence_id', $agenceIds)
                ->whereIn('micro_projet_id', $microProjetIds)
                ->get()
                ->map(function ($item) {
                    return $item->agence_id . '_' . $item->micro_projet_id;
                })
                ->toArray();

            $imputations = [];
            foreach ($request->imputations as $imputationData) {
                $key = $imputationData['agence_id'] . '_' . $imputationData['micro_projet_id'];

                if (in_array($key, $existingImputations)) {
                    ImputationAgence::where('agence_id', $imputationData['agence_id'])
                        ->where('micro_projet_id', $imputationData['micro_projet_id'])
                        ->update($imputationData);
                } else {
                    $imputation = ImputationAgence::create($imputationData);
                    $imputations[] = $imputation;
                }
            }

            return new JsonResponse([
                'message' => 'Imputations created successfully',
                'data' => $imputations,
                'total' => count($imputations)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating imputations', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $imputation = ImputationAgence::findOrFail($id);
        $validation = Validator::make($request->all(), [
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'statut' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $imputation->update($validation->validated());
            return new JsonResponse(['message' => 'Imputation updated successfully', 'data' => $imputation], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating imputation', 'error' => $e->getMessage()], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $imputation = ImputationAgence::findOrFail($id);
        $validation = Validator::make($request->all(), [
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'statut' => 'nullable|in:EN_ATTENTE,APPROUVE,NON_APPROUVE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $imputation->update(array_filter($validation->validated()));
            return new JsonResponse(['message' => 'Imputation patched successfully', 'data' => $imputation], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error patching imputation', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $imputation = ImputationAgence::findOrFail($id);
        try {
            $imputation->delete();
            return new JsonResponse(['message' => 'Imputation deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting imputation', 'error' => $e->getMessage()], 500);
        }
    }
}
