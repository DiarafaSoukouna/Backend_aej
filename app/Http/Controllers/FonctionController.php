<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fonction;
use App\Models\Structure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FonctionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $fonctions = Fonction::with(['structure'])->get();

        if ($request->has('structure_id') && !empty($request->structure_id))
            $fonctions = $fonctions->where('structure_id', $request->structure_id);

        return new JsonResponse(["Message" => "Fonctions récupérées avec succès", "data" => $fonctions], 200);
    }
    public function show($id): JsonResponse
    {
        $fonction = Fonction::with(['structure'])->find($id);
        if (!$fonction) {
            return new JsonResponse(["Message" => "Fonction non trouvée"], 404);
        }
        return new JsonResponse(["Message" => "Fonction trouvée avec succès", "data" => $fonction], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:fonctions,code',
            'description' => 'nullable|string',
            'structure_id' => 'nullable|exists:structures,id',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try {
            $fonction = Fonction::create($request->all());
            return new JsonResponse(["Message" => "Fonction créée avec succès", "data" => $fonction], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création de la fonction", "error" => $e->getMessage()], 500);
        }
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'fonctions' => 'required|array',
            'fonctions.*.nom' => 'required|string|max:255',
            'fonctions.*.code' => 'required|string|max:255|unique:fonctions,code',
            'fonctions.*.description' => 'nullable|string',
            'fonctions.*.structure_code' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }

        try {
            $fonctions = [];
            foreach ($request->fonctions as $fonctionData) {
                $structure = Structure::where('code', $fonctionData['structure_code'])->first();
                $fonctionData['structure_id'] = $structure ? $structure->id : null;
                unset($fonctionData['structure_code']);

                $fonctions[] = Fonction::create($fonctionData);
            }

            return new JsonResponse([
                'message' => 'Fonctions created successfully',
                'data' => $fonctions,
                'count' => count($fonctions)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating fonctions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $fonction = Fonction::find($id);
        if (!$fonction) {
            return new JsonResponse(["Message" => "Fonction non trouvée"], 404);
        }
        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:fonctions,code,' . $id,
            'description' => 'nullable|string',
            'structure_id' => 'nullable|exists:structures,id',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try {
            $fonction->update($request->all());
            return new JsonResponse(["Message" => "Fonction mise à jour avec succès", "data" => $fonction], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la mise à jour de la fonction", "error" => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $fonction = Fonction::find($id);
        if (!$fonction) {
            return new JsonResponse(["Message" => "Fonction non trouvée"], 404);
        }
        try {
            $fonction->delete();
            return new JsonResponse(["Message" => "Fonction supprimée avec succès"], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la suppression de la fonction", "error" => $e->getMessage()], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $fonction = Fonction::find($id);
        if (!$fonction) {
            return new JsonResponse(["Message" => "Fonction non trouvée"], 404);
        }

        $validation = Validator::make($request->all(), [
            'nom' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255|unique:fonctions,code,' . $id,
            'description' => 'nullable|string',
            'structure_id' => 'nullable|exists:structures,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }

        try {
            $fonction->update(array_filter($validation->validated()));
            return new JsonResponse(["Message" => "Fonction patchée avec succès", "data" => $fonction], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors du patch de la fonction", "error" => $e->getMessage()], 500);
        }
    }
}
