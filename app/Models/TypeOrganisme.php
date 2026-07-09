<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeOrganisme extends Model
{
    protected $fillable = [
        'code',
        'libelle',
    ];
}
