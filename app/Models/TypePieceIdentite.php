<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypePieceIdentite extends Model
{
    protected $fillable = [
        'external_id',
        'libelle',
        'description',
        'actif',
        'synced_at',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'synced_at' => 'datetime',
    ];
}
