<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\IndicateurSuivi;

class IndicateurSuiviController extends Controller
{
    public function index(): JsonResponse
    {
        $indicateursSuivi = IndicateurSuivi::with('indicateur')->get();
        return new JsonResponse(['Message' => 'IndicateurSuivi list retrieved successfully', 'data' => $indicateursSuivi], 200);
    }

    public function show($id): JsonResponse
    {
        $indicateurSuivi = IndicateurSuivi::with('indicateur')->find($id);
        if (!$indicateurSuivi) {
            return new JsonResponse(['Message' => 'IndicateurSuivi not found'], 404);
        }
        return new JsonResponse(['Message' => 'IndicateurSuivi retrieved successfully', 'data' => $indicateurSuivi], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'indicateur_id' => 'required|exists:indicateurs,id',
            'valeur' => 'required|string|max:255',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $indicateurSuivi = IndicateurSuivi::create($request->all());

            return new JsonResponse([
                'message' => 'IndicateurSuivi created successfully',
                'data' => $indicateurSuivi
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating indicateurSuivi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $indicateurSuivi = IndicateurSuivi::find($id);
        if (!$indicateurSuivi) {
            return new JsonResponse(['Message' => 'IndicateurSuivi not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'indicateur_id' => 'sometimes|required|exists:indicateurs,id',
            'valeur' => 'sometimes|required|string|max:255',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $indicateurSuivi->update($request->all());

            return new JsonResponse([
                'message' => 'IndicateurSuivi updated successfully',
                'data' => $indicateurSuivi
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating indicateurSuivi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $indicateurSuivi = IndicateurSuivi::find($id);
        if (!$indicateurSuivi) {
            return new JsonResponse(['Message' => 'IndicateurSuivi not found'], 404);
        }

        try {
            $indicateurSuivi->delete();
            return new JsonResponse(['Message' => 'IndicateurSuivi deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting indicateurSuivi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
