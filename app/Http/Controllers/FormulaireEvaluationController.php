<?php

namespace App\Http\Controllers;

use App\Models\FormulaireEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FormulaireEvaluationController extends Controller
{
    
    public function index(Request $request)
    {
        $query = FormulaireEvaluation::with('questions');

        if ($request->filled('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        if ($request->filled('public_cible')) {
            $query->where('public_cible', $request->input('public_cible'));
        }

        $formulaires = $query->latest()->paginate(15);

        return response()->json($formulaires);
    }

   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:50', 'unique:formulaire_evaluations,code'],
            'libelle' => ['required', 'string', 'max:200'],
            'public_cible' => ['required', 'string', 'max:50'],
            'actif' => ['nullable', 'boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.code' => ['required', 'string', 'max:50', 'distinct'],
            'questions.*.libelle' => ['required', 'string'],
            'questions.*.type_question' => ['required', 'in:number,select,text,textarea,date,boolean'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.ordre' => ['nullable', 'integer', 'min:0'],
            'questions.*.affichage' => ['nullable', 'boolean'],
            'questions.*.obligatoire' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $formulaire = DB::transaction(function () use ($validated) {
            $formulaire = FormulaireEvaluation::create([
                'code' => $validated['code'],
                'libelle' => $validated['libelle'],
                'public_cible' => $validated['public_cible'],
                'actif' => $validated['actif'] ?? true,
            ]);

            foreach ($validated['questions'] as $index => $question) {
                $formulaire->questions()->create([
                    'code' => $question['code'],
                    'libelle' => $question['libelle'],
                    'type_question' => $question['type_question'],
                    'options' => $question['options'] ?? null,
                    'ordre' => $question['ordre'] ?? $index,
                    'affichage' => $question['affichage'] ?? null,
                    'obligatoire' => $question['obligatoire'] ?? true,
                ]);
            }

            return $formulaire->load('questions');
        });

        return response()->json([
            'message' => 'Formulaire créé avec succès.',
            'data' => $formulaire,
        ], 201);
    }

  
    public function show($id)
    {
        $formulaireEvaluation = FormulaireEvaluation::findOrFail($id);
        return response()->json([
            'data' => $formulaireEvaluation->load('questions'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $formulaireEvaluation = FormulaireEvaluation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => [
                'sometimes', 'string', 'max:50',
                Rule::unique('formulaire_evaluations', 'code')->ignore($formulaireEvaluation->id),
            ],
            'libelle' => ['sometimes', 'string', 'max:200'],
            'public_cible' => ['sometimes', 'string', 'max:50'],
            'actif' => ['sometimes', 'boolean'],
            'questions' => ['sometimes', 'array', 'min:1'],
            'questions.*.id' => [
                'nullable',
                'integer',
                Rule::exists('question_evaluations', 'id')->where('formulaire_id', $formulaireEvaluation->id),
            ],
            'questions.*.code' => ['required_with:questions', 'string', 'max:50', 'distinct'],
            'questions.*.libelle' => ['required_with:questions', 'string'],
            'questions.*.type_question' => ['required_with:questions', 'in:number,select,text,textarea,date,boolean'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.ordre' => ['nullable', 'integer', 'min:0'],
            'questions.*.affichage' => ['nullable', 'boolean'],
            'questions.*.obligatoire' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $formulaire = DB::transaction(function () use ($validated, $formulaireEvaluation) {
            $formulaireEvaluation->update(array_filter([
                'code' => $validated['code'] ?? null,
                'libelle' => $validated['libelle'] ?? null,
                'public_cible' => $validated['public_cible'] ?? null,
                'actif' => $validated['actif'] ?? null,
            ], fn ($value) => $value !== null));

            if (isset($validated['questions'])) {
                $idsEnvoyes = collect($validated['questions'])
                    ->pluck('id')
                    ->filter()
                    ->all();

                $formulaireEvaluation->questions()
                    ->whereNotIn('id', $idsEnvoyes)
                    ->delete();

                foreach ($validated['questions'] as $index => $question) {
                    if (isset($question['id'])) {
                        $formulaireEvaluation->questions()->findOrFail($question['id'])->update([
                            'code' => $question['code'],
                            'libelle' => $question['libelle'],
                            'type_question' => $question['type_question'],
                            'options' => $question['options'] ?? null,
                            'ordre' => $question['ordre'] ?? $index,
                            'affichage' => $question['affichage'] ?? null,
                            'obligatoire' => $question['obligatoire'] ?? true,
                        ]);
                    } else {
                        $formulaireEvaluation->questions()->create([
                            'code' => $question['code'],
                            'libelle' => $question['libelle'],
                            'type_question' => $question['type_question'],
                            'options' => $question['options'] ?? null,
                            'ordre' => $question['ordre'] ?? $index,
                            'affichage' => $question['affichage'] ?? null,
                            'obligatoire' => $question['obligatoire'] ?? true,
                        ]);
                    }
                }
            }

            return $formulaireEvaluation->load('questions');
        });

        return response()->json([
            'message' => 'Formulaire mis à jour avec succès.',
            'data' => $formulaire,
        ]);
    }

    public function destroy($id)
    {
        $formulaireEvaluation = FormulaireEvaluation::findOrFail($id);
        $formulaireEvaluation->delete();

        return response()->json([
            'message' => 'Formulaire supprimé avec succès.',
        ], 200);
    }
}
