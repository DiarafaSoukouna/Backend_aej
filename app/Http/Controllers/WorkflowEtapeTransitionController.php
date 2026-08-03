<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkflowEtapeTransition;

class WorkflowEtapeTransitionController extends Controller
{
    public function index(): JsonResponse
    {
        $transitions = WorkflowEtapeTransition::with(['version', 'fromEtape', 'toEtape'])->get();
        return new JsonResponse(['Message' => 'Workflow etape transition list retrieved successfully', 'data' => $transitions], 200);
    }

    public function show($id): JsonResponse
    {
        $transition = WorkflowEtapeTransition::with(['version', 'fromEtape', 'toEtape'])->find($id);
        if (!$transition) {
            return new JsonResponse(['Message' => 'Workflow etape transition not found'], 404);
        }
        return new JsonResponse(['Message' => 'Workflow etape transition retrieved successfully', 'data' => $transition], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'version' => 'required|exists:workflow_versions,version',
            'from_etape_id' => 'required|exists:workflow_etapes,id',
            'to_etape_id' => 'required|exists:workflow_etapes,id|different:from_etape_id',
            'transition_type' => 'required|in:NEXT,RETURN,PARALLEL,MERGE,CANCEL,END',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $transition = WorkflowEtapeTransition::create($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape transition created successfully',
                'data' => $transition
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating workflow etape transition',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $transition = WorkflowEtapeTransition::find($id);
        if (!$transition) {
            return new JsonResponse(['Message' => 'Workflow etape transition not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'version' => 'sometimes|required|exists:workflow_versions,version',
            'from_etape_id' => 'sometimes|required|exists:workflow_etapes,id',
            'to_etape_id' => 'sometimes|required|exists:workflow_etapes,id|different:from_etape_id',
            'transition_type' => 'sometimes|required|in:NEXT,RETURN,PARALLEL,MERGE,CANCEL,END',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $transition->update($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape transition updated successfully',
                'data' => $transition
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating workflow etape transition',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $transition = WorkflowEtapeTransition::find($id);
        if (!$transition) {
            return new JsonResponse(['Message' => 'Workflow etape transition not found'], 404);
        }

        try {
            $transition->delete();
            return new JsonResponse(['Message' => 'Workflow etape transition deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting workflow etape transition',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
