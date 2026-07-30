<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkflowEtapeRole;

class WorkflowEtapeRoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = WorkflowEtapeRole::with(['etape', 'role'])->get();
        return new JsonResponse(['Message' => 'Workflow etape role list retrieved successfully', 'data' => $roles], 200);
    }

    public function show($id): JsonResponse
    {
        $role = WorkflowEtapeRole::with(['etape', 'role'])->find($id);
        if (!$role) {
            return new JsonResponse(['Message' => 'Workflow etape role not found'], 404);
        }
        return new JsonResponse(['Message' => 'Workflow etape role retrieved successfully', 'data' => $role], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'etape_code' => 'required|exists:workflow_etapes,code',
            'role_code' => 'required|exists:roles,code',
            'responsibility' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $role = WorkflowEtapeRole::create($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape role created successfully',
                'data' => $role
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating workflow etape role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $role = WorkflowEtapeRole::find($id);
        if (!$role) {
            return new JsonResponse(['Message' => 'Workflow etape role not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'etape_code' => 'sometimes|required|exists:workflow_etapes,code',
            'role_code' => 'sometimes|required|exists:roles,code',
            'responsibility' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $role->update($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape role updated successfully',
                'data' => $role
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating workflow etape role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $role = WorkflowEtapeRole::find($id);
        if (!$role) {
            return new JsonResponse(['Message' => 'Workflow etape role not found'], 404);
        }

        try {
            $role->delete();
            return new JsonResponse(['Message' => 'Workflow etape role deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting workflow etape role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
