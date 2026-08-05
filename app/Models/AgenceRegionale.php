<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenceRegionale extends Model
{
    protected $table = 'agences_regionales';

    protected $fillable = [
        'code',
        'nom',
        'latitude',
        'longitude',
        'contact',
        'localisation',
        'adresse',
        'telephone',
        'email',
        'chef_agence_id',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    public function chefAgence()
    {
        return $this->belongsTo(Personnel::class, 'chef_agence_id');
    }
}
