<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indicateur;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class IndicateurController extends Controller
{
    public function index(Request $request) : JsonResponse
    {
        $query = Indicateur::with(['microProjet', 'suivis']);
        
        $filters = ['micro_projet_id', 'code', 'libelle'];
        foreach ($filters as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        $perPage = $request->get('per_page', 20);
        $indicateurs = $query->paginate($perPage);

        return new JsonResponse([
            'message' => 'Indicateurs retrieved successfully',
            'data' => $indicateurs->items(),
            'pagination' => [
                'current_page' => $indicateurs->currentPage(),
                'per_page' => $indicateurs->perPage(),
                'total' => $indicateurs->total(),
                'last_page' => $indicateurs->lastPage(),
                'from' => $indicateurs->firstItem(),
                'to' => $indicateurs->lastItem(),
            ],
        ], 200);
    }
    public function show($id) : JsonResponse
    {
        $indicateur = Indicateur::with('microProjet', 'suivis')->find($id);
        if (!$indicateur) {
            return new JsonResponse(['Message' => 'Indicateur non trouvé'], 404);
        }
        return new JsonResponse(['Message' => 'Indicateur retrouvé avec succès', 'data' => $indicateur], 200);
    }
    public function store(Request $request) : JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'code' => 'required|string|unique:indicateurs,code',
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unite' => 'nullable|string|max:50',
            'valeur_cible' => 'required|string',
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
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'code' => 'required|string|unique:indicateurs,code,' . $id,
            'libelle' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'valeur_cible' => 'sometimes|required|string',
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