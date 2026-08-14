<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormulaireEvaluation extends Model
{
    protected $fillable = [
        'code',
        'libelle',
        'public_cible',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];
       public function questions()
    {
        return $this->hasMany(QuestionEvaluation::class, 'formulaire_id');
    }
}
