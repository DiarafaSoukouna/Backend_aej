<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suivi extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'jeune_id',
        'libelle',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class);
    }

    public function jeune()
    {
        return $this->belongsTo(Jeune::class);
    }
}
