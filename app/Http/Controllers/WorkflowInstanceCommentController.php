<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstanceComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkflowInstanceCommentController extends Controller
{
    public function index(): JsonResponse
    {
        $comments = WorkflowInstanceComment::with(['workflowInstance', 'etape', 'commentedBy'])->get();
        return response()->json(['message' => 'Comments retrieved successfully', 'data' => $comments]);
    }

    public function show($id): JsonResponse
    {
        $comment = WorkflowInstanceComment::with(['workflowInstance', 'etape', 'commentedBy'])->findOrFail($id);
        return response()->json(['message' => 'Comment retrieved successfully', 'data' => $comment]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'workflow_instance_id' => 'required|exists:workflow_instance,id',
            'etape_code' => 'required|string|max:50|exists:workflow_etapes,code',
            'commented_by_id' => 'nullable|exists:personnels,id',
            'comment' => 'required|string',
            'created_at' => 'nullable|date',
        ]);

        $comment = WorkflowInstanceComment::create($validated);
        return response()->json(['message' => 'Comment created successfully', 'data' => $comment], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $comment = WorkflowInstanceComment::findOrFail($id);

        $validated = $request->validate([
            'workflow_instance_id' => 'nullable|exists:workflow_instance,id',
            'etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'commented_by_id' => 'nullable|exists:personnels,id',
            'comment' => 'nullable|string',
        ]);

        $comment->update($validated);
        return response()->json(['message' => 'Comment updated successfully', 'data' => $comment]);
    }

    public function destroy($id): JsonResponse
    {
        $comment = WorkflowInstanceComment::findOrFail($id);
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully'], 204);
    }
}
