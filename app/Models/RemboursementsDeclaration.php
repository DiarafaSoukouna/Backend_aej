<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemboursementsDeclaration extends Model
{
    protected $fillable = [
        'plan_remboursement_id',
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

    public function planRemboursement()
    {
        return $this->belongsTo(PlanRemboursement::class, 'plan_remboursement_id');
    }

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class, 'promoteur_id');
    }
}
