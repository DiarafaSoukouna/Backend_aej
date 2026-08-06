<?php

namespace App\Http\Controllers;

use App\Models\ZoneIntervention;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        $validated = $request->validate([
            'projet_id' => 'required|exists:projets,id',
            'departement_id' => 'nullable|exists:departements,id',
            'adresse' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $zone = ZoneIntervention::create($validated);
        return response()->json(['message' => 'Zone created successfully', 'data' => $zone], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $zone = ZoneIntervention::findOrFail($id);

        $validated = $request->validate([
            'projet_id' => 'nullable|exists:projets,id',
            'departement_id' => 'nullable|exists:departements,id',
            'adresse' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $zone->update($validated);
        return response()->json(['message' => 'Zone updated successfully', 'data' => $zone]);
    }

    public function destroy($id): JsonResponse
    {
        $zone = ZoneIntervention::findOrFail($id);
        $zone->delete();
        return response()->json(['message' => 'Zone deleted successfully'], 204);
    }
}
