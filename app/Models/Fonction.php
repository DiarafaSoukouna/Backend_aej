<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fonction extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'description',
        'service_id',
    ];
}
