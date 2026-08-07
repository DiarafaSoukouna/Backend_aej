<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decaissement extends Model
{
    protected $fillable = [
        'plan_id',
        'agence_id',
        'montant_decaisse',
        'date_decaissement',
        'reference_banque',
        'statut',
        'observations',
    ];

    protected $casts = [
        'montant_decaisse' => 'decimal:2',
        'date_decaissement' => 'date',
    ];

    public function plan()
    {
        return $this->belongsTo(PlanDecaissement::class, 'plan_id');
    }

    public function agence()
    {
        return $this->belongsTo(AgenceRegionale::class, 'agence_id');
    }
}
