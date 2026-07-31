<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicateurSuivi extends Model
{
    protected $fillable = [
        'indicateur_id',
        'valeur',
    ];

    public function indicateur()
    {
        return $this->belongsTo(Indicateur::class);
    }
}
