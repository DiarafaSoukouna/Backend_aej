<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetsRemboursement extends Model
{
    protected $fillable = [
        'budget_id',
        'montant_remboursement',
        'montant_garantie',
        'montant_recouvrement',
        'dure_remboursement',
        'dure_differe',
        'date_premiere_echeance',
        'date_derniere_cheance',
        'echeance_rembourse',
        'restructuration_pret',
        'capital_amorti',
        'interets',
    ];

    protected $casts = [
        'montant_remboursement' => 'decimal:2',
        'montant_garantie' => 'decimal:2',
        'montant_recouvrement' => 'decimal:2',
        'echeance_rembourse' => 'decimal:2',
        'capital_amorti' => 'decimal:2',
        'interets' => 'decimal:2',
        'restructuration_pret' => 'boolean',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
