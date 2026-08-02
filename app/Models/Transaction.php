<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions_financieres';

    protected $fillable = [
        'micro_projet_id',
        'categorie_id',
        'libelle',
        'type',
        'montant',
        'date',
        'justificatif_path',
        'observations',
        'saisi_par',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date' => 'date',
        'type' => 'string',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class);
    }

    public function categorie()
    {
        return $this->belongsTo(CategorieTransaction::class, 'categorie_id');
    }

    public function saisiPar()
    {
        return $this->belongsTo(Personnel::class, 'saisi_par');
    }
}
