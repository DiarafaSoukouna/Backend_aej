<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Commune;
use App\Models\SousPrefecture;

class CommuneController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $communes = Commune::with(['sousPrefecture'])->get();

        if ($request->has('sous_prefecture_id') && !empty($request->sous_prefecture_id))
            $communes = $communes->where('sous_prefecture_id', $request->sous_prefecture_id);

        return new JsonResponse([
            'message' => 'Communes retrieved successfully',
            'data' => $communes
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $commune = Commune::with(['sousPrefecture', 'villes'])->find($id);
        if (!$commune) {
            return new JsonResponse(['message' => 'Commune not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Commune retrieved successfully',
            'data' => $commune
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'sous_prefecture_id' => 'required|exists:sous_prefectures,id',
            'code' => 'nullable|string|max:50|unique:communes',
            'nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $commune = Commune::create($validation->validated());
            return new JsonResponse([
                'message' => 'Commune created successfully',
                'data' => $commune
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating commune',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $commune = Commune::find($id);
        if (!$commune) {
            return new JsonResponse(['message' => 'Commune not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'sous_prefecture_id' => 'sometimes|required|exists:sous_prefectures,id',
            'code' => 'sometimes|nullable|string|max:50|unique:communes,code,'.$id,
            'nom' => 'sometimes|required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $commune->update($validation->validated());
            return new JsonResponse([
                'message' => 'Commune updated successfully',
                'data' => $commune
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating commune',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $commune = Commune::find($id);
        if (!$commune) {
            return new JsonResponse(['message' => 'Commune not found'], 404);
        }

        try {
            $commune->delete();
            return new JsonResponse([
                'message' => 'Commune deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting commune',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'communes' => 'required|array|min:1',
            'communes.*.sous_prefecture_code' => 'required|string',
            'communes.*.code' => 'nullable|string|max:50|unique:communes',
            'communes.*.nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $communes = [];
            foreach ($request->communes as $communeData) {
                $sousPrefecture = SousPrefecture::where('code', $communeData['sous_prefecture_code'])->first();
                if (!$sousPrefecture) {
                    return new JsonResponse([
                        'message' => 'Sous prefecture not found for code: ' . $communeData['sous_prefecture_code']
                    ], 404);
                }

                $communeData['sous_prefecture_id'] = $sousPrefecture->id;
                unset($communeData['sous_prefecture_code']);

                $communes[] = Commune::create($communeData);
            }

            return new JsonResponse([
                'message' => 'Communes created successfully',
                'data' => $communes,
                'count' => count($communes)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating communes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}