<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\FonctionController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\TypeEntrepriseController;
use App\Http\Controllers\TypeEmploiController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TypeOrganismeController;
use App\Http\Controllers\IndicateurController;
use App\Http\Controllers\PromoteurController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowVersionController;
use App\Http\Controllers\WorkflowEtapeController;
use App\Http\Controllers\WorkflowEtapeSlaController;
use App\Http\Controllers\WorkflowEtapeDeliverableController;
use App\Http\Controllers\WorkflowEtapeRoleController;
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
use App\Http\Controllers\FormulaireEvaluationController;
use App\Http\Controllers\EvaluationController;

use App\Http\Controllers\MicroProjetController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganismeFinancementController;
use App\Http\Controllers\WorkflowInstanceController;
use App\Http\Controllers\WorkflowInstanceHistoryController;
use App\Http\Controllers\WorkflowInstanceDeliverableController;
use App\Http\Controllers\WorkflowInstanceCommentController;
use App\Http\Controllers\WorkflowExecutionController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\ZoneInterventionController;
use App\Http\Controllers\GuichetController;
use App\Http\Controllers\DispositifController;
use App\Http\Controllers\ExploitationController;
use App\Http\Controllers\VisitePhotoController;
use App\Http\Controllers\EmbaucheController;
use App\Http\Controllers\LotTransmissionController;
use App\Http\Controllers\LigneDecaissementController;
use App\Http\Controllers\PlanRemboursementController;
use App\Http\Controllers\RecouvrementController;

