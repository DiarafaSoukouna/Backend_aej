<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanDecaissement extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'budget_id',
        'compte_financement_id',
        'montant_planifie',
        'date_prevue',
        'justificatif_path',
    ];

    protected $casts = [
        'montant_planifie' => 'decimal:2',
        'date_prevue' => 'date',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }

    public function compteFinancement()
    {
        return $this->belongsTo(CompteFinancement::class, 'compte_financement_id');
    }

    public function ligneDecaissements()
    {
        return $this->hasMany(LigneDecaissement::class, 'plan_decaissement_id');
    }

    public function decaissements()
    {
        return $this->hasMany(Decaissement::class, 'plan_decaissement_id');
    }

    public function decaissementsDeclarations()
    {
        return $this->hasMany(DecaissementsDeclaration::class, 'plan_decaissement_id');
    }
}
