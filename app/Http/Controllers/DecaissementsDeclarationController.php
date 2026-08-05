<?php

namespace App\Http\Controllers;

use App\Models\DecaissementsDeclaration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DecaissementsDeclarationController extends Controller
{
    public function index(): JsonResponse
    {
        $declarations = DecaissementsDeclaration::with(['plan', 'promoteur'])->get();
        return new JsonResponse([
            'message' => 'Déclarations de décaissement retrieved successfully',
            'data' => $declarations
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plan_decaissements,id',
            'promoteur_id' => 'required|exists:promoteurs,id',
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
            $declaration = DecaissementsDeclaration::create($validation->validated());
            return new JsonResponse([
                'message' => 'Déclaration de décaissement created successfully',
                'data' => $declaration
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating déclaration de décaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $declaration = DecaissementsDeclaration::with(['plan', 'promoteur'])->find($id);
        if (!$declaration) {
            return new JsonResponse(['Message' => 'Déclaration de décaissement not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Déclaration de décaissement retrieved successfully',
            'data' => $declaration
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $declaration = DecaissementsDeclaration::find($id);
        if (!$declaration) {
            return new JsonResponse(['Message' => 'Déclaration de décaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'plan_id' => 'sometimes|required|exists:plan_decaissements,id',
            'promoteur_id' => 'sometimes|required|exists:promoteurs,id',
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
                'message' => 'Déclaration de décaissement updated successfully',
                'data' => $declaration
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating déclaration de décaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $declaration = DecaissementsDeclaration::find($id);
        if (!$declaration) {
            return new JsonResponse(['Message' => 'Déclaration de décaissement not found'], 404);
        }

        try {
            $declaration->delete();
            return new JsonResponse([
                'message' => 'Déclaration de décaissement deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting déclaration de décaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
