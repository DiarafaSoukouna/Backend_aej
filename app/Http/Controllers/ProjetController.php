<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjetController extends Controller
{
    public function index(): JsonResponse
    {
        $projets = Projet::with(['secteur', 'zonesIntervention', 'dispositifs'])->get();
        return response()->json(['message' => 'Projets retrieved successfully', 'data' => $projets]);
    }

    public function show($id): JsonResponse
    {
        $projet = Projet::with(['secteur', 'zonesIntervention', 'dispositifs'])->findOrFail($id);
        return response()->json(['message' => 'Projet retrieved successfully', 'data' => $projet]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'secteur_id' => 'required|exists:secteurs,id',
            'titre' => 'required|string|max:255',
        ]);

        $projet = Projet::create($validated);
        return response()->json(['message' => 'Projet created successfully', 'data' => $projet], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $projet = Projet::findOrFail($id);

        $validated = $request->validate([
            'secteur_id' => 'nullable|exists:secteurs,id',
            'titre' => 'nullable|string|max:255',
        ]);

        $projet->update($validated);
        return response()->json(['message' => 'Projet updated successfully', 'data' => $projet]);
    }

    public function destroy($id): JsonResponse
    {
        $projet = Projet::findOrFail($id);
        $projet->delete();
        return response()->json(['message' => 'Projet deleted successfully'], 204);
    }
}
