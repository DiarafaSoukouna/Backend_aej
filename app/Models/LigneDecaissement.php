<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LigneDecaissement extends Model
{
    use HasFactory;

    protected $table = 'ligne_decaissements';

    protected $fillable = [
        'plan_decaissement_id',
        'numero_ligne',
        'object_ligne',
        'montant_ligne',
        'mode_decaisse',
        'date_prevue',
        'intitule_prestataire',
        'numero_compte',
        'contact',
        'statut',
        'observations',
    ];

    protected $casts = [
        'montant_ligne' => 'decimal:2',
        'date_prevue' => 'date',
    ];

    public function planDecaissement()
    {
        return $this->belongsTo(PlanDecaissement::class, 'plan_decaissement_id');
    }

    public function decaissements()
    {
        return $this->hasMany(Decaissement::class, 'ligne_decaissement_id');
    }

    public function decaissementsDeclarations()
    {
        return $this->hasMany(DecaissementsDeclaration::class, 'ligne_decaissement_id');
    }
}
