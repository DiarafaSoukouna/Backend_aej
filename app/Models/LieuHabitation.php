<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LieuHabitation extends Model
{
    protected $table = 'lieux_habitation';

    protected $fillable = [
        'nom',
        'ville_id',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }
}
