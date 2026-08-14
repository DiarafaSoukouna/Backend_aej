<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    protected $fillable = [
        'micro_projet_id',
        'auteur_id',
        'content',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function auteur()
    {
        return $this->belongsTo(Personnel::class, 'auteur_id');
    }
}
