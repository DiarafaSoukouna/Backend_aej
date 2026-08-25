<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstanceHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
        $validation = Validator::make($request->all(), [
            'workflow_instance_id' => 'required|exists:workflow_instance,id',
            'etape_code' => 'required|string|max:50|exists:workflow_etapes,code',
            'role_code' => 'nullable|string|max:50|exists:roles,code',
            'action' => 'required|string|max:50',
            'comment' => 'nullable|string',
            'acted_by' => 'nullable|exists:personnels,id',
            'acted_at' => 'nullable|date',
            'comments' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $history = WorkflowInstanceHistory::create($validation->validated());
            $history->workflowInstance->update(['current_etape_code' => $validation->validated()['etape_code']]);

            return response()->json(['message' => 'History created successfully', 'data' => $history], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'History creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $history = WorkflowInstanceHistory::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'workflow_instance_id' => 'nullable|exists:workflow_instance,id',
            'etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'role_code' => 'nullable|string|max:50|exists:roles,code',
            'action' => 'nullable|string|max:50',
            'comment' => 'nullable|string',
            'acted_by' => 'nullable|exists:personnels,id',
            'acted_at' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $history->update($validation->validated());
            if (isset($validation->validated()['etape_code'])) {
                $history->workflowInstance->update(['current_etape_code' => $validation->validated()['etape_code']]);
            }

            return response()->json(['message' => 'History updated successfully', 'data' => $history]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'History update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $history = WorkflowInstanceHistory::findOrFail($id);
        try {
            $history->delete();
            return response()->json(['message' => 'History deleted successfully'], 204);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'History deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
