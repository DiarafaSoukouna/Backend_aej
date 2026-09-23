<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExecutionLigneDecaissement extends Model
{
    use HasFactory;

    protected $table = 'execution_ligne_decaissements';

    protected $fillable = [
        'ligne_decaissement_id',
        'statut',
        'mode_decaisse',
        'date_decaisse',
        'justificatif_path',
        'observations',
    ];

    protected $casts = [
        'date_decaisse' => 'date',
    ];

    public function ligneDecaissement()
    {
        return $this->belongsTo(LigneDecaissement::class, 'ligne_decaissement_id');
    }
}