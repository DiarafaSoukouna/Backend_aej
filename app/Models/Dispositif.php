<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dispositif extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'projet_id',
        'guichet_id',
        'workflow_version',
        'intitule',
        'budget_alloue',
        'montant_min',
        'montant_max',
        'taux',
        'duree',
        'nbre_emplois_prevu',
        'nbre_beneficiaire_prevu',
        'nbre_micro_projet_prevu',
    ];

    protected $casts = [
        'budget_alloue' => 'decimal:2',
        'montant_min' => 'decimal:2',
        'montant_max' => 'decimal:2',
        'taux' => 'decimal:2',
        'duree' => 'integer',
        'nbre_emplois_prevu' => 'integer',
        'nbre_beneficiaire_prevu' => 'integer',
        'nbre_micro_projet_prevu' => 'integer',
    ];

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function guichet()
    {
        return $this->belongsTo(Guichet::class, 'guichet_id');
    }

    public function workflowVersion()
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version', 'code');
    }
}
