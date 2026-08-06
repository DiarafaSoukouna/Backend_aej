<?php

namespace App\Http\Controllers;

use App\Models\RemboursementsDeclaration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RemboursementsDeclarationController extends Controller
{
    public function index(): JsonResponse
    {
        $declarations = RemboursementsDeclaration::with(['promoteur', 'budget'])->get();
        return new JsonResponse([
            'message' => 'Declarations retrieved successfully',
            'data' => $declarations
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'promoteur_id' => 'required|exists:promoteurs,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'montant_declare' => 'required|numeric',
            'date_declaree' => 'required|date',
            'reference_banque' => 'nullable|string|max:100',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'statut' => 'required|in:BROUILLON,SOUMIS,TRAITE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $declaration = RemboursementsDeclaration::create($validation->validated());
            return new JsonResponse([
                'message' => 'Declaration created successfully',
                'data' => $declaration
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating declaration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $declaration = RemboursementsDeclaration::with(['promoteur', 'budget'])->find($id);
        if (!$declaration) {
            return new JsonResponse(['Message' => 'Declaration not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Declaration retrieved successfully',
            'data' => $declaration
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $declaration = RemboursementsDeclaration::find($id);
        if (!$declaration) {
            return new JsonResponse(['Message' => 'Declaration not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'promoteur_id' => 'sometimes|required|exists:promoteurs,id',
            'budget_id' => 'nullable|sometimes|exists:budgets,id',
            'montant_declare' => 'sometimes|required|numeric',
            'date_declaree' => 'sometimes|required|date',
            'reference_banque' => 'nullable|string|max:100',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'statut' => 'sometimes|required|in:BROUILLON,SOUMIS,TRAITE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $declaration->update($validation->validated());
            return new JsonResponse([
                'message' => 'Declaration updated successfully',
                'data' => $declaration
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating declaration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $declaration = RemboursementsDeclaration::find($id);
        if (!$declaration) {
            return new JsonResponse(['Message' => 'Declaration not found'], 404);
        }

        try {
            $declaration->delete();
            return new JsonResponse([
                'message' => 'Declaration deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting declaration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
