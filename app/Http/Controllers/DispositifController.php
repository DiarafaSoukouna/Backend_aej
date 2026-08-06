<?php

namespace App\Http\Controllers;

use App\Models\Dispositif;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DispositifController extends Controller
{
    public function index(): JsonResponse
    {
        $dispositifs = Dispositif::with(['projet'])->get();
        return response()->json(['message' => 'Dispositifs retrieved successfully', 'data' => $dispositifs]);
    }

    public function show($id): JsonResponse
    {
        $dispositif = Dispositif::with(['projet'])->findOrFail($id);
        return response()->json(['message' => 'Dispositif retrieved successfully', 'data' => $dispositif]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:dispositifs,code',
            'projet_id' => 'nullable|exists:projets,id|unique:dispositifs,projet_id',
            'intitule' => 'required|string|max:200',
            'budget_alloue' => 'required|numeric|min:0',
            'nbre_emplois_prevu' => 'nullable|integer|min:0',
            'nbre_beneficiaire_prevu' => 'nullable|integer|min:0',
            'nbre_micro_projet_prevu' => 'nullable|integer|min:0',
        ]);

        $dispositif = Dispositif::create($validated);
        return response()->json(['message' => 'Dispositif created successfully', 'data' => $dispositif], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $dispositif = Dispositif::findOrFail($id);

        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:dispositifs,code,' . $id,
            'projet_id' => 'nullable|exists:projets,id|unique:dispositifs,projet_id,' . $id,
            'intitule' => 'nullable|string|max:200',
            'budget_alloue' => 'nullable|numeric|min:0',
            'nbre_emplois_prevu' => 'nullable|integer|min:0',
            'nbre_beneficiaire_prevu' => 'nullable|integer|min:0',
            'nbre_micro_projet_prevu' => 'nullable|integer|min:0',
        ]);

        $dispositif->update($validated);
        return response()->json(['message' => 'Dispositif updated successfully', 'data' => $dispositif]);
    }

    public function destroy($id): JsonResponse
    {
        $dispositif = Dispositif::findOrFail($id);
        $dispositif->delete();
        return response()->json(['message' => 'Dispositif deleted successfully'], 200);
    }
}
