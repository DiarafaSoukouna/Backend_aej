<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'code',
        'nom',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function villes()
    {
        return $this->hasMany(Ville::class, 'departement_id');
    }

    public function zonesIntervention()
    {
        return $this->hasMany(ZoneIntervention::class, 'departement_id');
    }
}
