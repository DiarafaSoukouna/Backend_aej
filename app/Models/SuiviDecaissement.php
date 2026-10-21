<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuiviDecaissement extends Model
{
    protected $fillable = [
        'decaissement_id',
        'montant_decaisse',
        'date_decaisse',
        'justificatif_path',
        'observations',
    ];

    protected $casts = [
        'montant_decaisse' => 'decimal:2',
        'date_decaisse' => 'date',
    ];

    public $timestamps = false;

    public function decaissement()
    {
        return $this->belongsTo(Decaissement::class, 'decaissement_id');
    }
}
