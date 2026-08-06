<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'raison_sociale',
        'sigle',
        'rccm',
        'ninea',
        'type_entreprise_id',
        'adresse',
        'contact',
        'email',
        'region_id',
        'commune_id',
    ];

    public function typeEntreprise()
    {
        return $this->belongsTo(TypeEntreprise::class, 'type_entreprise_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    public function embauches()
    {
        return $this->hasMany(Embauche::class, 'entreprise_id');
    }
}
