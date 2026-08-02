<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SituationMatrimoniale extends Model
{
    protected $fillable = [
        'external_id',
        'libelle',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}
