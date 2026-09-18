<?php

namespace App\Http\Controllers;

use App\Models\WorkflowRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;

class WorkflowRoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = WorkflowRole::all();
        return new JsonResponse([
            'message' => 'Roles retrieved successfully',
            'data' => $roles
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:workflow_roles,code',
            'name' => 'required|string|max:150',
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
            $role = WorkflowRole::create($validation->validated());

            return new JsonResponse([
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Role creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function show(WorkflowRole $workflowRole): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Role retrieved successfully',
            'data' => $workflowRole
        ], 200);
    }

    public function update(Request $request, WorkflowRole $workflowRole): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:50|unique:workflow_roles,code,' . $workflowRole->id,
            'name' => 'sometimes|required|string|max:150',
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
            $workflowRole->update($validation->validated());

            return new JsonResponse([
                'message' => 'Role updated successfully',
                'data' => $workflowRole
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Role update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(WorkflowRole $workflowRole): JsonResponse
    {
        try {
            $workflowRole->delete();

            return new JsonResponse([
                'message' => 'Role deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Role deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
