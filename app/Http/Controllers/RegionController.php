<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Region;

class RegionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $regions = Region::get();

        return new JsonResponse([
            'message' => 'Regions retrieved successfully',
            'data' => $regions
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $region = Region::with(['departements'])->find($id);
        if (!$region) {
            return new JsonResponse(['message' => 'Region not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Region retrieved successfully',
            'data' => $region
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'nullable|string|max:50|unique:regions',
            'nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $region = Region::create($validation->validated());
            return new JsonResponse([
                'message' => 'Region created successfully',
                'data' => $region
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating region',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $region = Region::find($id);
        if (!$region) {
            return new JsonResponse(['message' => 'Region not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|nullable|string|max:50|unique:regions,code,'.$id,
            'nom' => 'sometimes|required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $region->update($validation->validated());
            return new JsonResponse([
                'message' => 'Region updated successfully',
                'data' => $region
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating region',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $region = Region::find($id);
        if (!$region) {
            return new JsonResponse(['message' => 'Region not found'], 404);
        }

        try {
            $region->delete();
            return new JsonResponse([
                'message' => 'Region deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting region',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'regions' => 'required|array|min:1',
            'regions.*.code' => 'nullable|string|max:50|unique:regions',
            'regions.*.nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $regions = [];
            foreach ($request->regions as $regionData) {
                $regions[] = Region::create($regionData);
            }

            return new JsonResponse([
                'message' => 'Regions created successfully',
                'data' => $regions,
                'count' => count($regions)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating regions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}