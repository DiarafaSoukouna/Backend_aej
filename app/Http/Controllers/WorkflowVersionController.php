<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkflowVersion;

class WorkflowVersionController extends Controller
{
    public function index(): JsonResponse
    {
        $versions = WorkflowVersion::with(['workflow', 'etapes'])->get();
        return new JsonResponse(['Message' => 'Workflow version list retrieved successfully', 'data' => $versions], 200);
    }

    public function show($id): JsonResponse
    {
        $version = WorkflowVersion::with(['workflow', 'etapes'])->find($id);
        if (!$version) {
            return new JsonResponse(['Message' => 'Workflow version not found'], 404);
        }
        return new JsonResponse(['Message' => 'Workflow version retrieved successfully', 'data' => $version], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'workflow_code' => 'required|exists:workflows,code',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'version' => 'required|string|max:20',
            'code' => 'nullable|string|max:50|unique:workflow_versions,code',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            if (!isset($data['code']) || empty($data['code'])) $data['code'] = $data['workflow_code'] . '_' . $data['version'];
            $version = WorkflowVersion::create($data);

            return new JsonResponse([
                'message' => 'Workflow version created successfully',
                'data' => $version
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating workflow version',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $version = WorkflowVersion::find($id);
        if (!$version) {
            return new JsonResponse(['Message' => 'Workflow version not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'workflow_code' => 'sometimes|required|exists:workflows,code',
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'version' => 'sometimes|required|string|max:20',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $version->update($request->all());

            return new JsonResponse([
                'message' => 'Workflow version updated successfully',
                'data' => $version
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating workflow version',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $version = WorkflowVersion::find($id);
        if (!$version) {
            return new JsonResponse(['Message' => 'Workflow version not found'], 404);
        }

        try {
            $version->delete();
            return new JsonResponse(['Message' => 'Workflow version deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting workflow version',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
