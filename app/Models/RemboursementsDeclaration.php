<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemboursementsDeclaration extends Model
{
    protected $fillable = [
        'promoteur_id',
        'budget_id',
        'montant_declare',
        'date_declaree',
        'reference_banque',
        'justificatif_path',
        'observations',
        'statut',
    ];

    protected $casts = [
        'date_declaree' => 'date',
    ];

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class, 'promoteur_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }
}
