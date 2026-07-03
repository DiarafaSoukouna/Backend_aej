<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Localite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LocaliteController extends Controller
{
    public function index(): JsonResponse
    {
        $localites = Localite::all();
        return new JsonResponse(["Message" => "Localités récupérées avec succès", "data" => $localites], 200);
    }
    public function show($id) : JsonResponse
    {
        $localite = Localite::find($id);
        if (!$localite) {
            return new JsonResponse(["Message" => "Localité non trouvée"], 404);
        }
        return new JsonResponse(["Message" => "Localité récupérée avec succès", "data" => $localite], 200);
    }
    public function getLocalitesByNiveau($niveauId) : JsonResponse
    {
        $localites = Localite::where("niveau_localite_id", $niveauId)->get();
        return new JsonResponse(["Message" => "Localités récupérées avec succès", "data" => $localites ], 200);
    }
    public function store(Request $request) : JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:localites',
            'couche_cartographique' => 'nullable|string|max:255',
            'niveau_localite_id' => 'required|integer|exists:niveau_localites,id',
           
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $localite = Localite::create($request->all());
            return new JsonResponse(["Message" => "Localité créée avec succès", "data" => $localite], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création de la localité", "error" => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id) : JsonResponse
    {
        $localite = Localite::find($id);
        if (!$localite) {
            return new JsonResponse(["Message" => "Localité non trouvée"], 404);
        }
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:localites,code,' . $id,
            'couche_cartographique' => 'nullable|string|max:255',
            'niveau_localite_id' => 'required|integer|exists:niveau_localites,id',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $localite->update($request->all());
            return new JsonResponse(["Message" => "Localité mise à jour avec succès", "data" => $localite], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la mise à jour de la localité", "error" => $e->getMessage()], 500);
        }
    }
    public function destroy($id) : JsonResponse
    {
        $localite = Localite::find($id);
        if (!$localite) {
            return new JsonResponse(["Message" => "Localité non trouvée"], 404);
        }
        try{
            $localite->delete();
            return new JsonResponse(["Message" => "Localité supprimée avec succès"], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la suppression de la localité", "error" => $e->getMessage()], 500);
        }
    }
}
