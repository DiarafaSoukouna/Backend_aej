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
use App\Http\Controllers\WorkflowRoleController;
use App\Http\Controllers\WorkflowDeliverableController;
use App\Http\Controllers\SuiviController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\RemboursementController;
use App\Http\Controllers\IndicateurSuiviController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategorieTransactionController;
use App\Http\Controllers\BalanceComptableController;
use App\Http\Controllers\SyncAejController;
use App\Http\Controllers\AejApiController;
use App\Http\Controllers\CompteFinancementController;
use App\Http\Controllers\ObservationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PlanDecaissementController;
use App\Http\Controllers\DecaissementController;
use App\Http\Controllers\RemboursementsDeclarationController;
use App\Http\Controllers\DecaissementsDeclarationController;


Route::apiResource('directions', DirectionController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('fonctions', FonctionController::class);
Route::apiResource('niveau-localites', NiveauLocaliteController::class);
Route::apiResource('type-entreprises', TypeEntrepriseController::class);
Route::get('localites/niveau/{niveauId}', [LocaliteController::class, 'getLocalitesByNiveau']);
Route::apiResource('localites', LocaliteController::class);
Route::apiResource('configurations', ConfigurationController::class);
Route::patch('/configurations', [ConfigurationController::class, 'patch']);
Route::apiResource('type-emplois', TypeEmploiController::class);
Route::apiResource('personnels', PersonnelController::class);
Route::put('personnels/updatePassword/{id}', [PersonnelController::class, 'updatePassword']);

Route::middleware('web')->group(function () {
    Route::post('personnels/login', [PersonnelController::class, 'auth']);
    Route::post('personnels/logout', [PersonnelController::class, 'logout']);
    Route::post('auth/refresh', [PersonnelController::class, 'refresh']);
    Route::get('/personnel/me', [PersonnelController::class, 'me']);

});

Route::apiResource('permissions', PermissionController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('type-organismes', TypeOrganismeController::class);
Route::apiResource('organismes', OrganismeController::class);

// Workflow routes
Route::prefix('workflow')->group(function () {
    Route::apiResource('models', WorkflowController::class);
    Route::apiResource('versions', WorkflowVersionController::class);
    Route::apiResource('roles', WorkflowRoleController::class);
    Route::apiResource('deliverables', WorkflowDeliverableController::class);
    Route::apiResource('decision-outcomes', WorkflowDecisionOutcomeController::class);
    Route::apiResource('etapes', WorkflowEtapeController::class);
    Route::apiResource('etape-slas', WorkflowEtapeSlaController::class);
    Route::apiResource('etape-deliverables', WorkflowEtapeDeliverableController::class);
    Route::apiResource('etape-roles', WorkflowEtapeRoleController::class);
    Route::apiResource('etape-decisions', WorkflowEtapeDecisionController::class);
    // Route::apiResource('etape-transitions', WorkflowEtapeTransitionController::class);
});

// Suivis & Indicateurs
Route::apiResource('suivis', SuiviController::class);
Route::apiResource('indicateurs', IndicateurController::class);
Route::apiResource('indicateur-suivis', IndicateurSuiviController::class);

// Finances routes
Route::apiResource('budgets', BudgetController::class);
Route::apiResource('compte-financements', CompteFinancementController::class);
Route::apiResource('plan-decaissements', PlanDecaissementController::class);
Route::apiResource('decaissements', DecaissementController::class);
Route::apiResource('decaissements-declarations', DecaissementsDeclarationController::class);
Route::apiResource('remboursements', RemboursementController::class);
Route::apiResource('remboursements-declarations', RemboursementsDeclarationController::class);
Route::apiResource('transactions', TransactionController::class);
Route::apiResource('categories-transactions', CategorieTransactionController::class);
Route::get('balance-comptable', [BalanceComptableController::class, 'index']);
Route::get('balance-comptable/micro-projet/{microProjetId}', [BalanceComptableController::class, 'byMicroProjet']);

// Autres routes
Route::apiResource('observations', ObservationController::class);
Route::apiResource('documents', DocumentController::class);

// AEJ API Routes
Route::prefix('aej')->group(function () {
    Route::get('types-pieces-identites', [AejApiController::class, 'getTypesPiecesIdentites']);
    Route::get('situations-matrimoniale', [AejApiController::class, 'getSituationsMatrimoniale']);
    Route::get('secteurs', [AejApiController::class, 'getSecteurs']);
    Route::get('sous-secteurs', [AejApiController::class, 'getSousSecteurs']);
    Route::get('niveaux-etudes', [AejApiController::class, 'getNiveauxEtudes']);
    Route::get('agences-regionales', [AejApiController::class, 'getAgencesRegionales']);
    Route::get('sexes', [AejApiController::class, 'getSexes']);
    Route::get('lieu-habitations', [AejApiController::class, 'getLieuHabitations']);
    Route::get('pays', [AejApiController::class, 'getPays']);
    Route::get('situations-handicaps', [AejApiController::class, 'getSituationsHandicaps']);
    Route::get('communes', [AejApiController::class, 'getCommunes']);
    Route::get('division-regionale', [AejApiController::class, 'getDivisionRegionale']);
    Route::get('villes', [AejApiController::class, 'getVilles']);
    Route::get('referentiels', [AejApiController::class, 'getAllReferentiels']);

    // Cache and sync routes
    Route::post('clear-cache', [AejApiController::class, 'clearCache']);
    Route::post('sync', [SyncAejController::class, 'sync']);
    Route::post('sync-all', [SyncAejController::class, 'syncAll']);
});
