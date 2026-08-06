<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pays extends Model
{
    protected $table = 'pays';

    protected $fillable = [
        'code_iso',
        'nom',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'int';
}
