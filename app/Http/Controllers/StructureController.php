<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Structure;

class StructureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $structures = Structure::with(['niveauHierarchie', 'parent'])->get();

        if ($request->has('niveau_id') && !empty($request->niveau_id))
            $structures = $structures->where('niveau_id', $request->niveau_id);
        if ($request->has('parent_id') && !empty($request->parent_id))
            $structures = $structures->where('parent_id', $request->parent_id);

        return new JsonResponse([
            'message' => 'Structures retrieved successfully',
            'data' => $structures
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $structure = Structure::with(['niveauHierarchie', 'parent', 'children', 'fonctions', 'personnels'])->find($id);
        if (!$structure) {
            return new JsonResponse(['message' => 'Structure not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Structure retrieved successfully',
            'data' => $structure
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:structures',
            'description' => 'nullable|string',
            'niveau_id' => 'nullable|exists:niveau_hierarchie,id',
            'parent_id' => 'nullable|exists:structures,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $structure = Structure::create($validation->validated());
            return new JsonResponse([
                'message' => 'Structure created successfully',
                'data' => $structure
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $structure = Structure::find($id);
        if (!$structure) {
            return new JsonResponse(['message' => 'Structure not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:structures,code,'.$id,
            'description' => 'nullable|string',
            'niveau_id' => 'nullable|exists:niveau_hierarchie,id',
            'parent_id' => 'nullable|exists:structures,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $structure->update($validation->validated());
            return new JsonResponse([
                'message' => 'Structure updated successfully',
                'data' => $structure
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $structure = Structure::find($id);
        if (!$structure) {
            return new JsonResponse(['message' => 'Structure not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'nom' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255|unique:structures,code,'.$id,
            'description' => 'nullable|string',
            'niveau_id' => 'nullable|exists:niveau_hierarchie,id',
            'parent_id' => 'nullable|exists:structures,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $structure->update(array_filter($validation->validated()));
            return new JsonResponse([
                'message' => 'Structure patched successfully',
                'data' => $structure
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $structure = Structure::find($id);
        if (!$structure) {
            return new JsonResponse(['message' => 'Structure not found'], 404);
        }

        try {
            $structure->delete();
            return new JsonResponse([
                'message' => 'Structure deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}