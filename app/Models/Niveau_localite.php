<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Niveau_localite extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Niveau_localite::class, 'parent_id');
    }
}
