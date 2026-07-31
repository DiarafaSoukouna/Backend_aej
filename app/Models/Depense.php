<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'categorie',
        'intitule',
        'montant_depense',
        'date_depense',
        'justificatif_path',
        'observations',
        'saisi_par',
    ];

    protected $casts = [
        'montant_depense' => 'decimal:2',
        'date_depense' => 'date',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class);
    }

    public function saisiPar()
    {
        return $this->belongsTo(Personnel::class, 'saisi_par');
    }
}
