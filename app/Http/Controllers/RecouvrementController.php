<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Recouvrement;

class RecouvrementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $recouvrements = Recouvrement::with(['microProjet', 'planRemboursement', 'agent'])->get();

        if ($request->has('micro_projet_id') && !empty($request->micro_projet_id)) 
            $recouvrements = $recouvrements->where('micro_projet_id', $request->micro_projet_id);
        if ($request->has('plan_remboursement_id') && !empty($request->plan_remboursement_id)) 
            $recouvrements = $recouvrements->where('plan_remboursement_id', $request->plan_remboursement_id);
        if ($request->has('agent_id') && !empty($request->agent_id)) 
            $recouvrements = $recouvrements->where('agent_id', $request->agent_id);
        if ($request->has('type_action') && !empty($request->type_action)) 
            $recouvrements = $recouvrements->where('type_action', $request->type_action);

        return new JsonResponse(['message' => 'Recouvrements retrieved successfully', 'data' => $recouvrements], 200);
    }

    public function show($id): JsonResponse
    {
        $recouvrement = Recouvrement::with(['microProjet', 'planRemboursement', 'agent'])->find($id);
        if (!$recouvrement) {
            return new JsonResponse(['message' => 'Recouvrement not found'], 404);
        }
        return new JsonResponse(['message' => 'Recouvrement retrieved successfully', 'data' => $recouvrement], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'plan_remboursement_id' => 'nullable|exists:plan_remboursements,id',
            'agent_id' => 'nullable|exists:personnels,id',
            'montant_recouvre' => 'nullable|numeric',
            'date_recouvrement' => 'nullable|date',
            'type_action' => 'nullable|in:APPEL,COURRIER,DECHARGE,MISE_EN_DEMEURE,CONTENTIEUX',
            'observations' => 'nullable|string',
            'justificatif_path' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $recouvrement = Recouvrement::create($validation->validated());
            return new JsonResponse(['message' => 'Recouvrement created successfully', 'data' => $recouvrement], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating recouvrement', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $recouvrement = Recouvrement::find($id);
        if (!$recouvrement) {
            return new JsonResponse(['message' => 'Recouvrement not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'plan_remboursement_id' => 'nullable|exists:plan_remboursements,id',
            'agent_id' => 'nullable|exists:personnels,id',
            'montant_recouvre' => 'nullable|numeric',
            'date_recouvrement' => 'nullable|date',
            'type_action' => 'nullable|in:APPEL,COURRIER,DECHARGE,MISE_EN_DEMEURE,CONTENTIEUX',
            'observations' => 'nullable|string',
            'justificatif_path' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $recouvrement->update($validation->validated());
            return new JsonResponse(['message' => 'Recouvrement updated successfully', 'data' => $recouvrement], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating recouvrement', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $recouvrement = Recouvrement::find($id);
        if (!$recouvrement) {
            return new JsonResponse(['message' => 'Recouvrement not found'], 404);
        }

        try {
            $recouvrement->delete();
            return new JsonResponse(['message' => 'Recouvrement deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting recouvrement', 'error' => $e->getMessage()], 500);
        }
    }
}
