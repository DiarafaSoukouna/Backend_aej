<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReponseEvaluation extends Model
{
    protected $fillable = [
        'evaluation_id',
        'question_id',
        'reponse_texte',
        'promoteur_id',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    public function question()
    {
        return $this->belongsTo(QuestionEvaluation::class, 'question_id');
    }
}
