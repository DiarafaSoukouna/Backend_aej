<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstanceHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkflowInstanceHistoryController extends Controller
{
    public function index(): JsonResponse
    {
        $history = WorkflowInstanceHistory::with(['workflowInstance', 'etape', 'actedBy'])->get();
        return response()->json(['message' => 'History retrieved successfully', 'data' => $history]);
    }

    public function show($id): JsonResponse
    {
        $history = WorkflowInstanceHistory::with(['workflowInstance', 'etape', 'actedBy'])->findOrFail($id);
        return response()->json(['message' => 'History retrieved successfully', 'data' => $history]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'workflow_instance_id' => 'required|exists:workflow_instance,id',
            'etape_code' => 'required|string|max:50|exists:workflow_etapes,code',
            'role_code' => 'nullable|string|max:50|exists:roles,code',
            'action' => 'required|string|max:50',
            'comment' => 'nullable|string',
            'acted_by' => 'nullable|exists:personnels,id',
            'acted_at' => 'nullable|date',
            'comments' => 'nullable|string',
        ]);

        $history = WorkflowInstanceHistory::create($validated);
        $history->workflowInstance->update(['current_etape_code' => $validated['etape_code']]);

        return response()->json(['message' => 'History created successfully', 'data' => $history], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $history = WorkflowInstanceHistory::findOrFail($id);

        $validated = $request->validate([
            'workflow_instance_id' => 'nullable|exists:workflow_instance,id',
            'etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'role_code' => 'nullable|string|max:50|exists:roles,code',
            'action' => 'nullable|string|max:50',
            'comment' => 'nullable|string',
            'acted_by' => 'nullable|exists:personnels,id',
            'acted_at' => 'nullable|date',
        ]);

        $history->update($validated);
        if (isset($validated['etape_code'])) {
            $history->workflowInstance->update(['current_etape_code' => $validated['etape_code']]);
        }

        return response()->json(['message' => 'History updated successfully', 'data' => $history]);
    }

    public function destroy($id): JsonResponse
    {
        $history = WorkflowInstanceHistory::findOrFail($id);
        $history->delete();
        return response()->json(['message' => 'History deleted successfully'], 204);
    }
}
