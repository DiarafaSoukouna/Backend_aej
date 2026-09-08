<?php

namespace App\Models; 

use Illuminate\Database\Eloquent\Model;

class Indicateur extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'code',
        'libelle',
        'description',
        'unite',
        'valeur_cible',
        'statut',
    ];

    protected $casts = [
        'statut' => 'boolean',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class);
    }

    public function suivis()
    {
        return $this->hasMany(IndicateurSuivi::class);
    }
}
