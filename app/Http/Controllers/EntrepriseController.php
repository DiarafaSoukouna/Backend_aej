<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EntrepriseController extends Controller
{
    public function index(): JsonResponse
    {
        $entreprises = Entreprise::with(['typeEntreprise', 'region', 'commune', 'embauches'])->get();
        return response()->json(['message' => 'Entreprises retrieved successfully', 'data' => $entreprises]);
    }

    public function show($id): JsonResponse
    {
        $entreprise = Entreprise::with(['typeEntreprise', 'region', 'commune', 'embauches'])->findOrFail($id);
        return response()->json(['message' => 'Entreprise retrieved successfully', 'data' => $entreprise]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'numero' => 'nullable|string|max:50|unique:entreprises,numero',
            'raison_sociale' => 'required|string|max:200',
            'sigle' => 'nullable|string|max:30',
            'rccm' => 'nullable|string|max:50|unique:entreprises,rccm',
            'ninea' => 'nullable|string|max:50|unique:entreprises,ninea',
            'type_entreprise_id' => 'nullable|exists:type_entreprises,id',
            'adresse' => 'nullable|string',
            'contact' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'region_id' => 'nullable|exists:regions,id',
            'commune_id' => 'nullable|exists:communes,id',
        ]);

        $entreprise = Entreprise::create($validated);
        return response()->json(['message' => 'Entreprise created successfully', 'data' => $entreprise], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $entreprise = Entreprise::findOrFail($id);

        $validated = $request->validate([
            'numero' => 'nullable|string|max:50|unique:entreprises,numero,' . $id,
            'raison_sociale' => 'nullable|string|max:200',
            'sigle' => 'nullable|string|max:30',
            'rccm' => 'nullable|string|max:50|unique:entreprises,rccm,' . $id,
            'ninea' => 'nullable|string|max:50|unique:entreprises,ninea,' . $id,
            'type_entreprise_id' => 'nullable|exists:type_entreprises,id',
            'adresse' => 'nullable|string',
            'contact' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'region_id' => 'nullable|exists:regions,id',
            'commune_id' => 'nullable|exists:communes,id',
        ]);

        $entreprise->update($validated);
        return response()->json(['message' => 'Entreprise updated successfully', 'data' => $entreprise]);
    }

    public function destroy($id): JsonResponse
    {
        $entreprise = Entreprise::findOrFail($id);
        $entreprise->delete();
        return response()->json(['message' => 'Entreprise deleted successfully'], 204);
    }
}
