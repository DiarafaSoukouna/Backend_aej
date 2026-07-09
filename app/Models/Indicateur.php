<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicateur extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'type_valeur',
        'unite',
        'statut',
    ];
}
