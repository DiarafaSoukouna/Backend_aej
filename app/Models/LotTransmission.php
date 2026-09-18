<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LotTransmission extends Model
{
    use HasFactory;

    protected $table = 'lots_transmission';

    protected $fillable = [
        'organisme_id',
        'guichet_id',
        'code',
        'titre',
        'fichier_repartition',
        'fichier_courrier',
        'reference_courrier',
        'reference_convention',
        'date_transmission',
        'taux_recouvrement',
        'duree_differee',
        'duree_remboursement',
        'statut',
    ];

    protected $casts = [
        'date_transmission' => 'date',
        'taux_recouvrement' => 'decimal:2',
        'duree_differee' => 'integer',
        'duree_remboursement' => 'integer',
    ];

    public function organisme()
    {
        return $this->belongsTo(OrganismeFinancement::class, 'organisme_id');
    }

    public function guichet()
    {
        return $this->belongsTo(Guichet::class, 'guichet_id');
    }

    public function microProjets()
    {
        return $this->belongsToMany(MicroProjet::class, 'lots_micro_projets', 'lot_id', 'micro_projet_id')
                    ->withPivot('statut')
                    ->withTimestamps();
    }
}
