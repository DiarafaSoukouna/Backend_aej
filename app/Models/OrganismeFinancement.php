<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganismeFinancement extends Model
{
    protected $fillable = [
        'nom',
        'sigle',
        'type',
        'site_web',
        'description',
        'adresse',
        'telephone',
        'email',
        'region_id',
    ];

    public function typeOrganisme()
    {
        return $this->belongsTo(TypeOrganisme::class, 'type');
    }

    // public function region()
    // {
    //     return $this->belongsTo(Region::class, 'region_id');
    // }
}
