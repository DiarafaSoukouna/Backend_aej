<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\SousPrefecture;

class SousPrefectureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sousPrefectures = SousPrefecture::with(['departement'])->get();

        if ($request->has('departement_id') && !empty($request->departement_id))
            $sousPrefectures = $sousPrefectures->where('departement_id', $request->departement_id);

        return new JsonResponse([
            'message' => 'Sous prefectures retrieved successfully',
            'data' => $sousPrefectures
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $sousPrefecture = SousPrefecture::with(['departement', 'communes'])->find($id);
        if (!$sousPrefecture) {
            return new JsonResponse(['message' => 'Sous prefecture not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Sous prefecture retrieved successfully',
            'data' => $sousPrefecture
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'departement_id' => 'required|exists:departements,id',
            'code' => 'nullable|string|max:50|unique:sous_prefectures',
            'nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $sousPrefecture = SousPrefecture::create($validation->validated());
            return new JsonResponse([
                'message' => 'Sous prefecture created successfully',
                'data' => $sousPrefecture
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating sous prefecture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $sousPrefecture = SousPrefecture::find($id);
        if (!$sousPrefecture) {
            return new JsonResponse(['message' => 'Sous prefecture not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'departement_id' => 'sometimes|required|exists:departements,id',
            'code' => 'sometimes|nullable|string|max:50|unique:sous_prefectures,code,'.$id,
            'nom' => 'sometimes|required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $sousPrefecture->update($validation->validated());
            return new JsonResponse([
                'message' => 'Sous prefecture updated successfully',
                'data' => $sousPrefecture
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating sous prefecture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $sousPrefecture = SousPrefecture::find($id);
        if (!$sousPrefecture) {
            return new JsonResponse(['message' => 'Sous prefecture not found'], 404);
        }

        try {
            $sousPrefecture->delete();
            return new JsonResponse([
                'message' => 'Sous prefecture deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting sous prefecture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'sous_prefectures' => 'required|array|min:1',
            'sous_prefectures.*.departement_code' => 'required|string',
            'sous_prefectures.*.code' => 'nullable|string|max:50|unique:sous_prefectures',
            'sous_prefectures.*.nom' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $sousPrefectures = [];
            foreach ($request->sous_prefectures as $sousPrefectureData) {
                $departement = Departement::where('code', $sousPrefectureData['departement_code'])->first();
                if (!$departement) {
                    return new JsonResponse([
                        'message' => 'Departement not found for code: ' . $sousPrefectureData['departement_code']
                    ], 404);
                }

                $sousPrefectureData['departement_id'] = $departement->id;
                unset($sousPrefectureData['departement_code']);

                $sousPrefectures[] = SousPrefecture::create($sousPrefectureData);
            }

            return new JsonResponse([
                'message' => 'Sous prefectures created successfully',
                'data' => $sousPrefectures,
                'count' => count($sousPrefectures)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating sous prefectures',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}