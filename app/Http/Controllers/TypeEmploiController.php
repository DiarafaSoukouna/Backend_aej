<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeEmploi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TypeEmploiController extends Controller
{
    public function index() : JsonResponse
    {
        $typeEmplois = TypeEmploi::all();
        return new JsonResponse(["Message" => "Type emplois récupérées avec succès", "data" => $typeEmplois], 200);
    }
    public function show($id):JsonResponse
    {
        $typeEmploi = TypeEmploi::find($id);
        if (!$typeEmploi) {
            return new JsonResponse(["Message" => "Type emploi non trouvé"], 404);
        }
        return new JsonResponse(["Message" => "Type emploi trouvé avec succès", "data" => $typeEmploi], 200);
    }
    public function store(Request $request) : JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:type_emplois',
            'libelle' => 'required|string|max:255',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $typeEmploi = TypeEmploi::create($request->all());
            return new JsonResponse(["Message" => "Type emploi créé avec succès", "data" => $typeEmploi], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création du type emploi", "error" => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        $typeEmploi = TypeEmploi::find($id);
        if (!$typeEmploi) {
            return new JsonResponse(["Message" => "Type emploi non trouvé"], 404);
        }
        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:255|unique:type_emplois,code,'.$id,
            'libelle' => 'sometimes|required|string|max:255',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $typeEmploi->update($request->all());
            return new JsonResponse(["Message" => "Type emploi mis à jour avec succès", "data" => $typeEmploi], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la mise à jour du type emploi", "error" => $e->getMessage()], 500);
        }
    }
    public function destroy($id): JsonResponse
    {
        $typeEmploi = TypeEmploi::find($id);
        if (!$typeEmploi) {
            return new JsonResponse(["Message" => "Type emploi non trouvé"], 404);
        }
        try{
            $typeEmploi->delete();
            return new JsonResponse(["Message" => "Type emploi supprimé avec succès"], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la suppression du type emploi", "error" => $e->getMessage()], 500);
        }
    }
}
