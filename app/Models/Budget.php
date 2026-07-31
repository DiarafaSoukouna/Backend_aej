<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'intitule',
        'montant_alloue',
        'annee_financement',
        'devise',
        'statut',
        'date_octroye',
        'deblocage',
        'date_deblocage',
        'signature_convention',
        'date_signature',
        'reception_acte_credit',
        'date_reception',
        'observations',
        'valide_par',
    ];

    protected $casts = [
        'montant_alloue' => 'decimal:2',
        'deblocage' => 'boolean',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class);
    }

    public function validePar()
    {
        return $this->belongsTo(Personnel::class, 'valide_par');
    }

    public function budgetsRemboursement()
    {
        return $this->hasOne(BudgetsRemboursement::class);
    }

    public function remboursements()
    {
        return $this->hasMany(Remboursement::class);
    }

    public function planDecaissements()
    {
        return $this->hasMany(PlanDecaissement::class);
    }
}
