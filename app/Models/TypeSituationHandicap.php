<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeSituationHandicap extends Model
{
    protected $table = 'types_situation_handicap';

    protected $fillable = [
        'libelle',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'int';
}
