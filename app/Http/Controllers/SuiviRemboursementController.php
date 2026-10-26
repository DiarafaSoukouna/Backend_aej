<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuiviRemboursement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SuiviRemboursementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $suiviRemboursements = SuiviRemboursement::with(['amortissement']);

        if ($request->has('amortissement_id') && !empty($request->amortissement_id))
            $suiviRemboursements = $suiviRemboursements->where('amortissement_id', $request->amortissement_id);

        $suiviRemboursementData = $suiviRemboursements->paginate($request->get('per_page', 20));

        return new JsonResponse([
            'message' => 'Suivi remboursements retrieved successfully',
            'data' => $suiviRemboursementData,
            'pagination' => [
                'current_page' => $suiviRemboursementData->currentPage(),
                'last_page' => $suiviRemboursementData->lastPage(),
                'per_page' => $suiviRemboursementData->perPage(),
                'total' => $suiviRemboursementData->total(),
            ]
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $suiviRemboursement = SuiviRemboursement::with(['amortissement'])->find($id);
        if (!$suiviRemboursement) {
            return new JsonResponse(['Message' => 'Suivi remboursement not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Suivi remboursement retrieved successfully',
            'data' => $suiviRemboursement
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'amortissement_id' => 'required|exists:tableau_amortissements,id',
            'montant_paye' => 'nullable|numeric',
            'date_paiement' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $suiviRemboursement = SuiviRemboursement::create($validation->validated());
            return new JsonResponse([
                'message' => 'Suivi remboursement created successfully',
                'data' => $suiviRemboursement
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating suivi remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $suiviRemboursement = SuiviRemboursement::find($id);
        if (!$suiviRemboursement) {
            return new JsonResponse(['Message' => 'Suivi remboursement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'amortissement_id' => 'nullable|exists:tableau_amortissements,id',
            'montant_paye' => 'nullable|numeric',
            'date_paiement' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $suiviRemboursement->update($validation->validated());
            return new JsonResponse([
                'message' => 'Suivi remboursement updated successfully',
                'data' => $suiviRemboursement
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating suivi remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $suiviRemboursement = SuiviRemboursement::find($id);
        if (!$suiviRemboursement) {
            return new JsonResponse(['Message' => 'Suivi remboursement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'amortissement_id' => 'nullable|exists:tableau_amortissements,id',
            'montant_paye' => 'nullable|numeric',
            'date_paiement' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $suiviRemboursement->update(array_filter($validation->validated()));
            return new JsonResponse([
                'message' => 'Suivi remboursement patched successfully',
                'data' => $suiviRemboursement
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching suivi remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $suiviRemboursement = SuiviRemboursement::find($id);
        if (!$suiviRemboursement) {
            return new JsonResponse(['Message' => 'Suivi remboursement not found'], 404);
        }

        try {
            $suiviRemboursement->delete();
            return new JsonResponse([
                'message' => 'Suivi remboursement deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting suivi remboursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
