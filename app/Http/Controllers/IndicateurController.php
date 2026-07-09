<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indicateur;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class IndicateurController extends Controller
{
    public function index() : JsonResponse
    {
        $indicateurs = Indicateur::all();
        return new JsonResponse(['Message' => 'Indicateurs retrouvés avec succès', 'data' => $indicateurs], 200);

    }
    public function show($id) : JsonResponse
    {
        $indicateur = Indicateur::find($id);
        if (!$indicateur) {
            return new JsonResponse(['Message' => 'Indicateur non trouvé'], 404);
        }
        return new JsonResponse(['Message' => 'Indicateur retrouvé avec succès', 'data' => $indicateur], 200);
    }
    public function store(Request $request) : JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:150|unique:indicateurs',
            'description' => 'nullable|string',
            'type_valeur' => 'required|in:numerique,texte,pourcentage,booleen',
            'unite' => 'nullable|string|max:50',
            'statut' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $indicateur = Indicateur::create($validation->validated());

            return new JsonResponse([
                'message' => 'Indicateur créé avec succès',
                'data' => $indicateur
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la création de l\'indicateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id) : JsonResponse
    {
        $indicateur = Indicateur::find($id);
        if (!$indicateur) {
            return new JsonResponse(['Message' => 'Indicateur non trouvé'], 404);
        }

        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:150|unique:indicateurs,nom,' . $id,
            'description' => 'nullable|string',
            'type_valeur' => 'sometimes|required|in:numerique,texte,pourcentage,booleen',
            'unite' => 'nullable|string|max:50',
            'statut' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $indicateur->update($validation->validated());

            return new JsonResponse([
                'message' => 'Indicateur mis à jour avec succès',
                'data' => $indicateur
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la mise à jour de l\'indicateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id) : JsonResponse
    {
        $indicateur = Indicateur::find($id);
        if (!$indicateur) {
            return new JsonResponse(['Message' => 'Indicateur non trouvé'], 404);
        }

        try {
            $indicateur->delete();

            return new JsonResponse([
                'message' => 'Indicateur supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la suppression de l\'indicateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
