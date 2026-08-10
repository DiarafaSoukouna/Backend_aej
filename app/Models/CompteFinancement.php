<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompteFinancement extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'organisme_id',
        'budget_id',
        'etat_compte',
        'avis_partenaire',
        'montant_accorde',
        'duree_pret',
        'duree_remboursement',
        'taux_interet',
        'date_ouverture',
        'lieu_ouverture',
        'observation',
    ];

    protected $casts = [
        'montant_accorde' => 'decimal:2',
        'duree_pret' => 'integer',
        'duree_remboursement' => 'integer',
        'taux_interet' => 'decimal:2',
        'date_ouverture' => 'date',
    ];

    public function organisme()
    {
        return $this->belongsTo(OrganismeFinancement::class, 'organisme_id');
    }

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }

    public function planDecaissements()
    {
        return $this->hasMany(PlanDecaissement::class, 'compte_financement_id');
    }
}
