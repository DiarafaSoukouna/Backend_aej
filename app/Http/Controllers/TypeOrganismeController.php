<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeOrganisme;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class TypeOrganismeController extends Controller
{
    public function index(): JsonResponse
    {
        $typeOrganismes = TypeOrganisme::all();
        return new JsonResponse(['Message' => 'Types organismes retrouvés avec succès', 'data' => $typeOrganismes], 200);
    }
    public function show($id) : JsonResponse
    {
        $typeOrganisme = TypeOrganisme::find($id);
        if (!$typeOrganisme) {
            return new JsonResponse(['Message' => 'Type organisme non trouvé'], 404);
        }
        return new JsonResponse(['Message' => 'Type organisme retrouvé avec succès', 'data' => $typeOrganisme], 200);
    }
    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:30|unique:type_organismes',
            'libelle' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $typeOrganisme = TypeOrganisme::create($request->only(['code', 'libelle']));

            return new JsonResponse([
                'message' => 'Type organisme créé avec succès',
                'data' => $typeOrganisme
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la création du type organisme',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        $typeOrganisme = TypeOrganisme::find($id);
        if (!$typeOrganisme) {
            return new JsonResponse(['Message' => 'Type organisme non trouvé'], 404);
        }

        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:30|unique:type_organismes,code,' . $id,
            'libelle' => 'sometimes|required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $typeOrganisme->update($request->only(['code', 'libelle']));

            return new JsonResponse([
                'message' => 'Type organisme mis à jour avec succès',
                'data' => $typeOrganisme
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la mise à jour du type organisme',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id): JsonResponse
    {
        $typeOrganisme = TypeOrganisme::find($id);
        if (!$typeOrganisme) {
            return new JsonResponse(['Message' => 'Type organisme non trouvé'], 404);
        }

        try {
            $typeOrganisme->delete();
            return new JsonResponse(['Message' => 'Type organisme supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la suppression du type organisme',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
