<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decaissement extends Model
{
    protected $fillable = [
        'numero_decaissement',
        'plan_decaissement_id',
        'agence_id',
        'montant_decaisse',
        'date_decaissement',
        'reference_banque',
        'statut',
        'justificatif_path',
        'observations',
    ];

    protected $casts = [
        'montant_decaisse' => 'decimal:2',
        'date_decaissement' => 'date',
    ];

    public function planDecaissement()
    {
        return $this->belongsTo(PlanDecaissement::class, 'plan_decaissement_id');
    }

    public function agence()
    {
        return $this->belongsTo(AgenceRegionale::class, 'agence_id');
    }
}
