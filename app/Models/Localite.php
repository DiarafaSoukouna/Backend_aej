<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'couche_cartographique',
        'niveau_localite_id',
    ];

    public function niveau()
    {
        return $this->hasMany(Niveau_localite::class, "niveau_localite_id");
    }
}
