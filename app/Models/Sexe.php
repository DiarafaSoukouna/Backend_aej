<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sexe extends Model
{
    protected $table = 'sexes';

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
