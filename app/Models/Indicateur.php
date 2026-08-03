<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicateur extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'nom',
        'description',
        'type_valeur',
        'unite',
        'statut',
    ];

    protected $casts = [
        'statut' => 'boolean',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class);
    }

    public function indicateursSuivi()
    {
        return $this->hasMany(IndicateurSuivi::class);
    }
}
