<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenceRegionale extends Model
{
    protected $fillable = [
        'external_id',
        'code',
        'nom',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}
