<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\FonctionController;
use App\Http\Controllers\NiveauLocaliteController;
use App\Http\Controllers\LocaliteController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\TypeEntrepriseController;
use App\Http\Controllers\TypeEmploiController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TypeOrganismeController;
use App\Http\Controllers\OrganismeController;
use App\Http\Controllers\IndicateurController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowVersionController;
use App\Http\Controllers\WorkflowEtapeController;
use App\Http\Controllers\WorkflowEtapeSlaController;
use App\Http\Controllers\WorkflowEtapeDeliverableController;
use App\Http\Controllers\WorkflowEtapeRoleController;
use App\Http\Controllers\WorkflowEtapeTransitionController;
use App\Http\Controllers\WorkflowEtapeDecisionController;
use App\Http\Controllers\WorkflowDecisionOutcomeController;


Route::apiResource('directions', DirectionController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('fonctions', FonctionController::class);
Route::apiResource('niveau-localites', NiveauLocaliteController::class);
Route::apiResource('type-entreprises', TypeEntrepriseController::class);
Route::get('localites/niveau/{niveauId}', [LocaliteController::class, 'getLocalitesByNiveau']);
Route::apiResource('localites', LocaliteController::class);
Route::apiResource('configurations', ConfigurationController::class);
Route::apiResource('type-emplois', TypeEmploiController::class);
Route::apiResource('personnels', PersonnelController::class);
Route::put('personnels/updatePassword/{id}', [PersonnelController::class, 'updatePassword']);
Route::post('personnels/login', [PersonnelController::class, 'auth']);
Route::post('personnels/logout', [PersonnelController::class, 'logout']);
Route::apiResource('permissions', PermissionController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('type-organismes', TypeOrganismeController::class);
Route::apiResource('organismes', OrganismeController::class);
Route::apiResource('indicateurs', IndicateurController::class);

// Workflow routes
Route::apiResource('workflows', WorkflowController::class);
Route::apiResource('workflow-versions', WorkflowVersionController::class);
Route::apiResource('workflow-etapes', WorkflowEtapeController::class);
Route::apiResource('workflow-etape-slas', WorkflowEtapeSlaController::class);
Route::apiResource('workflow-etape-deliverables', WorkflowEtapeDeliverableController::class);
Route::apiResource('workflow-etape-roles', WorkflowEtapeRoleController::class);
Route::apiResource('workflow-etape-transitions', WorkflowEtapeTransitionController::class);
Route::apiResource('workflow-etape-decisions', WorkflowEtapeDecisionController::class);
Route::apiResource('workflow-decision-outcomes', WorkflowDecisionOutcomeController::class);