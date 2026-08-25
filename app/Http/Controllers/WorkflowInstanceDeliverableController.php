<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstanceDeliverable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WorkflowInstanceDeliverableController extends Controller
{
    public function index(): JsonResponse
    {
        $deliverables = WorkflowInstanceDeliverable::with(['workflowInstance', 'deliverable', 'producedBy'])->get();
        return response()->json(['message' => 'Deliverables retrieved successfully', 'data' => $deliverables]);
    }

    public function show($id): JsonResponse
    {
        $deliverable = WorkflowInstanceDeliverable::with(['workflowInstance', 'deliverable', 'producedBy'])->findOrFail($id);
        return response()->json(['message' => 'Deliverable retrieved successfully', 'data' => $deliverable]);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'workflow_instance_id' => 'required|exists:workflow_instance,id',
            'deliverable_code' => 'required|exists:workflow_deliverables,code',
            'file_path' => 'required|string',
            'file_name' => 'required|string|max:255',
            'file_size' => 'required|integer',
            'file_type' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
            'produced_at' => 'nullable|date',
            'produced_by_id' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $deliverable = WorkflowInstanceDeliverable::create($validation->validated());
            return response()->json(['message' => 'Deliverable created successfully', 'data' => $deliverable], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Deliverable creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }

    }

    public function update(Request $request, $id): JsonResponse
    {
        $deliverable = WorkflowInstanceDeliverable::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'workflow_instance_id' => 'nullable|exists:workflow_instance,id',
            'deliverable_code' => 'nullable|exists:workflow_deliverables,code',
            'file_path' => 'nullable|string',
            'file_name' => 'nullable|string|max:255',
            'file_size' => 'nullable|integer',
            'file_type' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
            'produced_at' => 'nullable|date',
            'produced_by_id' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $deliverable->update($validation->validated());
            return response()->json(['message' => 'Deliverable updated successfully', 'data' => $deliverable]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Deliverable updating failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $deliverable = WorkflowInstanceDeliverable::findOrFail($id);
        try {
            $deliverable->delete();
            return response()->json(['message' => 'Deliverable deleted successfully'], 204);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Deliverable deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
