<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WorkflowInstanceController extends Controller
{
    public function index(): JsonResponse
    {
        $instances = WorkflowInstance::with(['microProjet', 'currentEtape', 'nextEtape', 'history', 'deliverables', 'comments'])->get();
        return response()->json(['message' => 'Instances retrieved successfully', 'data' => $instances]);
    }

    public function show($id): JsonResponse
    {
        $instance = WorkflowInstance::with(['microProjet', 'currentEtape', 'nextEtape', 'history', 'deliverables', 'comments'])->findOrFail($id);
        return response()->json(['message' => 'Instance retrieved successfully', 'data' => $instance]);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'workflow_version' => 'required|string|max:20',
            'current_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'next_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'statut' => 'required|in:EN_COURS,TERMINE,REJETE,ABANDONNE',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after:started_at',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        $instance = WorkflowInstance::create($validation->validated());
        return response()->json(['message' => 'Instance created successfully', 'data' => $instance], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'workflow_version' => 'nullable|exists:workflow_versions,code',
            'current_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'next_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'statut' => 'nullable|in:EN_COURS,TERMINE,REJETE,ABANDONNE',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after:started_at',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $instance->update($validation->validated());
            return response()->json(['message' => 'Instance updated successfully', 'data' => $instance]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Instance updating failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'current_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'next_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'statut' => 'nullable|in:EN_COURS,TERMINE,REJETE,ABANDONNE',
            'completed_at' => 'nullable|date|after:started_at',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $instance->update(array_filter($validation->validated()));
            return response()->json(['message' => 'Instance patched successfully', 'data' => $instance]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Instance patching failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($id);
        try {
            $instance->delete();
            return response()->json(['message' => 'Instance deleted successfully'], 204);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Instance deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
