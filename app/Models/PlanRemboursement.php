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
        'date_ouverture',
        'decision',
        'montant_credit',
        'interets',
        'duree_pret',
        'duree_remboursement',
        'fichier_amortissement',
        'fichier_convention',
    ];

    protected $casts = [
        'date_ouverture' => 'date',
        'montant_credit' => 'decimal:2',
        'interets' => 'decimal:2',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }

    public function tableauAmortissements()
    {
        return $this->hasMany(TableauAmortissement::class, 'plan_remboursement_id');
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
