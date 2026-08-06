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
        'intitule',
        'budget_alloue',
        'nbre_emplois_prevu',
        'nbre_beneficiaire_prevu',
        'nbre_micro_projet_prevu',
    ];

    protected $casts = [
        'budget_alloue' => 'decimal:2',
        'nbre_emplois_prevu' => 'integer',
        'nbre_beneficiaire_prevu' => 'integer',
        'nbre_micro_projet_prevu' => 'integer',
    ];

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }
}
