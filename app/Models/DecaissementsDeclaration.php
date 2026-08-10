<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecaissementsDeclaration extends Model
{
    protected $fillable = [
        'plan_decaissement_id',
        'ligne_decaissement_id',
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

    public function planDecaissement()
    {
        return $this->belongsTo(PlanDecaissement::class, 'plan_decaissement_id');
    }

    public function ligneDecaissement()
    {
        return $this->belongsTo(LigneDecaissement::class, 'ligne_decaissement_id');
    }

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class, 'promoteur_id');
    }
}
