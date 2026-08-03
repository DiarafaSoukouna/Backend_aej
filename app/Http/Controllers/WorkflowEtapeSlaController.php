<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkflowEtapeSla;

class WorkflowEtapeSlaController extends Controller
{
    public function index(): JsonResponse
    {
        $slas = WorkflowEtapeSla::with('etape')->get();
        return new JsonResponse(['Message' => 'Workflow etape SLA list retrieved successfully', 'data' => $slas], 200);
    }

    public function show($id): JsonResponse
    {
        $sla = WorkflowEtapeSla::with('etape')->find($id);
        if (!$sla) {
            return new JsonResponse(['Message' => 'Workflow etape SLA not found'], 404);
        }
        return new JsonResponse(['Message' => 'Workflow etape SLA retrieved successfully', 'data' => $sla], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'etape_code' => 'required|exists:workflow_etapes,code',
            'duration_value' => 'required|integer',
            'duration_unit' => 'required|in:HEURES,JOURS,SEMAINES,MOIS',
            'delay_type' => 'required|in:FIXE,RELATIF',
            'description' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $sla = WorkflowEtapeSla::create($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape SLA created successfully',
                'data' => $sla
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating workflow etape SLA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $sla = WorkflowEtapeSla::find($id);
        if (!$sla) {
            return new JsonResponse(['Message' => 'Workflow etape SLA not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'etape_code' => 'sometimes|required|exists:workflow_etapes,code',
            'duration_value' => 'sometimes|required|integer',
            'duration_unit' => 'sometimes|required|in:HEURES,JOURS,SEMAINES,MOIS',
            'delay_type' => 'sometimes|required|in:FIXE,RELATIF',
            'description' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $sla->update($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape SLA updated successfully',
                'data' => $sla
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating workflow etape SLA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $sla = WorkflowEtapeSla::find($id);
        if (!$sla) {
            return new JsonResponse(['Message' => 'Workflow etape SLA not found'], 404);
        }

        try {
            $sla->delete();
            return new JsonResponse(['Message' => 'Workflow etape SLA deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting workflow etape SLA',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
