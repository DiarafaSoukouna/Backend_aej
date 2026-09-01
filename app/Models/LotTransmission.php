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
        'dossiers',
    ];

    protected $casts = [
        'date_transmission' => 'date',
        'taux_recouvrement' => 'decimal:2',
        'duree_differee' => 'integer',
        'duree_remboursement' => 'integer',
    ];

    protected $appends = [
        'micro_projets',
    ];

    public function organisme()
    {
        return $this->belongsTo(OrganismeFinancement::class, 'organisme_id');
    }

    public function getMicroProjetsAttribute()
    {
        if (empty($this->dossiers)) return collect();

        $codes = array_values(array_filter(
            array_map('trim', explode('|', $this->dossiers))
        ));

        if (empty($codes)) return collect();

        $microProjets = MicroProjet::whereIn('code', $codes)->get()->keyBy('code');
        return collect($codes)->map(fn($code) => $microProjets->get($code))->filter()->values();
    }
}
