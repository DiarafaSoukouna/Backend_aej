<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\CategorieTransaction;

class CategorieTransactionController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = CategorieTransaction::with('parent', 'children')->get();
        return new JsonResponse(['Message' => 'Categories retrieved successfully', 'data' => $categories], 200);
    }

    public function show($id): JsonResponse
    {
        $categorie = CategorieTransaction::with('parent', 'children')->find($id);
        if (!$categorie) {
            return new JsonResponse(['Message' => 'Categorie not found'], 404);
        }
        return new JsonResponse(['Message' => 'Categorie retrieved successfully', 'data' => $categorie], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:categories_transactions,code',
            'libelle' => 'required|string|max:100',
            'description' => 'nullable|string',
            'niveau' => 'sometimes|integer|min:1',
            'parent_id' => 'nullable|exists:categories_transactions,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $categorie = CategorieTransaction::create($request->all());

            return new JsonResponse([
                'message' => 'Categorie created successfully',
                'data' => $categorie
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating categorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $categorie = CategorieTransaction::find($id);
        if (!$categorie) {
            return new JsonResponse(['Message' => 'Categorie not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:50|unique:categories_transactions,code,' . $id,
            'libelle' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'niveau' => 'sometimes|integer|min:1',
            'parent_id' => 'nullable|exists:categories_transactions,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $categorie->update($request->all());

            return new JsonResponse([
                'message' => 'Categorie updated successfully',
                'data' => $categorie
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating categorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $categorie = CategorieTransaction::find($id);
        if (!$categorie) {
            return new JsonResponse(['Message' => 'Categorie not found'], 404);
        }

        try {
            $categorie->delete();
            return new JsonResponse(['Message' => 'Categorie deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting categorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
