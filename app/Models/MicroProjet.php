<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MicroProjet extends Model
{
    protected $fillable = [
        'code',
        'intitule',
        'matricule',
        'description',
        'montant_total',
        'dispositif_id',
        'organisme_id',
        'guichet_id',
        'secteur_id',
        'commune_id',
        'agence_id',
        'agence_imputation_id',
        'promoteur_id',
        'stade_projet',
        'type_projet',
        'statut',
        'localisation',
        'geolocalisation',
        'date_certification',
        'date_transmission_partenaire',
    ];

    protected $casts = [
        'montant_total' => 'decimal:2',
        'date_certification' => 'date',
        'date_transmission_partenaire' => 'date',
    ];

    public function dispositif()
    {
        return $this->belongsTo(Dispositif::class, 'dispositif_id');
    }

    public function organisme()
    {
        return $this->belongsTo(OrganismeFinancement::class, 'organisme_id');
    }

    public function guichet()
    {
        return $this->belongsTo(Guichet::class, 'guichet_id');
    }

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteur_id');
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    public function agence()
    {
        return $this->belongsTo(AgenceRegionale::class, 'agence_id');
    }

    public function agenceImputation()
    {
        return $this->belongsTo(AgenceRegionale::class, 'agence_imputation_id');
    }

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class, 'promoteur_id');
    }

    public function workflowInstance()
    {
        return $this->hasOne(WorkflowInstance::class, 'micro_projet_id');
    }
    
    public function budget()
    {
        return $this->hasOne(Budget::class, 'micro_projet_id');
    }

    public function compteFinancement()
    {
        return $this->hasOne(CompteFinancement::class, 'micro_projet_id');
    }

    public function ligneDecaissements()
    {
        return $this->hasManyThrough(LigneDecaissement::class, PlanDecaissement::class, 'micro_projet_id', 'plan_decaissement_id');
    }

    public function recouvrements()
    {
        return $this->hasMany(Recouvrement::class, 'micro_projet_id');
    }

    public function planDecaissement()
    {
        return $this->hasOne(PlanDecaissement::class, 'micro_projet_id');
    }

    public function planRemboursement()
    {
        return $this->hasOne(PlanRemboursement::class, 'micro_projet_id');
    }

    public function lotMicroProjet()
    {
        return $this->hasOne(LotMicroProjet::class, 'micro_projet_id');
    }

    public function lotTransmission()
    {
        return $this->hasOneThrough(LotTransmission::class, LotMicroProjet::class, 'micro_projet_id', 'id', 'id', 'lot_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'micro_projet_id');
    }

    public function exploitations()
    {
        return $this->hasMany(Exploitation::class, 'micro_projet_id');
    }

    public function indicateurs()
    {
        return $this->hasMany(Indicateur::class, 'micro_projet_id');
    }

    public function suivis()
    {
        return $this->hasMany(Suivi::class, 'micro_projet_id');
    }

    public function embauches()
    {
        return $this->hasMany(Embauche::class, 'micro_projet_id');
    }

    public function formulaireEvaluation()
    {
        return $this->hasMany(FormulaireEvaluation::class, 'micro_projet_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'micro_projet_id');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class, 'micro_projet_id');
    }
}
