<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(): JsonResponse
    {
        $transactions = Transaction::with(['microProjet', 'saisiPar'])->get();
        return new JsonResponse(['Message' => 'Transaction list retrieved successfully', 'data' => $transactions], 200);
    }

    public function show($id): JsonResponse
    {
        $transaction = Transaction::with(['microProjet', 'saisiPar'])->find($id);
        if (!$transaction) {
            return new JsonResponse(['Message' => 'Transaction not found'], 404);
        }
        return new JsonResponse(['Message' => 'Transaction retrieved successfully', 'data' => $transaction], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'categorie_id' => 'required|exists:categories_transactions,id',
            'libelle' => 'required|string|max:200',
            'montant' => 'required|numeric',
            'date' => 'required|date',
            'type' => 'required|in:DEBIT,CREDIT',
            'justificatif_path' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
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
            'micro_projet_id' => 'sometimes|required|exists:micro_projets,id',
            'categorie_id' => 'sometimes|required|exists:categories_transactions,id',
            'libelle' => 'sometimes|required|string|max:200',
            'montant' => 'sometimes|required|numeric',
            'date' => 'sometimes|required|date',
            'type' => 'sometimes|required|in:DEBIT,CREDIT',
            'justificatif_path' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
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
