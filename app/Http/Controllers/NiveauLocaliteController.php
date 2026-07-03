<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Niveau_localite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;   

class NiveauLocaliteController extends Controller
{
    public function index() : JsonResponse
    {
        $localites = Niveau_localite::all();
        return new JsonResponse(["Message" => "Niveau de localité récupérés avec succès", "data" => $localites], 200);
    }
    public function show($id) : JsonResponse
    {
        $localite = Niveau_localite::with('parent')->find($id);
        if (!$localite) {
            return new JsonResponse(["Message" => "Niveau de localité non trouvé"], 404);
        }
        return new JsonResponse(["Message" => "Niveau de localité trouvé avec succès", "data" => $localite], 200);
    }
    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:niveau_localites',
            'parent_id' => 'nullable|exists:niveau_localites,id',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $localite = Niveau_localite::create($request->all());
            return new JsonResponse(["Message" => "Niveau de localité créé avec succès", "data" => $localite], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création du niveau de localité", "error" => $e->getMessage()], 500);

        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        $localite = Niveau_localite::find($id);
        if (!$localite) {
            return new JsonResponse(["Message" => "Niveau de localité non trouvé"], 404);
        }
        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:niveau_localites,code,'.$id,
            'parent_id' => 'nullable|exists:niveau_localites,id',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $localite->update($request->all());
            return new JsonResponse(["Message" => "Niveau de localité mis à jour avec succès", "data" => $localite], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la mise à jour du niveau de localité", "error" => $e->getMessage()], 500);

        }
    }
    public function destroy($id): JsonResponse
    {
        $localite = Niveau_localite::find($id);
        if (!$localite) {
            return new JsonResponse(["Message" => "Niveau de localité non trouvé"], 404);
        }
        try{
            $localite->delete();
            return new JsonResponse(["Message" => "Niveau de localité supprimé avec succès"], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la suppression du niveau de localité", "error" => $e->getMessage()], 500);

        }
    }
}
