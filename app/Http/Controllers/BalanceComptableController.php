<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Budget;
use App\Models\Depense;
use App\Models\Remboursement;

class BalanceComptableController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $totalBudgetAlloue = Budget::sum('montant_alloue');
            $totalRemboursements = Remboursement::where('statut', 'PAYE')->sum('montant_paye');
            $totalRecettes = $totalBudgetAlloue + $totalRemboursements;
            $totalDepenses = Depense::sum('montant_depense');
            $solde = $totalRecettes - $totalDepenses;

            return new JsonResponse([
                'message' => 'Balance comptable retrieved successfully',
                'data' => [
                    'total_recettes' => $totalRecettes,
                    'total_depenses' => $totalDepenses,
                    'solde' => $solde,
                    'details' => [
                        'budget_alloue' => $totalBudgetAlloue,
                        'remboursements_recus' => $totalRemboursements,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error calculating balance comptable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function byMicroProjet($microProjetId): JsonResponse
    {
        try {
            $budget = Budget::where('micro_projet_id', $microProjetId)->first();
            $totalDepenses = Depense::where('micro_projet_id', $microProjetId)->sum('montant_depense');
            $totalRemboursements = 0;
            if ($budget) {
                $totalRemboursements = Remboursement::where('budget_id', $budget->id)
                    ->where('statut', 'PAYE')
                    ->sum('montant_paye');
            }

            $totalRecettes = $budget ? ($budget->montant_alloue + $totalRemboursements) : 0;
            $solde = $totalRecettes - $totalDepenses;

            return new JsonResponse([
                'message' => 'Balance comptable for micro projet retrieved successfully',
                'data' => [
                    'micro_projet_id' => $microProjetId,
                    'total_recettes' => $totalRecettes,
                    'total_depenses' => $totalDepenses,
                    'solde' => $solde,
                    'details' => [
                        'budget_alloue' => $budget ? $budget->montant_alloue : 0,
                        'remboursements_recus' => $totalRemboursements,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error calculating balance comptable for micro projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
