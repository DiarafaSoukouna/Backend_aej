<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    protected $fillable = [
        'external_id',
        'departement_id',
        'code',
        'nom',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function communes()
    {
        return $this->hasMany(Commune::class, 'ville_id');
    }

    public function lieuxHabitation()
    {
        return $this->hasMany(LieuHabitation::class, 'ville_id');
    }
}
