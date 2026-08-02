<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\SyncAejReferentielsJob;
use Illuminate\Support\Facades\Log;

class SyncAejController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $referentiel = $request->input('referentiel', 'all');
        
        $validReferentiels = [
            'all',
            'types_pieces_identites',
            'situations_matrimoniale',
            'situations_handicaps',
            'secteurs',
            'sous_secteurs',
            'niveaux_etudes',
            'agences_regionales',
            'sexes',
            'lieu_habitations',
            'pays',
            'communes',
            'division_regionale',
            'villes',
        ];

        if (!in_array($referentiel, $validReferentiels)) {
            return new JsonResponse([
                'message' => 'Invalid referentiel',
                'valid_referentiels' => $validReferentiels,
            ], 400);
        }

        try {
            SyncAejReferentielsJob::dispatch($referentiel);
            
            Log::info("AEJ Sync Job dispatched", ['referentiel' => $referentiel]);
            
            return new JsonResponse([
                'message' => 'Synchronization job dispatched successfully',
                'referentiel' => $referentiel,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch AEJ Sync Job', [
                'referentiel' => $referentiel,
                'error' => $e->getMessage(),
            ]);
            
            return new JsonResponse([
                'message' => 'Failed to dispatch synchronization job',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncAll(): JsonResponse
    {
        try {
            SyncAejReferentielsJob::dispatch('all');
            
            Log::info('AEJ Sync Job dispatched for all referentiels');
            
            return new JsonResponse([
                'message' => 'All referentiels synchronization job dispatched successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch AEJ Sync Job for all referentiels', [
                'error' => $e->getMessage(),
            ]);
            
            return new JsonResponse([
                'message' => 'Failed to dispatch synchronization job',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
