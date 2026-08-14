<?php

namespace App\Http\Controllers;

use App\Models\Guichet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        $validated = $request->validate([
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

        $guichet = Guichet::create($validated);
        return response()->json(['message' => 'Guichet created successfully', 'data' => $guichet], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $guichet = Guichet::findOrFail($id);

        $validated = $request->validate([
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

        $guichet->update($validated);
        return response()->json(['message' => 'Guichet updated successfully', 'data' => $guichet]);
    }

    public function destroy($id): JsonResponse
    {
        $guichet = Guichet::findOrFail($id);
        $guichet->delete();
        return response()->json(['message' => 'Guichet deleted successfully'], 204);
    }
}
