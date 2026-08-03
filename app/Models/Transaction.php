<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions_financieres';

    protected $fillable = [
        'micro_projet_id',
        'promoteur_id',
        'categorie_id',
        'libelle',
        'type',
        'montant',
        'statut',
        'mode_paiement',
        'reference',
        'justificatif_path',
        'observations',
        'date',
        'saisi_par',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date' => 'date',
        'type' => 'string',
        'statut' => 'string',
        'mode_paiement' => 'string',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class);
    }

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class);
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
