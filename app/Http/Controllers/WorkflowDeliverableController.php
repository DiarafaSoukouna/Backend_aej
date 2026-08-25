<?php

namespace App\Http\Controllers;

use App\Models\WorkflowDeliverable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:workflow_deliverables,code',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $deliverable = WorkflowDeliverable::create($validation->validated());
            return new JsonResponse([
                'message' => 'Deliverable created successfully',
                'data' => $deliverable
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Deliverable creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
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
        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:50|unique:workflow_deliverables,code,' . $workflowDeliverable->id,
            'name' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $workflowDeliverable->update($validation->validated());
            return new JsonResponse([
                'message' => 'Deliverable updated successfully',
                'data' => $workflowDeliverable
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Deliverable update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(WorkflowDeliverable $workflowDeliverable): JsonResponse
    {
        try {
            $workflowDeliverable->delete();
            return new JsonResponse([
                'message' => 'Deliverable deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Deliverable deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
