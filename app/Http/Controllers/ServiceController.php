<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class ServiceController extends Controller
{
    public function index(): JsonResponse 
    {
        $services = Service::all();
        return new JsonResponse(["Message" => "Liste des services", "data" => $services], 200);
    }
    public function show($id): JsonResponse 
    {
        $service = Service::find($id);
        if (!$service) {
            return new JsonResponse(["Message" => "Service non trouvé"], 404);
        }
        return new JsonResponse(["Message" => "Service trouvé avec succès", "data" => $service], 200);
    }
    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:services',
            'description' => 'nullable|string',
            'direction_id' => 'required|exists:directions,id',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $service = Service::create($request->all());
            return new JsonResponse(["Message" => "Service créé avec succès", "data" => $service], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création du service", "error" => $e->getMessage()], 500);

        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        $service = Service::find($id);
        if (!$service) {
            return new JsonResponse(["Message" => "Service non trouvé"], 404);
        }
        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:services,code,'.$id,
            'description' => 'nullable|string',
            'direction_id' => 'sometimes|required|exists:directions,id',
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $service->update($request->all());
            return new JsonResponse(["Message" => "Service mis à jour avec succès", "data" => $service], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la mise à jour du service", "error" => $e->getMessage()], 500);

        }
    }
    public function destroy($id): JsonResponse
    {
        $service = Service::find($id);
        if (!$service) {
            return new JsonResponse(["Message" => "Service non trouvé"], 404);
        }
        try{
            $service->delete();
            return new JsonResponse(["Message" => "Service supprimé avec succès"], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la suppression du service", "error" => $e->getMessage()], 500);

        }
    }
}
