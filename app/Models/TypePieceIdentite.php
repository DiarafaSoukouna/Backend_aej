<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypePieceIdentite extends Model
{
    protected $table = 'types_pieces_identites';

    protected $fillable = [
        'libelle',
        'description',
        'actif',
        'synced_at',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'int';
}
