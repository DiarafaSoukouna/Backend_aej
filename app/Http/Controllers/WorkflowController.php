<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Workflow;

class WorkflowController extends Controller
{
    public function index(): JsonResponse
    {
        $workflows = Workflow::with('versions', 'guichets')->get();
        return new JsonResponse(['Message' => 'Workflow list retrieved successfully', 'data' => $workflows], 200);
    }

    public function show($id): JsonResponse
    {
        $workflow = Workflow::with('versions', 'guichets')->find($id);
        if (!$workflow) {
            return new JsonResponse(['Message' => 'Workflow not found'], 404);
        }
        return new JsonResponse(['Message' => 'Workflow retrieved successfully', 'data' => $workflow], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:20|unique:workflows',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $workflow = Workflow::create($request->all());

            return new JsonResponse([
                'message' => 'Workflow created successfully',
                'data' => $workflow
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $workflow = Workflow::find($id);
        if (!$workflow) {
            return new JsonResponse(['Message' => 'Workflow not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:20|unique:workflows,code,' . $id,
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $workflow->update($request->all());

            return new JsonResponse([
                'message' => 'Workflow updated successfully',
                'data' => $workflow
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $workflow = Workflow::find($id);
        if (!$workflow) {
            return new JsonResponse(['Message' => 'Workflow not found'], 404);
        }

        try {
            $workflow->delete();
            return new JsonResponse(['Message' => 'Workflow deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
