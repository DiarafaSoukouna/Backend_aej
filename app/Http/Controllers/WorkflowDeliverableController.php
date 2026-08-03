<?php

namespace App\Http\Controllers;

use App\Models\WorkflowDeliverable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowDeliverableController extends Controller
{
    public function index(): JsonResponse
    {
        $deliverables = WorkflowDeliverable::all();
        return new JsonResponse([
            'message' => 'Deliverables retrieved successfully',
            'data' => $deliverables
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:workflow_deliverables,code',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $deliverable = WorkflowDeliverable::create($validated);

        return new JsonResponse([
            'message' => 'Deliverable created successfully',
            'data' => $deliverable
        ], 201);
    }

    public function show(WorkflowDeliverable $workflowDeliverable): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Deliverable retrieved successfully',
            'data' => $workflowDeliverable
        ], 200);
    }

    public function update(Request $request, WorkflowDeliverable $workflowDeliverable): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:50|unique:workflow_deliverables,code,' . $workflowDeliverable->id,
            'name' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $workflowDeliverable->update($validated);

        return new JsonResponse([
            'message' => 'Deliverable updated successfully',
            'data' => $workflowDeliverable
        ], 200);
    }

    public function destroy(WorkflowDeliverable $workflowDeliverable): JsonResponse
    {
        $workflowDeliverable->delete();

        return new JsonResponse([
            'message' => 'Deliverable deleted successfully'
        ], 200);
    }
}
