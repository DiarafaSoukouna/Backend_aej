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
        return $this->belongsTo(Organisme::class, 'organisme_id');
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

    public function workflowInstances()
    {
        return $this->hasMany(WorkflowInstance::class, 'micro_projet_id');
    }
}
