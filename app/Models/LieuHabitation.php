<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LieuHabitation extends Model
{
    protected $fillable = [
        'external_id',
        'nom',
        'ville_id',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }
}
