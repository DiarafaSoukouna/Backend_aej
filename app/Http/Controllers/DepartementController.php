<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Departement;
use App\Models\Region;

class DepartementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $departements = Departement::with(['region'])->get();

        if ($request->has('region_id') && !empty($request->region_id))
            $departements = $departements->where('region_id', $request->region_id);

        return new JsonResponse([
            'message' => 'Departements retrieved successfully',
            'data' => $departements
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $departement = Departement::with(['region', 'communes'])->find($id);
        if (!$departement) {
            return new JsonResponse(['message' => 'Departement not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Departement retrieved successfully',
            'data' => $departement
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'region_id' => 'required|exists:regions,id',
            'code' => 'nullable|string|max:50|unique:departements',
            'nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $departement = Departement::create($validation->validated());
            return new JsonResponse([
                'message' => 'Departement created successfully',
                'data' => $departement
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating departement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $departement = Departement::find($id);
        if (!$departement) {
            return new JsonResponse(['message' => 'Departement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'region_id' => 'sometimes|required|exists:regions,id',
            'code' => 'sometimes|nullable|string|max:50|unique:departements,code,'.$id,
            'nom' => 'sometimes|required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $departement->update($validation->validated());
            return new JsonResponse([
                'message' => 'Departement updated successfully',
                'data' => $departement
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating departement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $departement = Departement::find($id);
        if (!$departement) {
            return new JsonResponse(['message' => 'Departement not found'], 404);
        }

        try {
            $departement->delete();
            return new JsonResponse([
                'message' => 'Departement deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting departement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'departements' => 'required|array|min:1',
            'departements.*.region_code' => 'required|string',
            'departements.*.code' => 'nullable|string|max:50|unique:departements',
            'departements.*.nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $departements = [];
            foreach ($request->departements as $departementData) {
                $region = Region::where('code', $departementData['region_code'])->first();
                if (!$region) {
                    return new JsonResponse([
                        'message' => 'Region not found for code: ' . $departementData['region_code']
                    ], 404);
                }

                $departementData['region_id'] = $region->id;
                unset($departementData['region_code']);
                $departements[] = Departement::create($departementData);
            }

            return new JsonResponse([
                'message' => 'Departements created successfully',
                'data' => $departements,
                'count' => count($departements)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating departements',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}