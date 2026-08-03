<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeSituationHandicap extends Model
{
    protected $fillable = [
        'external_id',
        'code',
        'libelle',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}
