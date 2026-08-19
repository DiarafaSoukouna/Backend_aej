<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WorkflowExecutionController extends Controller
{
    public function transition(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'current_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'next_etape_code' => 'nullable|string|max:50|exists:workflow_etapes,code',
            'statut' => 'nullable|in:EN_COURS,TERMINE,REJETE,ABANDONNE',
            'comment' => 'nullable|string',
        ]);

        // Update workflow instance
        if (isset($validated['current_etape_code'])) {
            $instance->current_etape_code = $validated['current_etape_code'];
        }
        if (isset($validated['next_etape_code'])) {
            $instance->next_etape_code = $validated['next_etape_code'];
        }
        if (isset($validated['statut'])) {
            $instance->statut = $validated['statut'];
        }
        $instance->save();

        // Add to history
        if (isset($validated['comment']) || isset($validated['current_etape_code'])) {
            $instance->history()->create([
                'workflow_instance_id' => $instance->id,
                'etape_code' => $validated['current_etape_code'] ?? $instance->current_etape_code,
                'action' => 'TRANSITION',
                'comment' => $validated['comment'] ?? null,
                'acted_by' => Auth::user()?->id,
                'acted_at' => now(),
            ]);
        }

        // Update micro-projet statut if needed
        if (isset($validated['current_etape_code']) && $instance->microProjet) {
            $instance->microProjet->statut = $validated['current_etape_code'];
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Workflow transition successful', 'data' => $instance->fresh()]);
    }

    public function validatePlanAffaires(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'approved' => 'required|boolean',
            'comment' => 'nullable|string',
        ]);

        $currentEtape = $validated['approved'] ? 'PLAN_AFFAIRES_VALIDE' : 'PLAN_AFFAIRES_REJETE';
        $nextEtape = $validated['approved'] ? 'TRANSMIS_PARTENAIRE' : null;
        $statut = $validated['approved'] ? 'EN_COURS' : 'REJETE';

        // Update workflow instance
        $instance->current_etape_code = $currentEtape;
        $instance->next_etape_code = $nextEtape;
        $instance->statut = $statut;
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => $currentEtape,
            'action' => $validated['approved'] ? 'VALIDATION_APPROUVEE' : 'VALIDATION_REJETEE',
            'comment' => $validated['comment'],
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = $currentEtape;
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Plan affaires validation successful', 'data' => $instance->fresh()]);
    }

    public function imputeAgence(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'agence_id' => 'required|exists:agences_regionales,id',
            'comment' => 'nullable|string',
        ]);

        // Update micro-projet with agency imputation
        if ($instance->microProjet) {
            $instance->microProjet->agence_imputation_id = $validated['agence_id'];
            $instance->microProjet->statut = 'IMPUTE_AGENCE';
            $instance->microProjet->save();
        }

        // Update workflow instance
        $instance->current_etape_code = 'IMPUTE_AGENCE';
        $instance->next_etape_code = 'PLAN_DECAISSEMENT_SAISI';
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => 'IMPUTE_AGENCE',
            'action' => 'IMPUTATION_AGENCE',
            'comment' => $validated['comment'],
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        return response()->json(['message' => 'Agency imputation successful', 'data' => $instance->fresh()]);
    }

    public function validatePlanDecaissement(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'approved' => 'required|boolean',
            'validation_level' => 'required|in:CAR,SDRF,SDEF,SDPF,DPF',
            'comment' => 'nullable|string',
        ]);

        $currentEtape = 'EN_VALIDATION_INTERNE';

        // Update workflow instance
        $instance->current_etape_code = $currentEtape;
        $instance->next_etape_code = $validated['approved'] ? 'EN_VALIDATION_INTERNE' : 'PLAN_DECAISSEMENT_SAISI';
        $instance->statut = $validated['approved'] ? 'EN_COURS' : 'EN_COURS';
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => $currentEtape,
            'action' => 'VALIDATION_' . $validated['validation_level'] . '_' . ($validated['approved'] ? 'APPROUVEE' : 'REJETEE'),
            'comment' => $validated['comment'],
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = $currentEtape;
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Plan decaissement validation successful', 'data' => $instance->fresh()]);
    }

    public function validatePlanRemboursement(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'approved' => 'required|boolean',
            'comment' => 'nullable|string',
        ]);

        $currentEtape = $validated['approved'] ? 'EN_ANALYSE_PARTENAIRE' : 'ANNULE';
        $nextEtape = $validated['approved'] ? 'EN_FINANCEMENT' : null;
        $statut = $validated['approved'] ? 'EN_COURS' : 'REJETE';

        // Update workflow instance
        $instance->current_etape_code = $currentEtape;
        $instance->next_etape_code = $nextEtape;
        $instance->statut = $statut;
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => $currentEtape,
            'action' => $validated['approved'] ? 'VALIDATION_APPROUVEE' : 'VALIDATION_REJETEE',
            'comment' => $validated['comment'],
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = $currentEtape;
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Plan remboursement validation successful', 'data' => $instance->fresh()]);
    }

    public function authorizeLigneDecaissement(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'ligne_ids' => 'required|array',
            'ligne_ids.*' => 'exists:ligne_decaissements,id',
            'comment' => 'nullable|string',
        ]);

        // Update ligne decaissements statut
        foreach ($validated['ligne_ids'] as $ligneId) {
            $ligne = \App\Models\LigneDecaissement::find($ligneId);
            if ($ligne) {
                $ligne->statut = 'VALIDE';
                $ligne->save();
            }
        }

        // Update workflow instance
        $instance->current_etape_code = 'EN_FINANCEMENT';
        $instance->next_etape_code = 'EN_DECAISSEMENT';
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => 'EN_FINANCEMENT',
            'action' => 'AUTORISATION_LIGNES',
            'comment' => $validated['comment'] ?? 'Lignes autorisées: ' . implode(', ', array_map('strval', $validated['ligne_ids'])),
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = 'EN_FINANCEMENT';
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Ligne decaissement authorization successful', 'data' => $instance->fresh()]);
    }

    public function transmitPartenaire(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'lot_transmission_id' => 'required|exists:lots_transmission,id',
            'comment' => 'nullable|string',
        ]);

        // Update workflow instance
        $instance->current_etape_code = 'TRANSMIS_PARTENAIRE';
        $instance->next_etape_code = 'EN_ANALYSE_PARTENAIRE';
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => 'TRANSMIS_PARTENAIRE',
            'action' => 'TRANSMISSION_PARTENAIRE',
            'comment' => $validated['comment'],
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = 'TRANSMIS_PARTENAIRE';
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Transmission to partner successful', 'data' => $instance->fresh()]);
    }

    public function analysePartenaire(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'approved' => 'required|boolean',
            'compte_financement_id' => 'nullable|exists:compte_financements,id',
            'comment' => 'nullable|string',
        ]);

        $currentEtape = 'EN_ANALYSE_PARTENAIRE';
        $nextEtape = $validated['approved'] ? 'EN_FINANCEMENT' : 'ANNULE';
        $statut = $validated['approved'] ? 'EN_COURS' : 'REJETE';

        // Update workflow instance
        $instance->current_etape_code = $currentEtape;
        $instance->next_etape_code = $nextEtape;
        $instance->statut = $statut;
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => $currentEtape,
            'action' => $validated['approved'] ? 'ANALYSE_APPROUVEE' : 'ANALYSE_REJETEE',
            'comment' => $validated['comment'],
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = $currentEtape;
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Partner analysis successful', 'data' => $instance->fresh()]);
    }

    public function executeDecaissement(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'decaissement_ids' => 'required|array',
            'decaissement_ids.*' => 'exists:decaissements,id',
            'comment' => 'nullable|string',
        ]);

        // Update decaissements statut
        foreach ($validated['decaissement_ids'] as $decaissementId) {
            $decaissement = \App\Models\Decaissement::find($decaissementId);
            if ($decaissement) {
                $decaissement->statut = 'EXECUTE';
                $decaissement->save();
            }
        }

        // Update workflow instance
        $instance->current_etape_code = 'EN_DECAISSEMENT';
        $instance->next_etape_code = 'EN_REMBOURSEMENT';
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => 'EN_DECAISSEMENT',
            'action' => 'EXECUTION_DECAISSEMENT',
            'comment' => $validated['comment'] ?? 'Décaissements exécutés: ' . implode(', ', array_map('strval', $validated['decaissement_ids'])),
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = 'EN_DECAISSEMENT';
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Decaissement execution successful', 'data' => $instance->fresh()]);
    }

    public function executeRemboursement(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'remboursement_ids' => 'required|array',
            'remboursement_ids.*' => 'exists:remboursements,id',
            'comment' => 'nullable|string',
        ]);

        // Update remboursements statut
        foreach ($validated['remboursement_ids'] as $remboursementId) {
            $remboursement = \App\Models\Remboursement::find($remboursementId);
            if ($remboursement) {
                $remboursement->statut = 'PAYE';
                $remboursement->save();
            }
        }

        // Update workflow instance
        $instance->current_etape_code = 'EN_REMBOURSEMENT';
        $instance->next_etape_code = 'EN_SUIVI';
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => 'EN_REMBOURSEMENT',
            'action' => 'EXECUTION_REMBOURSEMENT',
            'comment' => $validated['comment'] ?? 'Remboursements exécutés: ' . implode(', ', array_map('strval', $validated['remboursement_ids'])),
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = 'EN_REMBOURSEMENT';
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Remboursement execution successful', 'data' => $instance->fresh()]);
    }

    public function executeRecouvrement(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'recouvrement_ids' => 'required|array',
            'recouvrement_ids.*' => 'exists:recouvrements,id',
            'comment' => 'nullable|string',
        ]);

        // Update recouvrements statut
        foreach ($validated['recouvrement_ids'] as $recouvrementId) {
            $recouvrement = \App\Models\Recouvrement::find($recouvrementId);
            if ($recouvrement) {
                $recouvrement->statut = 'RECUPERE';
                $recouvrement->save();
            }
        }

        // Update workflow instance
        $instance->current_etape_code = 'EN_REMBOURSEMENT';
        $instance->next_etape_code = 'EN_SUIVI';
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => 'EN_REMBOURSEMENT',
            'action' => 'EXECUTION_RECOUVREMENT',
            'comment' => $validated['comment'] ?? 'Recouvrements exécutés: ' . implode(', ', array_map('strval', $validated['recouvrement_ids'])),
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = 'EN_REMBOURSEMENT';
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Recouvrement execution successful', 'data' => $instance->fresh()]);
    }

    public function suivi(Request $request, $workflowInstanceId): JsonResponse
    {
        $instance = WorkflowInstance::findOrFail($workflowInstanceId);

        $validated = $request->validate([
            'exploitation_id' => 'nullable|exists:exploitations,id',
            'visite_photo_ids' => 'nullable|array',
            'visite_photo_ids.*' => 'exists:visite_photos,id',
            'comment' => 'nullable|string',
        ]);

        // Update workflow instance
        $instance->current_etape_code = 'EN_SUIVI';
        $instance->next_etape_code = 'EN_SUIVI';
        $instance->save();

        // Add to history
        $instance->history()->create([
            'workflow_instance_id' => $instance->id,
            'etape_code' => 'EN_SUIVI',
            'action' => 'SUIVI_PROJET',
            'comment' => $validated['comment'],
            'acted_by' => Auth::user()?->id,
            'acted_at' => now(),
        ]);

        // Update micro-projet statut
        if ($instance->microProjet) {
            $instance->microProjet->statut = 'EN_SUIVI';
            $instance->microProjet->save();
        }

        return response()->json(['message' => 'Suivi successful', 'data' => $instance->fresh()]);
    }
}
