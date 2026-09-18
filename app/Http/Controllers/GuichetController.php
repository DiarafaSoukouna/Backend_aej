<?php

namespace App\Http\Controllers;

use App\Models\Guichet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GuichetController extends Controller
{
    public function index(): JsonResponse
    {
        $guichets = Guichet::with('workflow')->get();
        return response()->json(['message' => 'Guichets retrieved successfully', 'data' => $guichets]);
    }

    public function show($id): JsonResponse
    {
        $guichet = Guichet::with('workflow')->findOrFail($id);
        return response()->json(['message' => 'Guichet retrieved successfully', 'data' => $guichet]);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'workflow_code' => 'nullable|string|max:50|exists:workflows,code',
            'code' => 'nullable|string|max:50|unique:guichets,code',
            'libelle' => 'required|string|max:100',
            'description' => 'nullable|string',
            'couleur' => 'nullable|string|max:7',
            'montant_min' => 'nullable|numeric|min:0',
            'montant_max' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'is_form_active' => 'nullable|boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $guichet = Guichet::create($validation->validated());
            return response()->json(['message' => 'Guichet created successfully', 'data' => $guichet], 201);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Guichet creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $guichet = Guichet::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'workflow_code' => 'nullable|string|max:50|exists:workflows,code',
            'code' => 'nullable|string|max:50|unique:guichets,code,' . $id,
            'libelle' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'couleur' => 'nullable|string|max:7',
            'montant_min' => 'nullable|numeric|min:0',
            'montant_max' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'is_form_active' => 'nullable|boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $guichet->update($validation->validated());
            return response()->json(['message' => 'Guichet updated successfully', 'data' => $guichet]);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Guichet update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $guichet = Guichet::findOrFail($id);
        try {
            $guichet->delete();
            return response()->json(['message' => 'Guichet deleted successfully'], 204);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Guichet deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
