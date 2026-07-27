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
Route::apiResource('indicateurs', IndicateurController::class);

