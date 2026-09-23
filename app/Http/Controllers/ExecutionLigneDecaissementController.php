<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\ExecutionLigneDecaissement;

class ExecutionLigneDecaissementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $executions = ExecutionLigneDecaissement::with(['ligneDecaissement'])->get();

        if ($request->has('ligne_decaissement_id') && !empty($request->ligne_decaissement_id))
            $executions = $executions->where('ligne_decaissement_id', $request->ligne_decaissement_id);
        if ($request->has('statut') && !empty($request->statut))
            $executions = $executions->where('statut', $request->statut);
        if ($request->has('mode_decaisse') && !empty($request->mode_decaisse))
            $executions = $executions->where('mode_decaisse', $request->mode_decaisse);

        return new JsonResponse([
            'message' => 'Execution ligne decaissements retrieved successfully',
            'data' => $executions
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $execution = ExecutionLigneDecaissement::with(['ligneDecaissement'])->find($id);
        if (!$execution) {
            return new JsonResponse(['message' => 'Execution ligne decaissement not found'], 404);
        }
        return new JsonResponse([
            'message' => 'Execution ligne decaissement retrieved successfully',
            'data' => $execution
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'ligne_decaissement_id' => 'nullable|exists:ligne_decaissements,id',
            'statut' => 'nullable|in:VALIDE,NON_VALIDE',
            'mode_decaisse' => 'nullable|in:CHEQUE,VIREMENT',
            'date_decaisse' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $execution = ExecutionLigneDecaissement::create($validation->validated());
            return new JsonResponse([
                'message' => 'Execution ligne decaissement created successfully',
                'data' => $execution
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating execution ligne decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $execution = ExecutionLigneDecaissement::find($id);
        if (!$execution) {
            return new JsonResponse(['message' => 'Execution ligne decaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'ligne_decaissement_id' => 'nullable|exists:ligne_decaissements,id',
            'statut' => 'nullable|in:VALIDE,NON_VALIDE',
            'mode_decaisse' => 'nullable|in:CHEQUE,VIREMENT',
            'date_decaisse' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $execution->update($validation->validated());
            return new JsonResponse([
                'message' => 'Execution ligne decaissement updated successfully',
                'data' => $execution
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating execution ligne decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $execution = ExecutionLigneDecaissement::find($id);
        if (!$execution) {
            return new JsonResponse(['message' => 'Execution ligne decaissement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'statut' => 'nullable|in:VALIDE,NON_VALIDE',
            'mode_decaisse' => 'nullable|in:CHEQUE,VIREMENT',
            'date_decaisse' => 'nullable|date',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $execution->update(array_filter($validation->validated()));
            return new JsonResponse([
                'message' => 'Execution ligne decaissement patched successfully',
                'data' => $execution
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching execution ligne decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $execution = ExecutionLigneDecaissement::find($id);
        if (!$execution) {
            return new JsonResponse(['message' => 'Execution ligne decaissement not found'], 404);
        }

        try {
            $execution->delete();
            return new JsonResponse([
                'message' => 'Execution ligne decaissement deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting execution ligne decaissement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}