<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Direction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DirectionController extends Controller
{
    public function index () : JsonResponse
    {
        $directions = Direction::all();
        return new JsonResponse(["Message" => "Liste des directions", "data" => $directions], 200);
    }
    public function show($id) : JsonResponse 
    {
        $direction = Direction::find($id);
        if (!$direction) {
            return new JsonResponse(["Message" => "Direction non trouvée"], 404);
        }
        return new JsonResponse(["Message" => "Direction trouvée avec succès", "data" => $direction], 200);
    }
    public function store(Request $request) : JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:directions',
            'description' => 'nullable|string',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $direction = Direction::create($request->all());
            return new JsonResponse(["Message" => "Direction créée avec succès", "data" => $direction], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création de la direction", "error" => $e->getMessage()], 500);

        }
    }
    public function update(Request $request, $id) : JsonResponse
    {
        $direction = Direction::find($id);
        if (!$direction) {
            return new JsonResponse(["Message" => "Direction non trouvée"], 404);
        }
        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:directions,code,'.$id,
            'description' => 'nullable|string',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $direction->update($request->all());
            return new JsonResponse(["Message" => "Direction mise à jour avec succès", "data" => $direction], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la mise à jour de la direction", "error" => $e->getMessage()], 500);

        }
    }
    public function destroy($id) : JsonResponse
    {
        $direction = Direction::find($id);
        if (!$direction) {
            return new JsonResponse(["Message" => "Direction non trouvée"], 404);
        }
        try{
            $direction->delete();
            return new JsonResponse(["Message" => "Direction supprimée avec succès"], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la suppression de la direction", "error" => $e->getMessage()], 500);

        }
    }
}
