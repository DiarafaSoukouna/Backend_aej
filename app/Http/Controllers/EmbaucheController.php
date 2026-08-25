<?php

namespace App\Http\Controllers;

use App\Models\Embauche;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
        $validation = Validator::make($request->all(), [
            'promoteur_id' => 'required|exists:promoteurs,id',
            'entreprise_id' => 'nullable|exists:entreprises,id',
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'type_emploi_id' => 'nullable|exists:type_emplois,id',
            'poste' => 'required|string|max:200',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $embauche = Embauche::create($validation->validated());
            return response()->json(['message' => 'Embauche created successfully', 'data' => $embauche], 201);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Embauche creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $embauche = Embauche::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'entreprise_id' => 'nullable|exists:entreprises,id',
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'type_emploi_id' => 'nullable|exists:type_emplois,id',
            'poste' => 'nullable|string|max:200',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $embauche->update($validation->validated());
            return response()->json(['message' => 'Embauche updated successfully', 'data' => $embauche]);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Embauche update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $embauche = Embauche::findOrFail($id);
        try {
            $embauche->delete();
            return response()->json(['message' => 'Embauche deleted successfully'], 204);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Embauche deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
