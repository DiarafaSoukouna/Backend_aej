<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisme extends Model
{
    protected $fillable = [
        'nom',
        'sigle',
        'type',
        'site_web',
        'description',
        'adresse',
        'telephone',
        'email',
    ];
}
