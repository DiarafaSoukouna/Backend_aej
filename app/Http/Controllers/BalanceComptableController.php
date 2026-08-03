<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Budget;
use App\Models\Transaction;
use App\Models\Remboursement;

class BalanceComptableController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $totalBudgetAlloue = Budget::sum('montant_accorde');
            $totalRemboursements = Remboursement::where('statut', 'PAYE')->sum('montant_paye');
            $totalRecettes = Transaction::where('type', 'RECETTE')->where('statut', 'VALIDE')->sum('montant');
            $totalDepenses = Transaction::where('type', 'DEPENSE')->where('statut', 'VALIDE')->sum('montant');
            $solde = $totalBudgetAlloue - $totalDepenses;

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
            $totalDepenses = Transaction::where('micro_projet_id', $microProjetId)->where('type', 'DEPENSE')->where('statut', 'VALIDE')->sum('montant');
            $totalRecettes = Transaction::where('micro_projet_id', $microProjetId)->where('type', 'RECETTE')->where('statut', 'VALIDE')->sum('montant');
            $totalRemboursements = 0;
            if ($budget) {
                $totalRemboursements = Remboursement::where('budget_id', $budget->id)
                    ->where('statut', 'PAYE')
                    ->sum('montant_paye');
            }

            $solde = $budget ? $budget->montant_accorde - $totalDepenses : 0;

            return new JsonResponse([
                'message' => 'Balance comptable for micro projet retrieved successfully',
                'data' => [
                    'micro_projet_id' => $microProjetId,
                    'total_recettes' => $totalRecettes,
                    'total_depenses' => $totalDepenses,
                    'solde' => $solde,
                    'details' => [
                        'budget_alloue' => $budget ? $budget->montant_accorde : 0,
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
