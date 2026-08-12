<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EvaluationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'formulaire_id' => ['required', 'exists:formulaire_evaluations,id'],
            'cible_type' => ['required', 'string', 'max:50'],
            'evaluateur_id' => ['required', 'integer'],
            'date_evaluation' => ['required', 'date'],
            'score_global' => ['nullable', 'numeric', 'between:0,100'],
            'commentaire' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $evaluation = Evaluation::create($validator->validated());

        return response()->json([
            'message' => 'Évaluation créée avec succès.',
            'data' => $evaluation,
        ], 201);
    }

    public function index()
    {
        $evaluations = Evaluation::with('formulaire')
            ->latest('date_evaluation')
            ->paginate(15);

        return response()->json($evaluations);
    }

    public function show($id)
    {
        $evaluation = Evaluation::findOrFail($id);

        return response()->json([
            'data' => $evaluation->load('formulaire'),
        ]);
    }

    public function addResponse(Request $request, $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reponses' => ['required', 'array', 'min:1'],
            'reponses.*.question_id' => [
                'required',
                'exists:question_evaluations,id',
            ],
            'reponses.*.reponse_texte' => ['nullable', 'string'],
            'reponses.*.promoteur_id' => ['required', 'exists:promoteurs,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        foreach ($validated['reponses'] as $reponse) {
            $questionBelongsToFormulaire = DB::table('question_evaluations')
                ->where('id', $reponse['question_id'])
                ->where('formulaire_id', $evaluation->formulaire_id)
                ->exists();

            if (!$questionBelongsToFormulaire) {
                return response()->json([
                    'message' => 'La question ' . $reponse['question_id'] . ' ne correspond pas au formulaire de cette évaluation.',
                ], 422);
            }
        }

        $responses = DB::transaction(function () use ($validated, $evaluation) {
            $responses = [];

            foreach ($validated['reponses'] as $reponse) {
                $responses[] = $evaluation->reponses()->create($reponse);
            }

            return $responses;
        });

        return response()->json([
            'message' => 'Les réponses ont été ajoutées avec succès.',
            'data' => $responses,
        ], 201);
    }

    public function responses($id)
    {
        $evaluation = Evaluation::findOrFail($id);

        return response()->json([
            'data' => $evaluation->reponses()->with('question')->get(),
        ]);
    }
}
