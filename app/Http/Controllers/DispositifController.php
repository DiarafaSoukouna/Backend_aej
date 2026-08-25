<?php

namespace App\Http\Controllers;

use App\Models\Dispositif;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
class DispositifController extends Controller
{
    public function index(): JsonResponse
    {
        $dispositifs = Dispositif::with(['projet', 'guichet', 'workflowVersion'])->get();
        return response()->json(['message' => 'Dispositifs retrieved successfully', 'data' => $dispositifs]);
    }

    public function show($id): JsonResponse
    {
        $dispositif = Dispositif::with(['projet', 'guichet', 'workflowVersion'])->findOrFail($id);
        return response()->json(['message' => 'Dispositif retrieved successfully', 'data' => $dispositif]);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'nullable|string|max:50|unique:dispositifs,code',
            'projet_id' => 'nullable|exists:projets,id|unique:dispositifs,projet_id',
            'guichet_id' => 'nullable|exists:guichets,id',
            'workflow_version' => 'nullable|string|max:50|exists:workflow_versions,code',
            'intitule' => 'required|string|max:200',
            'budget_alloue' => 'required|numeric|min:0',
            'montant_min' => 'nullable|numeric|min:0',
            'montant_max' => 'nullable|numeric|min:0',
            'taux' => 'nullable|numeric|min:0',
            'duree' => 'nullable|integer|min:0',
            'nbre_emplois_prevu' => 'nullable|integer|min:0',
            'nbre_beneficiaire_prevu' => 'nullable|integer|min:0',
            'nbre_micro_projet_prevu' => 'nullable|integer|min:0',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $dispositif = Dispositif::create($validation->validated());
            return new JsonResponse([
                'message' => 'Dispositif created successfully',
                'data' => $dispositif
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating dispositif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $dispositif = Dispositif::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'code' => 'nullable|string|max:50|unique:dispositifs,code,' . $id,
            'projet_id' => 'nullable|exists:projets,id|unique:dispositifs,projet_id,' . $id,
            'guichet_id' => 'nullable|exists:guichets,id',
            'workflow_version' => 'nullable|string|max:50|exists:workflow_versions,code',
            'intitule' => 'nullable|string|max:200',
            'budget_alloue' => 'nullable|numeric|min:0',
            'montant_min' => 'nullable|numeric|min:0',
            'montant_max' => 'nullable|numeric|min:0',
            'taux' => 'nullable|numeric|min:0',
            'duree' => 'nullable|integer|min:0',
            'nbre_emplois_prevu' => 'nullable|integer|min:0',
            'nbre_beneficiaire_prevu' => 'nullable|integer|min:0',
            'nbre_micro_projet_prevu' => 'nullable|integer|min:0',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $dispositif->update($validation->validated());
            return new JsonResponse([
                'message' => 'Dispositif updated successfully',
                'data' => $dispositif
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating dispositif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $dispositif = Dispositif::findOrFail($id);
        try {
            $dispositif->delete();
            return response()->json(['message' => 'Dispositif deleted successfully'], 204);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Dispositif deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
