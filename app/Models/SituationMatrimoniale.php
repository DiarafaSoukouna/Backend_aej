<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SituationMatrimoniale extends Model
{
    protected $table = 'situations_matrimoniales';

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
