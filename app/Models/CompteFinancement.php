<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompteFinancement extends Model
{
    protected $fillable = [
        'organisme_id',
        'micro_projet_id',
        'etat_ouverture',
        'localite_ouverture',
        'date_ouverture',
        'avis_partenaire',
        'observation',
    ];

    protected $casts = [
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
}
