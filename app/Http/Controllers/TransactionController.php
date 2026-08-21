<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = Transaction::with(['microProjet', 'promoteur', 'categorie', 'saisiPar'])->get();
        
        if ($request->has('micro_projet_id') && !empty($request->micro_projet_id)) 
            $transactions = $transactions->where('micro_projet_id', $request->micro_projet_id);
        if ($request->has('promoteur_id') && !empty($request->promoteur_id)) 
            $transactions = $transactions->where('promoteur_id', $request->promoteur_id);
        if ($request->has('categorie_id') && !empty($request->categorie_id)) 
            $transactions = $transactions->where('categorie_id', $request->categorie_id);
        if ($request->has('mode_paiement') && !empty($request->mode_paiement)) 
            $transactions = $transactions->where('mode_paiement', $request->mode_paiement);
        if ($request->has('type') && !empty($request->type)) 
            $transactions = $transactions->where('type', $request->type);
        if ($request->has('statut') && !empty($request->statut)) 
            $transactions = $transactions->where('statut', $request->statut);

        return new JsonResponse(['Message' => 'Transaction list retrieved successfully', 'data' => $transactions], 200);
    }

    public function show($id): JsonResponse
    {
        $transaction = Transaction::with(['microProjet', 'promoteur', 'categorie', 'saisiPar'])->find($id);
        if (!$transaction) {
            return new JsonResponse(['Message' => 'Transaction not found'], 404);
        }
        return new JsonResponse(['Message' => 'Transaction retrieved successfully', 'data' => $transaction], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
            'promoteur_id' => 'nullable|exists:promoteurs,id',
            'categorie_id' => 'required|exists:categories_transactions,id',
            'libelle' => 'required|string|max:200',
            'type' => 'required|in:RECETTE,DEPENSE',
            'montant' => 'required|numeric',
            'statut' => 'sometimes|in:BROUILLON,SOUMIS,VALIDE,REJETE,ANNULE',
            'mode_paiement' => 'nullable|in:ESPECES,BANQUE,MOBILE_MONEY,CHEQUE,AUTRE',
            'reference' => 'nullable|string|max:50|unique:transactions_financieres,reference',
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'date' => 'required|date',
            'saisi_par' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $transaction = Transaction::create($request->all());

            return new JsonResponse([
                'message' => 'Transaction created successfully',
                'data' => $transaction
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return new JsonResponse(['Message' => 'Transaction not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'sometimes|nullable|exists:micro_projets,id',
            'promoteur_id' => 'sometimes|nullable|exists:promoteurs,id',
            'categorie_id' => 'sometimes|required|exists:categories_transactions,id',
            'libelle' => 'sometimes|required|string|max:200',
            'type' => 'sometimes|required|in:RECETTE,DEPENSE',
            'montant' => 'sometimes|required|numeric',
            'statut' => 'sometimes|in:BROUILLON,SOUMIS,VALIDE,REJETE,ANNULE',
            'mode_paiement' => 'nullable|in:ESPECES,BANQUE,MOBILE_MONEY,CHEQUE,AUTRE',
            'reference' => 'nullable|string|max:50|unique:transactions_financieres,reference,' . $id,
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'date' => 'sometimes|required|date',
            'saisi_par' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $transaction->update($request->all());

            return new JsonResponse([
                'message' => 'Transaction updated successfully',
                'data' => $transaction
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return new JsonResponse(['Message' => 'Transaction not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'libelle' => 'nullable|string|max:200',
            'type' => 'nullable|in:RECETTE,DEPENSE',
            'montant' => 'nullable|numeric',
            'statut' => 'nullable|in:BROUILLON,SOUMIS,VALIDE,REJETE,ANNULE',
            'mode_paiement' => 'nullable|in:ESPECES,BANQUE,MOBILE_MONEY,CHEQUE,AUTRE',
            'reference' => 'nullable|string|max:50|unique:transactions_financieres,reference,' . $id,
            'justificatif_path' => 'nullable|string',
            'observations' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $transaction->update(array_filter($validation->validated()));

            return new JsonResponse([
                'message' => 'Transaction patched successfully',
                'data' => $transaction
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return new JsonResponse(['Message' => 'Transaction not found'], 404);
        }

        try {
            $transaction->delete();
            return new JsonResponse(['Message' => 'Transaction deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
