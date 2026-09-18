<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodicBulletin extends Model
{
    protected $table = 'periodic_bulletins';

    protected $fillable = [
        'periode_type',
        'periode_debut',
        'periode_fin',
        'organisme_id',
        'kpis',
        'commentaire_ia',
        'categorisation_payeurs',
        'pdf_path',
        'genere_par',
        'genere_auto',
    ];

    protected $casts = [
        'periode_debut'          => 'date',
        'periode_fin'            => 'date',
        'kpis'                   => 'array',
        'categorisation_payeurs' => 'array',
        'genere_auto'            => 'boolean',
    ];

    public function organisme()
    {
        return $this->belongsTo(OrganismeFinancement::class, 'organisme_id');
    }

    public function generePar()
    {
        return $this->belongsTo(Personnel::class, 'genere_par');
    }

    public function getPeriodeLabelAttribute(): string
    {
        return $this->periode_debut->format('d/m/Y') . ' - ' . $this->periode_fin->format('d/m/Y');
    }
}
