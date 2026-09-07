<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicateurSuivi extends Model
{
    protected $table = 'indicateurs_suivi';
    
    protected $fillable = [
        'indicateur_id',
        'promoteur_id',
        'valeur',
        'periode'
    ];

    public function indicateur()
    {
        return $this->belongsTo(Indicateur::class);
    }

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class);
    }
}
