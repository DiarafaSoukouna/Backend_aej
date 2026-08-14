<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanRemboursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'micro_projet_id',
        'budget_id',
        'echeance_mensuelle',
        'montant_echeance',
        'periode',
        'capital_rembourse',
        'capital_restant',
        'interets',
        'amortissement_capital',
        'justificatif_path',
    ];

    protected $casts = [
        'echeance_mensuelle' => 'date',
        'montant_echeance' => 'decimal:2',
        'capital_rembourse' => 'decimal:2',
        'capital_restant' => 'decimal:2',
        'interets' => 'decimal:2',
        'amortissement_capital' => 'decimal:2',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }

    public function remboursements()
    {
        return $this->hasMany(Remboursement::class, 'plan_remboursement_id');
    }

    public function remboursementsDeclarations()
    {
        return $this->hasMany(RemboursementsDeclaration::class, 'plan_remboursement_id');
    }

    public function recouvrements()
    {
        return $this->hasMany(Recouvrement::class, 'plan_remboursement_id');
    }
}
