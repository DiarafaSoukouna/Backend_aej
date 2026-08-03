<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $table = 'communes';

    protected $fillable = [
        'external_id',
        'nom',
        'ville_id',
        'divisionregionaleaej_id',
        'guichetemploi_id',
        'code',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    public function divisionRegionale()
    {
        return $this->belongsTo(DivisionRegionale::class, 'divisionregionaleaej_id');
    }

    public function guichetEmploi()
    {
        return $this->belongsTo(Guichet::class, 'guichetemploi_id');
    }
}
