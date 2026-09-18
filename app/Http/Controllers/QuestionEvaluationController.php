<?php

namespace App\Http\Controllers;

use App\Models\QuestionEvaluation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'formulaire_id' => 'required|exists:formulaire_evaluations,id',
            'code' => 'required|string|max:50',
            'libelle' => 'required|string',
            'type_question' => 'required|string|in:number,select,text,textarea,date,boolean',
            'options' => 'nullable|array',
            'ordre' => 'nullable|integer|min:0',
            'affichage' => 'nullable|boolean',
            'obligatoire' => 'nullable|boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $recouvrement = QuestionEvaluation::create($validation->validated());
            return new JsonResponse(['message' => 'Question created successfully', 'data' => $recouvrement], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating recouvrement', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $questionEvaluation = QuestionEvaluation::find($id);
        if (!$questionEvaluation) {
            return new JsonResponse(['message' => 'Question not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'code' => 'nullable|string|max:50',
            'libelle' => 'nullable|string',
            'type_question' => 'nullable|string|in:number,select,text,textarea,date,boolean',
            'options' => 'nullable|array',
            'ordre' => 'nullable|integer|min:0',
            'affichage' => 'nullable|boolean',
            'obligatoire' => 'nullable|boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $questionEvaluation->update($validation->validated());
            return new JsonResponse(['message' => 'Question updated successfully', 'data' => $questionEvaluation], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating questionEvaluation', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $questionEvaluation = QuestionEvaluation::find($id);
        if (!$questionEvaluation) {
            return new JsonResponse(['message' => 'Question not found'], 404);
        }

        try {
            $questionEvaluation->delete();
            return new JsonResponse(['message' => 'Question deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting question', 'error' => $e->getMessage()], 500);
        }
    }
}
