<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'intitule',
        'montant_accorde',
        'date_accord',
        'source',
        'statut',
        'devise',
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
        'montant_accorde' => 'decimal:2',
        'deblocage' => 'boolean',
        'date_accord' => 'date',
        'date_deblocage' => 'date',
        'date_signature' => 'date',
        'date_reception' => 'date',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class);
    }

    public function validePar()
    {
        return $this->belongsTo(Personnel::class, 'valide_par');
    }

    public function ligneDecaissements()
    {
        return $this->hasManyThrough(LigneDecaissement::class, PlanDecaissement::class, 'budget_id', 'plan_decaissement_id', 'id', 'id');
    }

    public function planDecaissements()
    {
        return $this->hasOne(PlanDecaissement::class, 'budget_id');
    }

    public function decaissements()
    {
        return $this->hasManyThrough(Decaissement::class, PlanDecaissement::class, 'budget_id', 'plan_decaissement_id', 'id', 'id');
    }

    public function planRemboursements()
    {
        return $this->hasOne(PlanRemboursement::class, 'budget_id');
    }

    public function remboursements()
    {
        return $this->hasManyThrough(Remboursement::class, PlanRemboursement::class, 'budget_id', 'plan_remboursement_id', 'id', 'id');
    }
}
