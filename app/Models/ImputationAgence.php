<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImputationAgence extends Model
{
    use HasFactory;

    protected $table = 'imputations_agences';

    public $timestamps = true;

    protected $fillable = [
        'agence_id',
        'micro_projet_id',
        'statut',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function agence()
    {
        return $this->belongsTo(AgenceRegionale::class, 'agence_id');
    }

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }
}
