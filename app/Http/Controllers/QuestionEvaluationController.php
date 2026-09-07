<?php

namespace App\Http\Controllers;

use App\Models\QuestionEvaluation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuestionEvaluationController extends Controller
{
     public function index(Request $request)
    {
        $query = QuestionEvaluation::with('formulaire');

        $filters = ['formulaire_id', 'code', 'libelle', 'type_question'];
        foreach ($filters as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        $perPage = $request->input('per_page', 20);
        $questions = $query->paginate($perPage);

        return new JsonResponse([
            'message' => 'Questions retrieved successfully',
            'data' => $questions->items(),
            'pagination' => [
                'current_page' => $questions->currentPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
                'last_page' => $questions->lastPage(),
                'from' => $questions->firstItem(),
                'to' => $questions->lastItem(),
            ],
        ], 200);
    }

    public function show($questionEvaluation): JsonResponse
    {
        $questionEvaluation = QuestionEvaluation::with(['formulaire'])->find($questionEvaluation);
        if (!$questionEvaluation) {
            return new JsonResponse(['message' => 'Question evaluation not found'], 404);
        }

        return new JsonResponse([
            'message' => 'Question evaluation retrieved successfully',
            'data' => $questionEvaluation
        ], 200);
    }

    public function destroy(QuestionEvaluation $questionEvaluation)
    {
        $questionEvaluation->delete();

        return new JsonResponse([
            'message' => 'Question supprimée avec succès.',
        ], 200);
    }
}
