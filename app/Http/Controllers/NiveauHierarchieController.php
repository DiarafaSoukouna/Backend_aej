<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\NiveauHierarchie;

class NiveauHierarchieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $niveaux = NiveauHierarchie::with(['structures'])->get();

        if ($request->has('niveau') && !empty($request->niveau))
            $niveaux = $niveaux->where('niveau', $request->niveau);

        return new JsonResponse([
            'message' => 'Niveau hierarchie retrieved successfully',
            'data' => $niveaux
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $niveau = NiveauHierarchie::with(['parent', 'children', 'structures'])->find($id);
        if (!$niveau) {
            return new JsonResponse(['message' => 'Niveau hierarchie not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Niveau hierarchie retrieved successfully',
            'data' => $niveau
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'libelle' => 'required|string|max:150',
            'niveau' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $niveau = NiveauHierarchie::create($validation->validated());
            return new JsonResponse([
                'message' => 'Niveau hierarchie created successfully',
                'data' => $niveau
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating niveau hierarchie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $niveau = NiveauHierarchie::find($id);
        if (!$niveau) {
            return new JsonResponse(['message' => 'Niveau hierarchie not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'libelle' => 'sometimes|required|string|max:150',
            'niveau' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $niveau->update($validation->validated());
            return new JsonResponse([
                'message' => 'Niveau hierarchie updated successfully',
                'data' => $niveau
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating niveau hierarchie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $niveau = NiveauHierarchie::find($id);
        if (!$niveau) {
            return new JsonResponse(['message' => 'Niveau hierarchie not found'], 404);
        }

        try {
            $niveau->delete();
            return new JsonResponse([
                'message' => 'Niveau hierarchie deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting niveau hierarchie',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}