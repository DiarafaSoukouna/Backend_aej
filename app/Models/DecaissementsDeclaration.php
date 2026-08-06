<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecaissementsDeclaration extends Model
{
    protected $fillable = [
        'plan_id',
        'promoteur_id',
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

    public function plan()
    {
        return $this->belongsTo(PlanDecaissement::class, 'plan_id');
    }

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class, 'promoteur_id');
    }
}
