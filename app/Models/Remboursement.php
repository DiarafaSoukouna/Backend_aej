<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remboursement extends Model
{
    protected $fillable = [
        'promoteur_id',
        'budget_id',
        'montant_echu',
        'montant_paye',
        'montant_impaye',
        'date_paiement',
        'penalites',
        'observations',
        'statut',
    ];

    protected $casts = [
        'montant_echu' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'montant_impaye' => 'decimal:2',
        'penalites' => 'decimal:2',
    ];

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
