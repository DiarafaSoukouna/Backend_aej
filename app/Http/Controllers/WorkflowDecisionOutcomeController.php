<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkflowDecisionOutcome;

class WorkflowDecisionOutcomeController extends Controller
{
    public function index(): JsonResponse
    {
        $outcomes = WorkflowDecisionOutcome::with(['decision', 'nextEtape'])->get();
        return new JsonResponse(['Message' => 'Workflow decision outcome list retrieved successfully', 'data' => $outcomes], 200);
    }

    public function show($id): JsonResponse
    {
        $outcome = WorkflowDecisionOutcome::with(['decision', 'nextEtape'])->find($id);
        if (!$outcome) {
            return new JsonResponse(['Message' => 'Workflow decision outcome not found'], 404);
        }
        return new JsonResponse(['Message' => 'Workflow decision outcome retrieved successfully', 'data' => $outcome], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'decision_id' => 'required|exists:workflow_etapes_decision,id',
            'code' => 'required|string|max:30',
            'label' => 'required|string|max:150',
            'next_etape_id' => 'nullable|exists:workflow_etapes,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $outcome = WorkflowDecisionOutcome::create($request->all());

            return new JsonResponse([
                'message' => 'Workflow decision outcome created successfully',
                'data' => $outcome
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating workflow decision outcome',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $outcome = WorkflowDecisionOutcome::find($id);
        if (!$outcome) {
            return new JsonResponse(['Message' => 'Workflow decision outcome not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'decision_id' => 'sometimes|required|exists:workflow_etapes_decision,id',
            'code' => 'sometimes|required|string|max:30',
            'label' => 'sometimes|required|string|max:150',
            'next_etape_id' => 'nullable|exists:workflow_etapes,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $outcome->update($request->all());

            return new JsonResponse([
                'message' => 'Workflow decision outcome updated successfully',
                'data' => $outcome
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating workflow decision outcome',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $outcome = WorkflowDecisionOutcome::find($id);
        if (!$outcome) {
            return new JsonResponse(['Message' => 'Workflow decision outcome not found'], 404);
        }

        try {
            $outcome->delete();
            return new JsonResponse(['Message' => 'Workflow decision outcome deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting workflow decision outcome',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
