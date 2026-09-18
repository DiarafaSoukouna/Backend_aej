<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
        $validation = Validator::make($request->all(), [
            'secteur_id' => 'required|exists:secteurs,id',
            'titre' => 'required|string|max:255',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $projet = Projet::create($validation->validated());
            return response()->json(['message' => 'Projet created successfully', 'data' => $projet], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Projet creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $projet = Projet::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'secteur_id' => 'nullable|exists:secteurs,id',
            'titre' => 'nullable|string|max:255',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $projet->update($validation->validated());
            return response()->json(['message' => 'Projet updated successfully', 'data' => $projet]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Projet update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $projet = Projet::findOrFail($id);
        try {
            $projet->delete();
            return response()->json(['message' => 'Projet deleted successfully'], 204);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Projet deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
