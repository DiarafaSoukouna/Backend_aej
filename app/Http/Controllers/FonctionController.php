<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fonction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FonctionController extends Controller
{
    public function index(): JsonResponse 
    {
        $fonctions = Fonction::all();
        return new JsonResponse(["Message" => "Fonctions récupérées avec succès", "data" => $fonctions], 200);
    }
    public function show($id): JsonResponse
    {
        $fonction = Fonction::find($id);
        if (!$fonction) {
            return new JsonResponse(["Message" => "Fonction non trouvée"], 404);
        }
        return new JsonResponse(["Message" => "Fonction trouvée avec succès", "data" => $fonction], 200);
    }
    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:fonctions',
            'description' => 'nullable|string',
            'service_id' => 'required|exists:services,id',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $fonction = Fonction::create($request->all());
            return new JsonResponse(["Message" => "Fonction créée avec succès", "data" => $fonction], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création de la fonction", "error" => $e->getMessage()], 500);

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
            'code' => 'sometimes|required|string|max:255|unique:fonctions,code,'.$id,
            'description' => 'nullable|string',
            'service_id' => 'sometimes|required|exists:services,id',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
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
        try{
            $fonction->delete();
            return new JsonResponse(["Message" => "Fonction supprimée avec succès"], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la suppression de la fonction", "error" => $e->getMessage()], 500);

        }
    }
}
