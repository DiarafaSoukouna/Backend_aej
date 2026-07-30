<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkflowEtape;

class WorkflowEtapeController extends Controller
{
    public function index(): JsonResponse
    {
        $etapes = WorkflowEtape::with(['version', 'parentEtape', 'sla', 'deliverables', 'roles', 'decision', 'children'])->get();
        return new JsonResponse(['Message' => 'Workflow etape list retrieved successfully', 'data' => $etapes], 200);
    }

    public function show($id): JsonResponse
    {
        $etape = WorkflowEtape::with(['version', 'parentEtape', 'sla', 'deliverables', 'roles', 'decision', 'children'])->find($id);
        if (!$etape) {
            return new JsonResponse(['Message' => 'Workflow etape not found'], 404);
        }
        return new JsonResponse(['Message' => 'Workflow etape retrieved successfully', 'data' => $etape], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'version' => 'required|exists:workflow_versions,version',
            'parent_etape_code' => 'nullable|exists:workflow_etapes,code',
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:200',
            'impact' => 'nullable|in:EN_SOUMISSION,EN_COURS,EN_ANALYSE,EN_FORMATION,EN_FINANCEMENT,EN_DECAISSEMENT,EN_SUIVI,EN_REMBOURSEMENT,EN_EVALUATION,TERMINE',
            'statut' => 'nullable|in:OUI,NON',
            'description' => 'nullable|string',
            'sequence_order' => 'required|integer',
            'is_active' => 'boolean',
            'valid_from' => 'date',
            'valid_to' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $etape = WorkflowEtape::create($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape created successfully',
                'data' => $etape
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating workflow etape',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $etape = WorkflowEtape::find($id);
        if (!$etape) {
            return new JsonResponse(['Message' => 'Workflow etape not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'version' => 'sometimes|required|exists:workflow_versions,version',
            'parent_etape_code' => 'nullable|exists:workflow_etapes,code',
            'code' => 'sometimes|required|string|max:30',
            'name' => 'sometimes|required|string|max:200',
            'impact' => 'nullable|in:EN_SOUMISSION,EN_COURS,EN_ANALYSE,EN_FORMATION,EN_FINANCEMENT,EN_DECAISSEMENT,EN_SUIVI,EN_REMBOURSEMENT,EN_EVALUATION,TERMINE',
            'statut' => 'nullable|in:OUI,NON',
            'description' => 'nullable|string',
            'sequence_order' => 'sometimes|required|integer',
            'is_active' => 'boolean',
            'valid_from' => 'date',
            'valid_to' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $etape->update($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape updated successfully',
                'data' => $etape
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating workflow etape',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $etape = WorkflowEtape::find($id);
        if (!$etape) {
            return new JsonResponse(['Message' => 'Workflow etape not found'], 404);
        }

        try {
            $etape->delete();
            return new JsonResponse(['Message' => 'Workflow etape deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting workflow etape',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
