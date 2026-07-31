<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Depense;

class DepenseController extends Controller
{
    public function index(): JsonResponse
    {
        $depenses = Depense::with(['microProjet', 'saisiPar'])->get();
        return new JsonResponse(['Message' => 'Depense list retrieved successfully', 'data' => $depenses], 200);
    }

    public function show($id): JsonResponse
    {
        $depense = Depense::with(['microProjet', 'saisiPar'])->find($id);
        if (!$depense) {
            return new JsonResponse(['Message' => 'Depense not found'], 404);
        }
        return new JsonResponse(['Message' => 'Depense retrieved successfully', 'data' => $depense], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'categorie' => 'required|in:MATERIEL,STOCK,SALAIRE,CHARGE,TRANSPORT,AUTRE',
            'intitule' => 'required|string|max:200',
            'montant_depense' => 'required|numeric',
            'date_depense' => 'required|date',
            'justificatif_path' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'saisi_par' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $depense = Depense::create($request->all());

            return new JsonResponse([
                'message' => 'Depense created successfully',
                'data' => $depense
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating depense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $depense = Depense::find($id);
        if (!$depense) {
            return new JsonResponse(['Message' => 'Depense not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'sometimes|required|exists:micro_projets,id',
            'categorie' => 'sometimes|required|in:MATERIEL,STOCK,SALAIRE,CHARGE,TRANSPORT,AUTRE',
            'intitule' => 'sometimes|required|string|max:200',
            'montant_depense' => 'sometimes|required|numeric',
            'date_depense' => 'sometimes|required|date',
            'justificatif_path' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'saisi_par' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $depense->update($request->all());

            return new JsonResponse([
                'message' => 'Depense updated successfully',
                'data' => $depense
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating depense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $depense = Depense::find($id);
        if (!$depense) {
            return new JsonResponse(['Message' => 'Depense not found'], 404);
        }

        try {
            $depense->delete();
            return new JsonResponse(['Message' => 'Depense deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting depense',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
