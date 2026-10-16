<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstanceHistory;
use App\Models\WorkflowInstance;
use App\Models\WorkflowEtape;
use App\Models\WorkflowEtapeRole;
use App\Models\Role;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\MailService;
use Illuminate\Support\Facades\Log;

class WorkflowInstanceHistoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkflowInstanceHistory::with(['workflowInstance', 'etape', 'actedBy']);
        $filters = ['workflow_instance_id', 'etape_code', 'role_code', 'acted_by'];

        foreach ($filters as $filter) {
            if ($request->has($filter)) $query->where($filter, $request->$filter);
        }
        
        $history = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'message' => 'History retrieved successfully', 
            'data' => $history->values(),
        ]);
    }

    public function show($id): JsonResponse
    {
        $history = WorkflowInstanceHistory::with(['workflowInstance', 'etape', 'actedBy'])->findOrFail($id);
        return response()->json(['message' => 'History retrieved successfully', 'data' => $history]);
    }

    public function store(Request $request, MailService $mailService): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'workflow_instance_id' => 'required|exists:workflow_instance,id',
            'etape_code' => 'required|string|max:50|exists:workflow_etapes,code',
            'role_code' => 'nullable|string|max:50|exists:roles,code',
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
            $etape = WorkflowEtape::where('code', $validation->validated()['etape_code'])->first();
            $history = WorkflowInstanceHistory::create($validation->validated() + ['action' => $etape->name]);
            $history->workflowInstance->update(['current_etape_code' => $validation->validated()['etape_code']]);
            
            if ($etape) {
                $etapeRoles = WorkflowEtapeRole::where('etape_code', $validation->validated()['etape_code'])->get();
                
                foreach ($etapeRoles as $etapeRole) {
                    $role = Role::where('code', $etapeRole->role_code)->first();

                    if ($role) {
                        $personnels = Personnel::where('role_id', $role->id)->where('is_active', true)->get();
                        
                        foreach ($personnels as $personnel) {
                            if ($personnel->email) {
                                $mailService->sendWorkflowAlertEmail($personnel->email, [
                                    'etape' => $etape->name,
                                    'projet' => $history->workflowInstance->microProjet->intitule ?? 'Projet #' . $history->workflowInstance->micro_projet_id,
                                    'action' => $etapeRole->action,
                                ]);
                            }
                        }
                    }
                }
            }

            return response()->json(['message' => 'History created successfully', 'data' => $history], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'History creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request, MailService $mailService): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'histories' => 'required|array|min:1',
            'histories.*.workflow_instance_id' => 'required|exists:workflow_instance,id',
            'histories.*.etape_code' => 'required|string|max:50|exists:workflow_etapes,code',
            'histories.*.role_code' => 'nullable|string|max:50|exists:roles,code',
            'histories.*.comment' => 'nullable|string',
            'histories.*.acted_by' => 'nullable|exists:personnels,id',
            'histories.*.acted_at' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $workflowInstanceIds = array_filter(array_unique(array_column($request->histories, 'workflow_instance_id')));
            $etapeCodes = array_filter(array_unique(array_column($request->histories, 'etape_code')));
            $roleCodes = array_filter(array_unique(array_column($request->histories, 'role_code')));
            $actedByIds = array_filter(array_unique(array_column($request->histories, 'acted_by')));

            $workflowInstances = !empty($workflowInstanceIds) ? WorkflowInstance::whereIn('id', $workflowInstanceIds)->get()->keyBy('id') : collect();
            $etapes = !empty($etapeCodes) ? WorkflowEtape::whereIn('code', $etapeCodes)->get()->keyBy('code') : collect();
            $roles = !empty($roleCodes) ? Role::whereIn('code', $roleCodes)->get()->keyBy('code') : collect();
            $personnels = !empty($actedByIds) ? Personnel::whereIn('id', $actedByIds)->get()->keyBy('id') : collect();

            $histories = [];
            foreach ($request->histories as $historyData) {
                $etape = $etapes->get($historyData['etape_code']);
                $historyData['action'] = $etape ? $etape->name : null;

                $history = WorkflowInstanceHistory::create($historyData);
                $histories[] = $history;

                // Update workflow instance current etape
                $workflowInstance = $workflowInstances->get($historyData['workflow_instance_id']);
                if ($workflowInstance) {
                    $workflowInstance->update(['current_etape_code' => $historyData['etape_code']]);
                }

                // Send email notifications if etape has roles
                if ($etape && isset($historyData['role_code'])) {
                    $role = $roles->get($historyData['role_code']);
                    if ($role) {
                        $personnelsInRole = Personnel::where('role_id', $role->id)->where('is_active', true)->get();
                        foreach ($personnelsInRole as $personnel) {
                            if ($personnel->email) {
                                $mailService->sendWorkflowAlertEmail($personnel->email, [
                                    'etape' => $etape->name,
                                    'projet' => $workflowInstance ? ($workflowInstance->microProjet->intitule ?? 'Projet #' . $workflowInstance->micro_projet_id) : 'Projet #' . $historyData['workflow_instance_id'],
                                    'action' => 'Multiple workflow history update',
                                ]);
                            }
                        }
                    }
                }
            }

            return response()->json([
                'message' => 'Histories created successfully',
                'data' => $histories,
                'total' => count($histories)
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error creating histories',
                'error' => $th->getMessage()
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
