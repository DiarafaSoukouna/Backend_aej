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
        $declarations = RemboursementsDeclaration::with(['planRemboursement', 'promoteur'])->get();
        return new JsonResponse([
            'message' => 'Declarations retrieved successfully',
            'data' => $declarations
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'plan_remboursement_id' => 'nullable|exists:plan_remboursements,id',
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'montant_declare' => 'nullable|numeric',
            'date_declaree' => 'nullable|date',
            'reference_banque' => 'nullable|string|max:100',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'statut' => 'nullable|in:BROUILLON,SOUMIS,TRAITE',
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
        $declaration = RemboursementsDeclaration::with(['planRemboursement', 'promoteur'])->find($id);
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
            'plan_remboursement_id' => 'nullable|exists:plan_remboursements,id',
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'montant_declare' => 'nullable|numeric',
            'date_declaree' => 'nullable|date',
            'reference_banque' => 'nullable|string|max:100',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'statut' => 'nullable|in:BROUILLON,SOUMIS,TRAITE',
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

    public function patch(Request $request, $id): JsonResponse
    {
        $declaration = RemboursementsDeclaration::find($id);
        if (!$declaration) {
            return new JsonResponse(['Message' => 'Declaration not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'montant_declare' => 'nullable|numeric',
            'date_declaree' => 'nullable|date',
            'reference_banque' => 'nullable|string|max:100',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'statut' => 'nullable|in:BROUILLON,SOUMIS,TRAITE',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $declaration->update(array_filter($validation->validated()));
            return new JsonResponse([
                'message' => 'Declaration patched successfully',
                'data' => $declaration
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching declaration',
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
