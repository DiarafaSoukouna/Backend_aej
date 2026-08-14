<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        $validated = $request->validate([
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'workflow_version' => 'required|string|max:20',
            'current_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'next_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'status' => 'required|in:EN_COURS,TERMINE,REJETE,ABANDONNE',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after:started_at',
        ]);

        $instance = WorkflowInstance::create($validated);
        return response()->json(['message' => 'Instance created successfully', 'data' => $instance], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($id);

        $validated = $request->validate([
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'workflow_version' => 'nullable|exists:workflow_versions,code',
            'current_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'next_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'status' => 'nullable|in:EN_COURS,TERMINE,REJETE,ABANDONNE',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after:started_at',
        ]);

        $instance->update($validated);
        return response()->json(['message' => 'Instance updated successfully', 'data' => $instance]);
    }

    public function destroy($id): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($id);
        $instance->delete();
        return response()->json(['message' => 'Instance deleted successfully'], 204);
    }
}
