<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Ville;

class VilleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $villes = Ville::with(['commune'])->get();

        if ($request->has('commune_id') && !empty($request->commune_id))
            $villes = $villes->where('commune_id', $request->commune_id);

        return new JsonResponse([
            'message' => 'Villes retrieved successfully',
            'data' => $villes
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $ville = Ville::with(['commune'])->find($id);
        if (!$ville) {
            return new JsonResponse(['message' => 'Ville not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Ville retrieved successfully',
            'data' => $ville
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'commune_id' => 'required|exists:communes,id',
            'code' => 'nullable|string|max:50|unique:villes',
            'nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $ville = Ville::create($validation->validated());
            return new JsonResponse([
                'message' => 'Ville created successfully',
                'data' => $ville
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating ville',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $ville = Ville::find($id);
        if (!$ville) {
            return new JsonResponse(['message' => 'Ville not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'commune_id' => 'sometimes|required|exists:communes,id',
            'code' => 'sometimes|nullable|string|max:50|unique:villes,code,'.$id,
            'nom' => 'sometimes|required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $ville->update($validation->validated());
            return new JsonResponse([
                'message' => 'Ville updated successfully',
                'data' => $ville
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating ville',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $ville = Ville::find($id);
        if (!$ville) {
            return new JsonResponse(['message' => 'Ville not found'], 404);
        }

        try {
            $ville->delete();
            return new JsonResponse([
                'message' => 'Ville deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting ville',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'villes' => 'required|array|min:1',
            'villes.*.commune_code' => 'required|string',
            'villes.*.code' => 'nullable|string|max:50|unique:villes',
            'villes.*.nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $villes = [];
            foreach ($request->villes as $villeData) {
                $commune = Commune::where('code', $villeData['commune_code'])->first();
                if (!$commune) {
                    return new JsonResponse([
                        'message' => 'Commune not found for code: ' . $villeData['commune_code']
                    ], 404);
                }

                $villeData['commune_id'] = $commune->id;
                unset($villeData['commune_code']);

                $villes[] = Ville::create($villeData);
            }

            return new JsonResponse([
                'message' => 'Villes created successfully',
                'data' => $villes,
                'count' => count($villes)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating villes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}