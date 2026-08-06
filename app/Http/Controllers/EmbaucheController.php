<?php

namespace App\Http\Controllers;

use App\Models\Embauche;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmbaucheController extends Controller
{
    public function index(): JsonResponse
    {
        $embauches = Embauche::with(['promoteur', 'entreprise', 'microProjet', 'typeEmploi'])->get();
        return response()->json(['message' => 'Embauches retrieved successfully', 'data' => $embauches]);
    }

    public function show($id): JsonResponse
    {
        $embauche = Embauche::with(['promoteur', 'entreprise', 'microProjet', 'typeEmploi'])->findOrFail($id);
        return response()->json(['message' => 'Embauche retrieved successfully', 'data' => $embauche]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'promoteur_id' => 'required|exists:promoteurs,id',
            'entreprise_id' => 'nullable|exists:entreprises,id',
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'type_emploi_id' => 'nullable|exists:type_emplois,id',
            'poste' => 'required|string|max:200',
        ]);

        $embauche = Embauche::create($validated);
        return response()->json(['message' => 'Embauche created successfully', 'data' => $embauche], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $embauche = Embauche::findOrFail($id);

        $validated = $request->validate([
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'entreprise_id' => 'nullable|exists:entreprises,id',
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'type_emploi_id' => 'nullable|exists:type_emplois,id',
            'poste' => 'nullable|string|max:200',
        ]);

        $embauche->update($validated);
        return response()->json(['message' => 'Embauche updated successfully', 'data' => $embauche]);
    }

    public function destroy($id): JsonResponse
    {
        $embauche = Embauche::findOrFail($id);
        $embauche->delete();
        return response()->json(['message' => 'Embauche deleted successfully'], 204);
    }
}
