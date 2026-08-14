<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Workflow as WorkflowModel;
use App\Models\WorkflowVersion;
use App\Models\WorkflowEtape;
use App\Models\WorkflowEtapeSla;
use App\Models\WorkflowEtapeRole;
use App\Models\WorkflowEtapeDeliverable;
use App\Models\WorkflowEtapeDecision;
use App\Models\WorkflowDecisionOutcome;
use App\Models\WorkflowRole;
use App\Models\WorkflowDeliverable;
use Database\Factories\WorkflowFactory;




class WorkflowSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Roles first (required for workflow_etapes_roles foreign key)
        foreach (WorkflowFactory::getWorkflowRoles() as $code => $data) {
            $role = \App\Models\Role::where('libelle', $data['name'])->first();
            if (!$role) {
                \App\Models\Role::create([
                    'code' => $code,
                    'libelle' => $data['name'],
                    'description' => $data['description'],
                ]);
            }
        }

        // Seed Workflow Roles
        foreach (WorkflowFactory::getWorkflowRoles() as $code => $data) {
            WorkflowRole::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => $data['is_active'],
                ]
            );
        }

        // Seed Workflow Decision Outcomes
        foreach (WorkflowFactory::getWorkflowDecisionOutcomes() as $code => $label) {
            WorkflowDecisionOutcome::updateOrCreate(
                ['code' => $code],
                ['label' => $label]
            );
        }

        // Seed Workflow Deliverables
        foreach (WorkflowFactory::getWorkflowDeliverables() as $code => $data) {
            WorkflowDeliverable::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => $data['is_active'],
                ]
            );
        }

        // Seed Workflows and Versions
        foreach (WorkflowFactory::getWorkflowModels() as $code => $data) {
            $workflow = WorkflowModel::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => $data['is_active'],
                ]
            );

            $versions = WorkflowFactory::getWorkflowVersions();
            if (isset($versions[$code])) {
                $versionData = $versions[$code];
                $versionCode = $versionData['workflow_code'] . '_' . $versionData['version'];
                WorkflowVersion::updateOrCreate(
                    ['code' => $versionCode],
                    [
                        'workflow_code' => $versionData['workflow_code'],
                        'version' => $versionData['version'],
                        'name' => $versionData['name'],
                        'description' => $versionData['description'],
                        'is_active' => $versionData['is_active'],
                        'is_default' => $versionData['is_default'],
                    ]
                );
            }
        }

        // Seed Workflow Etapes
        foreach (WorkflowFactory::getWorkflowEtapes() as $workflowCode => $etapes) {
            foreach ($etapes as $etapeData) {
                $versionCode = $workflowCode . '_' . $etapeData['version'];
                WorkflowEtape::updateOrCreate(
                    [
                        'workflow_version' => $versionCode,
                        'code' => $etapeData['code'],
                    ],
                    [
                        'parent_etape_code' => $etapeData['parent_etape_code'] ?? null,
                        'name' => $etapeData['name'],
                        'impact' => $etapeData['impact'] ?? null,
                        'statut' => 'NON',
                        'description' => $etapeData['description'],
                        'order' => $etapeData['order'],
                        'is_active' => $etapeData['is_active'],
                    ]
                );
            }
        }

        // Seed Workflow Etapes SLA
        foreach (WorkflowFactory::getWorkflowEtapesSla() as $workflowCode => $slas) {
            foreach ($slas as $slaData) {
                if (WorkflowEtape::where('code', $slaData['etape_code'])->exists()) {
                    WorkflowEtapeSla::updateOrCreate(
                        [
                            'etape_code' => $slaData['etape_code'],
                            'duration_value' => $slaData['duration_value'],
                            'duration_unit' => $slaData['duration_unit'],
                        ],
                        [
                            'description' => $slaData['description'],
                        ]
                    );
                }
            }
        }

        // Seed Workflow Etapes Roles
        foreach (WorkflowFactory::getWorkflowEtapesRoles() as $workflowCode => $roles) {
            foreach ($roles as $roleData) {
                if (WorkflowEtape::where('code', $roleData['etape_code'])->exists() 
                    && \App\Models\Role::where('code', $roleData['role_code'])->exists()) {
                    WorkflowEtapeRole::updateOrCreate(
                        [
                            'etape_code' => $roleData['etape_code'],
                            'role_code' => $roleData['role_code'],
                        ],
                        [
                            'responsibility' => $roleData['action'] ?? null,
                        ]
                    );
                }
            }
        }

        // Seed Workflow Etapes Decisions
        foreach (WorkflowFactory::getWorkflowEtapesDecision() as $workflowCode => $decisions) {
            foreach ($decisions as $decisionData) {
                if (WorkflowEtape::where('code', $decisionData['etape_code'])->exists()) {
                    WorkflowEtapeDecision::updateOrCreate(
                        [
                            'etape_code' => $decisionData['etape_code'],
                            'code' => $decisionData['code'],
                        ],
                        [
                            'name' => $decisionData['name'],
                            'description' => $decisionData['description'] ?? null,
                            'outcomes' => implode('|', $decisionData['outcomes']),
                        ]
                    );
                }
            }
        }

        // Seed Workflow Etapes Deliverables
        foreach (WorkflowFactory::getWorkflowEtapesDeliverable() as $workflowCode => $deliverables) {
            foreach ($deliverables as $deliverableData) {
                if (WorkflowEtape::where('code', $deliverableData['etape_code'])->exists()) {
                    WorkflowEtapeDeliverable::updateOrCreate(
                        [
                            'etape_code' => $deliverableData['etape_code'],
                            'deliverable_code' => $deliverableData['deliverable_code'],
                        ],
                        [
                            'is_required' => $deliverableData['is_required'] ?? true,
                        ]
                    );
                }
            }
        }
    }
}
