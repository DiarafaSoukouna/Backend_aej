<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LotImportation extends Model
{
    use HasFactory;

    protected $table = 'lots_importation';

    public $timestamps = false;

    protected $fillable = [
        'micro_projet_id',
        'code',
        'nom_promoteur',
        'prenom_promoteur',
        'montant_sollicite',
    ];

    protected $casts = [
        'montant_sollicite' => 'decimal:2',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }
}
