<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuiviDecaissement extends Model
{
    protected $fillable = [
        'ligne_decaissement_id',
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

    public function ligneDecaissement()
    {
        return $this->belongsTo(LigneDecaissement::class, 'ligne_decaissement_id');
    }
}
