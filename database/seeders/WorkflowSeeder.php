<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowEtape;
use App\Models\WorkflowEtapeSla;
use App\Models\WorkflowEtapeRole;
use App\Models\WorkflowEtapeTransition;
use App\Models\WorkflowEtapeDeliverable;
use App\Models\WorkflowEtapeDecision;
use App\Models\WorkflowDecisionOutcome;




class WorkflowSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // TODO: Create workflows, versions, etapes, roles, slas, transitions, deliverables, decisions, outcomes
        Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Workflow Description',
        ]);
    }
}
