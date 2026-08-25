<?php

namespace App\Http\Controllers;

use App\Models\ZoneIntervention;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ZoneInterventionController extends Controller
{
    public function index(): JsonResponse
    {
        $zones = ZoneIntervention::with(['projet', 'departement'])->get();
        return response()->json(['message' => 'Zones retrieved successfully', 'data' => $zones]);
    }

    public function show($id): JsonResponse
    {
        $zone = ZoneIntervention::with(['projet', 'departement'])->findOrFail($id);
        return response()->json(['message' => 'Zone retrieved successfully', 'data' => $zone]);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'projet_id' => 'required|exists:projets,id',
            'departement_id' => 'nullable|exists:departements,id',
            'adresse' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $zone = ZoneIntervention::create($validation->validated());
            return response()->json(['message' => 'Zone created successfully', 'data' => $zone], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Zone creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $zone = ZoneIntervention::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'projet_id' => 'nullable|exists:projets,id',
            'departement_id' => 'nullable|exists:departements,id',
            'adresse' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $zone->update($validation->validated());
            return response()->json(['message' => 'Zone updated successfully', 'data' => $zone]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Zone update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $zone = ZoneIntervention::findOrFail($id);
        
        try {
            $zone->delete();
            return response()->json(['message' => 'Zone deleted successfully'], 204);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Zone deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
