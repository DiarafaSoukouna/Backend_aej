<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'formulaire_id',
        'cible_type',
        'evaluateur_id',
        'date_evaluation',
        'score_global',
        'commentaire',
    ];

    protected $casts = [
        'date_evaluation' => 'datetime',
        'score_global' => 'decimal:2',
    ];

    public function formulaire()
    {
        return $this->belongsTo(FormulaireEvaluation::class, 'formulaire_id');
    }

    public function evaluateur()
    {
        return $this->belongsTo(Personnel::class, 'evaluateur_id');
    }
    public function reponses()
{
    return $this->hasMany(ReponseEvaluation::class, 'evaluation_id');
}
}