// Paramètres
Route::apiResource('configurations', ConfigurationController::class);
Route::patch('/configurations', [ConfigurationController::class, 'patch']);
Route::apiResource('directions', DirectionController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('fonctions', FonctionController::class);
Route::apiResource('type-entreprises', TypeEntrepriseController::class);
Route::apiResource('type-organismes', TypeOrganismeController::class);
Route::apiResource('type-emplois', TypeEmploiController::class);

// Gestion des utilisateurs
Route::apiResource('permissions', PermissionController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('personnels', PersonnelController::class);
Route::put('personnels/updatePassword/{id}', [PersonnelController::class, 'updatePassword']);
Route::get('promoteurs', [PromoteurController::class, 'index']);
Route::get('/promoteurs/export', [PromoteurController::class, 'exportCsv']);
Route::get('promoteurs/{id}', [PromoteurController::class, 'show']);
Route::get('projets', [MicroProjetController::class, 'index']);
Route::get('projets/{id}', [MicroProjetController::class, 'show']);
Route::post('projets/filter', [MicroProjetController::class, 'filter']);
Route::apiResource('notifications', NotificationController::class);
Route::put('notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
Route::get('notifications/personnel/{personnelId}', [NotificationController::class, 'getByPersonnel']);

// Authentification
Route::middleware('web')->group(function () {
    Route::post('personnels/login', [PersonnelController::class, 'auth']);
    Route::post('personnels/logout', [PersonnelController::class, 'logout']);
    Route::post('auth/refresh', [PersonnelController::class, 'refresh']);
    Route::get('/personnel/me', [PersonnelController::class, 'me']);
});

// Entreprises & Projets
Route::apiResource('mega-projets', ProjetController::class);
Route::apiResource('zones-intervention', ZoneInterventionController::class);
Route::apiResource('dispositifs', DispositifController::class);
Route::apiResource('guichets', GuichetController::class);

// Promoteurs
Route::get('promoteurs', [PromoteurController::class, 'index']);
Route::get('promoteurs/{id}', [PromoteurController::class, 'show']);
Route::post('promoteurs/filter', [PromoteurController::class, 'filter']);
Route::post('promoteurs/filter-with-projects', [PromoteurController::class, 'filterWithProjects']);
Route::apiResource('formulaires-evaluation', FormulaireEvaluationController::class);
Route::get('/formulaires-evaluation/{formulaireEvaluation}',[FormulaireEvaluationController::class, 'show']);
Route::apiResource('evaluations', EvaluationController::class);
Route::post('evaluations/{evaluation}/responses', [EvaluationController::class, 'addResponse']);
Route::get('evaluations/{evaluation}/responses', [EvaluationController::class, 'responses']);
Route::get('projets', [MicroProjetController::class, 'index']);
Route::get('projets/{id}', [MicroProjetController::class, 'show']);
Route::post('projets/filter', [MicroProjetController::class, 'filter']);

// Workflow
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
});

// Workflow Instances
Route::prefix('workflow-instances')->group(function () {
    Route::apiResource('instances', WorkflowInstanceController::class);
    Route::apiResource('histories', WorkflowInstanceHistoryController::class);
    Route::apiResource('deliverables', WorkflowInstanceDeliverableController::class);
    Route::apiResource('comments', WorkflowInstanceCommentController::class);
});

// Workflow-exécution
Route::prefix('workflow-instances/{workflowInstanceId}')->group(function () {
    // Workflow state transitions
    Route::post('transition', [WorkflowExecutionController::class, 'transition']);
    
    // AGR_CLASSIC-PLUS: ETAPE_02 - Joindre le plan d'affaires
    Route::post('deliverables/plan-affaires', [WorkflowInstanceDeliverableController::class, 'store']);
    
    // AGR_CLASSIC-PLUS: ETAPE_03 - Valider les plans d'affaires
    Route::post('validate-plan-affaires', [WorkflowExecutionController::class, 'validatePlanAffaires']);
    
    // MEPS-MPE: ETAPE_03 - Imputation aux agences régionales
    Route::post('impute-agence', [WorkflowExecutionController::class, 'imputeAgence']);
    
    // MEPS-MPE: ETAPE_04 - Mise en place du plan de décaissement
    Route::apiResource('plan-decaissements', PlanDecaissementController::class);
    Route::apiResource('ligne-decaissements', LigneDecaissementController::class);
    
    // MEPS-MPE: ETAPE_05_1 to ETAPE_05_5 - Validation du plan de décaissement
    Route::post('validate-plan-decaissement', [WorkflowExecutionController::class, 'validatePlanDecaissement']);
    
    // AGR_CLASSIC-PLUS: ETAPE_04 & MEPS-MPE: ETAPE_02 - Transmission au partenaire financier
    Route::apiResource('lots-transmission', LotTransmissionController::class);
    Route::post('transmit-partenaire', [WorkflowExecutionController::class, 'transmitPartenaire']);
    
    // AGR_CLASSIC-PLUS: ETAPE_05 - Traitement des dossiers par le partenaire financier
    Route::apiResource('plan-remboursements', PlanRemboursementController::class);
    Route::post('analyse-partenaire', [WorkflowExecutionController::class, 'analysePartenaire']);
    
    // MEPS-MPE: ETAPE_06 - Traitement des lignes de décaissement
    Route::post('authorize-ligne-decaissement', [WorkflowExecutionController::class, 'authorizeLigneDecaissement']);
    
    // MEPS-MPE: ETAPE_07 & AGR_CLASSIC-PLUS: ETAPE_06_1 - Exécution des lignes de décaissement
    Route::apiResource('decaissements', DecaissementController::class);
    Route::post('execute-decaissement', [WorkflowExecutionController::class, 'executeDecaissement']);
    
    // MEPS-MPE: ETAPE_08 & AGR_CLASSIC-PLUS: ETAPE_06_2 - Remboursement
    Route::apiResource('remboursements', RemboursementController::class);
    Route::post('execute-remboursement', [WorkflowExecutionController::class, 'executeRemboursement']);
    
    // MEPS-MPE: ETAPE_09 & AGR_CLASSIC-PLUS: ETAPE_06_3 - Recouvrement
    Route::apiResource('recouvrements', RecouvrementController::class);
    Route::post('execute-recouvrement', [WorkflowExecutionController::class, 'executeRecouvrement']);

    // MEPS-MPE: ETAPE_10 & AGR_CLASSIC-PLUS: ETAPE_08 - Suivis & Exploitation
    Route::apiResource('exploitations', ExploitationController::class);
    Route::apiResource('visite-photos', VisitePhotoController::class);
    Route::post('suivi', [WorkflowExecutionController::class, 'suivi']);
});

// Suivis & Indicateurs
Route::apiResource('suivis', SuiviController::class);
Route::apiResource('indicateurs', IndicateurController::class);
Route::apiResource('indicateur-suivis', IndicateurSuiviController::class);
Route::apiResource('exploitations', ExploitationController::class);
Route::apiResource('visite-photos', VisitePhotoController::class);
Route::apiResource('entreprises', EntrepriseController::class);
Route::apiResource('embauches', EmbaucheController::class);
Route::apiResource('observations', ObservationController::class);
Route::apiResource('documents', DocumentController::class);

// Finances
Route::apiResource('organismes', OrganismeFinancementController::class);
Route::get('organismes/region/{regionId}', [OrganismeFinancementController::class, 'getByRegion']);
Route::get('organismes/type/{typeId}', [OrganismeFinancementController::class, 'getByType']);
Route::apiResource('budgets', BudgetController::class);
Route::apiResource('compte-financements', CompteFinancementController::class);
Route::apiResource('plan-decaissements', PlanDecaissementController::class);
Route::apiResource('plan-remboursements', PlanRemboursementController::class);
Route::apiResource('lots-transmission', LotTransmissionController::class);
Route::apiResource('ligne-decaissements', LigneDecaissementController::class);
Route::apiResource('recouvrements', RecouvrementController::class);
Route::apiResource('decaissements', DecaissementController::class);
Route::apiResource('decaissements-declarations', DecaissementsDeclarationController::class);
Route::apiResource('remboursements', RemboursementController::class);
Route::apiResource('remboursements-declarations', RemboursementsDeclarationController::class);
Route::apiResource('transactions', TransactionController::class);
Route::apiResource('categories-transactions', CategorieTransactionController::class);
Route::get('balance-comptable', [BalanceComptableController::class, 'index']);
Route::get('balance-comptable/micro-projet/{microProjetId}', [BalanceComptableController::class, 'byMicroProjet']);

// AEJ API
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
