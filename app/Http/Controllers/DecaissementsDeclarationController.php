<?php

namespace App\Http\Controllers;

use App\Models\DecaissementsDeclaration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DecaissementsDeclarationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $declarations = DecaissementsDeclaration::with(['planDecaissement', 'ligneDecaissement', 'promoteur'])->get();

        if ($request->has('plan_decaissement_id') && !empty($request->plan_decaissement_id)) 
            $declarations = $declarations->where('plan_decaissement_id', $request->plan_decaissement_id);
        if ($request->has('ligne_decaissement_id') && !empty($request->ligne_decaissement_id)) 
            $declarations = $declarations->where('ligne_decaissement_id', $request->ligne_decaissement_id);
        if ($request->has('promoteur_id') && !empty($request->promoteur_id)) 
            $declarations = $declarations->where('promoteur_id', $request->promoteur_id);
        if ($request->has('statut') && !empty($request->statut)) 
            $declarations = $declarations->where('statut', $request->statut);

        return new JsonResponse([
            'message' => 'Déclarations de décaissement retrieved successfully',
            'data' => $declarations
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'plan_decaissement_id' => 'nullable|exists:plan_decaissements,id',
            'ligne_decaissement_id' => 'nullable|exists:ligne_decaissements,id',
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
        $declaration = DecaissementsDeclaration::with(['planDecaissement', 'ligneDecaissement', 'promoteur'])->find($id);
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
            'plan_decaissement_id' => 'nullable|exists:plan_decaissements,id',
            'ligne_decaissement_id' => 'nullable|exists:ligne_decaissements,id',
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

    public function patch(Request $request, $id): JsonResponse
    {
        $declaration = DecaissementsDeclaration::find($id);
        if (!$declaration) {
            return new JsonResponse(['Message' => 'Déclaration de décaissement not found'], 404);
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
                'message' => 'Déclaration de décaissement patched successfully',
                'data' => $declaration
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching déclaration de décaissement',
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
