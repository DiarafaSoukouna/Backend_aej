<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Suivi;

class SuiviController extends Controller
{
    public function index(): JsonResponse
    {
        $suivis = Suivi::with(['microProjet', 'jeune'])->get();
        return new JsonResponse(['Message' => 'Suivi list retrieved successfully', 'data' => $suivis], 200);
    }

    public function show($id): JsonResponse
    {
        $suivi = Suivi::with(['microProjet', 'jeune'])->find($id);
        if (!$suivi) {
            return new JsonResponse(['Message' => 'Suivi not found'], 404);
        }
        return new JsonResponse(['Message' => 'Suivi retrieved successfully', 'data' => $suivi], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'promoteur_id' => 'required|exists:promoteurs,id',
            'libelle' => 'required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $suivi = Suivi::create($request->all());

            return new JsonResponse([
                'message' => 'Suivi created successfully',
                'data' => $suivi
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating suivi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $suivi = Suivi::find($id);
        if (!$suivi) {
            return new JsonResponse(['Message' => 'Suivi not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'sometimes|required|exists:micro_projets,id',
            'promoteur_id' => 'sometimes|required|exists:promoteurs,id',
            'libelle' => 'sometimes|required|string|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $suivi->update($request->all());

            return new JsonResponse([
                'message' => 'Suivi updated successfully',
                'data' => $suivi
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating suivi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $suivi = Suivi::find($id);
        if (!$suivi) {
            return new JsonResponse(['Message' => 'Suivi not found'], 404);
        }

        try {
            $suivi->delete();
            return new JsonResponse(['Message' => 'Suivi deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting suivi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
