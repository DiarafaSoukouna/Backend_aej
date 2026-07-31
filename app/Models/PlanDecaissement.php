<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanDecaissement extends Model
{
    protected $fillable = [
        'budget_id',
        'code',
        'intitule',
        'montant_planifie',
        'date_prevue',
    ];

    protected $casts = [
        'montant_planifie' => 'decimal:2',
        'date_prevue' => 'date',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function decaissements()
    {
        return $this->hasMany(Decaissement::class);
    }
}
