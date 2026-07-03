<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeEntreprise;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TypeEntrepriseController extends Controller
{
    public function index(): JsonResponse
    {
        $typeEntreprises = TypeEntreprise::all();
        return new JsonResponse(["Message" => "Type entreprises récupérées avec succès", "data" => $typeEntreprises], 200);
    }
    public function show($id):JsonResponse
    {
        $typeEntreprise = TypeEntreprise::find($id);
        if (!$typeEntreprise) {
            return new JsonResponse(["Message" => "Type entreprise non trouvé"], 404);
        }
        return new JsonResponse(["Message" => "Type entreprise trouvé avec succès", "data" => $typeEntreprise], 200);
    }
    public function store(Request $request) : JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:type_entreprises',
            'libelle' => 'required|string|max:255',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $typeEntreprise = TypeEntreprise::create($request->all());
            return new JsonResponse(["Message" => "Type entreprise créé avec succès", "data" => $typeEntreprise], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création du type entreprise", "error" => $e->getMessage()], 500);
        }

    }
    public function update(Request $request, $id): JsonResponse
    {
        $typeEntreprise = TypeEntreprise::find($id);
        if (!$typeEntreprise) {
            return new JsonResponse(["Message" => "Type entreprise non trouvé"], 404);
        }
        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:255|unique:type_entreprises,code,'.$id,
            'libelle' => 'sometimes|required|string|max:255',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $typeEntreprise->update($request->all());
            return new JsonResponse(["Message" => "Type entreprise mis à jour avec succès", "data" => $typeEntreprise], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la mise à jour du type entreprise", "error" => $e->getMessage()], 500);
        }
    }
    public function destroy($id): JsonResponse
    {
        $typeEntreprise = TypeEntreprise::find($id);
        if (!$typeEntreprise) {
            return new JsonResponse(["Message" => "Type entreprise non trouvé"], 404);
        }
        try{
            $typeEntreprise->delete();
            return new JsonResponse(["Message" => "Type entreprise supprimé avec succès"], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la suppression du type entreprise", "error" => $e->getMessage()], 500);
        }
    }
}

