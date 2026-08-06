<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Projet extends Model
{
    use HasFactory;

    protected $fillable = [
        'secteur_id',
        'titre',
    ];

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteur_id');
    }

    public function zonesIntervention()
    {
        return $this->hasMany(ZoneIntervention::class, 'projet_id');
    }

    public function dispositifs()
    {
        return $this->hasMany(Dispositif::class, 'projet_id');
    }
}
