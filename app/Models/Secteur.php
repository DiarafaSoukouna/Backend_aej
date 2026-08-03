<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secteur extends Model
{
    protected $fillable = [
        'external_id',
        'libelle',
        'nom',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}
