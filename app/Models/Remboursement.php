<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remboursement extends Model
{
    protected $fillable = [
        'plan_remboursement_id',
        'promoteur_id',
        'montant_echu',
        'montant_paye',
        'montant_impaye',
        'penalites',
        'date_paiement',
        'observations',
        'statut',
    ];

    protected $casts = [
        'montant_echu' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'montant_impaye' => 'decimal:2',
        'penalites' => 'decimal:2',
        'date_paiement' => 'date',
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
