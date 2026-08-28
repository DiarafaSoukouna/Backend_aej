<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LotMicroProjet extends Model
{
    use HasFactory;

    protected $table = 'lots_micro_projets';

    protected $fillable = [
        'lot_id',
        'micro_projet_id',
        'statut',
    ];

    protected $casts = [
        'statut' => 'string',
    ];

    public function lot()
    {
        return $this->belongsTo(LotTransmission::class, 'lot_id');
    }

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }
}
