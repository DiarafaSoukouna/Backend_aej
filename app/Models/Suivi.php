<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suivi extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'promoteur_id',
        'libelle',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class);
    }

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class);
    }
}
