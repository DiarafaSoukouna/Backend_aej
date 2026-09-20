<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TableauAmortissement extends Model
{
    use HasFactory;

    protected $table = 'tableau_amortissements';

    protected $fillable = [
        'plan_remboursement_id',
        'periode',
        'date_echeance',
        'montant_echeance',
        'capital_rembourse',
        'capital_restant',
        'interets',
        'amortissement_capital',
        'statut',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'montant_echeance' => 'decimal:2',
        'capital_rembourse' => 'decimal:2',
        'capital_restant' => 'decimal:2',
        'interets' => 'decimal:2',
        'amortissement_capital' => 'decimal:2',
    ];

    public function planRemboursement()
    {
        return $this->belongsTo(PlanRemboursement::class, 'plan_remboursement_id');
    }
}
