<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkflowEtapeDeliverable;

class WorkflowEtapeDeliverableController extends Controller
{
    public function index(): JsonResponse
    {
        $deliverables = WorkflowEtapeDeliverable::with('etape')->get();
        return new JsonResponse(['Message' => 'Workflow etape deliverable list retrieved successfully', 'data' => $deliverables], 200);
    }

    public function show($id): JsonResponse
    {
        $deliverable = WorkflowEtapeDeliverable::with('etape')->find($id);
        if (!$deliverable) {
            return new JsonResponse(['Message' => 'Workflow etape deliverable not found'], 404);
        }
        return new JsonResponse(['Message' => 'Workflow etape deliverable retrieved successfully', 'data' => $deliverable], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'etape_code' => 'required|exists:workflow_etapes,code',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_mandatory' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $deliverable = WorkflowEtapeDeliverable::create($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape deliverable created successfully',
                'data' => $deliverable
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating workflow etape deliverable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $deliverable = WorkflowEtapeDeliverable::find($id);
        if (!$deliverable) {
            return new JsonResponse(['Message' => 'Workflow etape deliverable not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'etape_code' => 'sometimes|required|exists:workflow_etapes,code',
            'name' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'is_mandatory' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $deliverable->update($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape deliverable updated successfully',
                'data' => $deliverable
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating workflow etape deliverable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $deliverable = WorkflowEtapeDeliverable::find($id);
        if (!$deliverable) {
            return new JsonResponse(['Message' => 'Workflow etape deliverable not found'], 404);
        }

        try {
            $deliverable->delete();
            return new JsonResponse(['Message' => 'Workflow etape deliverable deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting workflow etape deliverable',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
