<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstanceComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WorkflowInstanceCommentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkflowInstanceComment::with(['workflowInstance', 'etape', 'commentedBy']);
        $filters = ['workflow_instance_id', 'etape_code', 'commented_by_id'];

        foreach ($filters as $filter) {
            if ($request->has($filter)) $query->where($filter, $request->$filter);
        }

        $comments = $query->get();
        return response()->json(['message' => 'Comments retrieved successfully', 'data' => $comments]);
    }

    public function show($id): JsonResponse
    {
        $comment = WorkflowInstanceComment::with(['workflowInstance', 'etape', 'commentedBy'])->findOrFail($id);
        return response()->json(['message' => 'Comment retrieved successfully', 'data' => $comment]);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'workflow_instance_id' => 'required|exists:workflow_instance,id',
            'etape_code' => 'required|string|max:50|exists:workflow_etapes,code',
            'commented_by_id' => 'nullable|exists:personnels,id',
            'comment' => 'required|string',
            'created_at' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $comment = WorkflowInstanceComment::create($validation->validated());
            return response()->json(['message' => 'Comment created successfully', 'data' => $comment], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Comment creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'comments' => 'required|array|min:1',
            'comments.*.workflow_instance_id' => 'required|exists:workflow_instance,id',
            'comments.*.etape_code' => 'required|string|max:50|exists:workflow_etapes,code',
            'comments.*.commented_by_id' => 'nullable|exists:personnels,id',
            'comments.*.comment' => 'required|string',
            'comments.*.created_at' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $comments = [];
            foreach ($request->comments as $commentData) {
                $comment = WorkflowInstanceComment::create($commentData);
                $comments[] = $comment;
            }

            return response()->json([
                'message' => 'Comments created successfully',
                'data' => $comments,
                'total' => count($comments)
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error creating comments',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $comment = WorkflowInstanceComment::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'workflow_instance_id' => 'nullable|exists:workflow_instance,id',
            'etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'commented_by_id' => 'nullable|exists:personnels,id',
            'comment' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $comment->update($validation->validated());
            return response()->json(['message' => 'Comment updated successfully', 'data' => $comment]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Comment update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $comment = WorkflowInstanceComment::findOrFail($id);
        try {
            $comment->delete();
            return response()->json(['message' => 'Comment deleted successfully'], 204);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Comment deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
