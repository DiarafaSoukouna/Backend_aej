<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuiviRemboursement extends Model
{
    protected $fillable = [
        'remboursement_id',
        'montant_paye',
        'date_paiement',
        'justificatif_path',
        'observations',
    ];

    protected $casts = [
        'montant_paye' => 'decimal:2',
        'date_paiement' => 'date',
    ];

    public $timestamps = false;

    public function remboursement()
    {
        return $this->belongsTo(Remboursement::class, 'remboursement_id');
    }
}
