<?php

namespace App\Http\Controllers;

use App\Models\WorkflowRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


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
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:workflow_roles,code',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $role = WorkflowRole::create($validated);

        return new JsonResponse([
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
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
        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:50|unique:workflow_roles,code,' . $workflowRole->id,
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $workflowRole->update($validated);

        return new JsonResponse([
            'message' => 'Role updated successfully',
            'data' => $workflowRole
        ], 200);
    }

    public function destroy(WorkflowRole $workflowRole): JsonResponse
    {
        $workflowRole->delete();

        return new JsonResponse([
            'message' => 'Role deleted successfully'
        ], 200);
    }
}
