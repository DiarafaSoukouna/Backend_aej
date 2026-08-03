<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkflowEtapeDecision;

class WorkflowEtapeDecisionController extends Controller
{
    public function index(): JsonResponse
    {
        $decisions = WorkflowEtapeDecision::with('etape')->get();
        return new JsonResponse(['Message' => 'Workflow etape decision list retrieved successfully', 'data' => $decisions], 200);
    }

    public function show($id): JsonResponse
    {
        $decision = WorkflowEtapeDecision::with('etape')->find($id);
        if (!$decision) {
            return new JsonResponse(['Message' => 'Workflow etape decision not found'], 404);
        }
        return new JsonResponse(['Message' => 'Workflow etape decision retrieved successfully', 'data' => $decision], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'etape_code' => 'required|exists:workflow_etapes,code',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $decision = WorkflowEtapeDecision::create($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape decision created successfully',
                'data' => $decision
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating workflow etape decision',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $decision = WorkflowEtapeDecision::find($id);
        if (!$decision) {
            return new JsonResponse(['Message' => 'Workflow etape decision not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'etape_code' => 'sometimes|required|exists:workflow_etapes,code',
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $decision->update($request->all());

            return new JsonResponse([
                'message' => 'Workflow etape decision updated successfully',
                'data' => $decision
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating workflow etape decision',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $decision = WorkflowEtapeDecision::find($id);
        if (!$decision) {
            return new JsonResponse(['Message' => 'Workflow etape decision not found'], 404);
        }

        try {
            $decision->delete();
            return new JsonResponse(['Message' => 'Workflow etape decision deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting workflow etape decision',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
