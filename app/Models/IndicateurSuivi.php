<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicateurSuivi extends Model
{
    protected $table = 'indicateurs_suivi';
    
    protected $fillable = [
        'indicateur_id',
        'valeur',
    ];

    public function indicateur()
    {
        return $this->belongsTo(Indicateur::class);
    }
}
