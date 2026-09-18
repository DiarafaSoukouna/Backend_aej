<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Garantie extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'organisme_id',
        'montant_garantie',
        'date_rappel',
        'statut',
    ];

    protected $casts = [
        'montant_garantie' => 'decimal:2',
        'date_rappel' => 'date',
    ];

    public function microProjet(): BelongsTo
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function organisme(): BelongsTo
    {
        return $this->belongsTo(OrganismeFinancement::class, 'organisme_id');
    }
}
