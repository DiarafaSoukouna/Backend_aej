<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionEvaluation extends Model
{
    protected $fillable = [
        'formulaire_id',
        'code',
        'libelle',
        'type_question',
        'options',
        'ordre',
        'affichage',
        'obligatoire',
    ];

    protected $casts = [
        'options' => 'array',
        'affichage' => 'boolean',
        'obligatoire' => 'boolean',
    ];

    public function formulaire()
    {
        return $this->belongsTo(FormulaireEvaluation::class, 'formulaire_id');
    }
    
}
