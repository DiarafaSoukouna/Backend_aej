<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
        $validation = Validator::make($request->all(), [
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

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $entreprise = Entreprise::create($validation->validated());
            return response()->json(['message' => 'Entreprise created successfully', 'data' => $entreprise], 201);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Entreprise creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $entreprise = Entreprise::findOrFail($id);

        $validation = Validator::make($request->all(), [
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

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $entreprise->update($validation->validated());
            return response()->json(['message' => 'Entreprise updated successfully', 'data' => $entreprise]);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Entreprise update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $entreprise = Entreprise::findOrFail($id);
        try {
            $entreprise->delete();
            return response()->json(['message' => 'Entreprise deleted successfully'], 204);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Entreprise deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
