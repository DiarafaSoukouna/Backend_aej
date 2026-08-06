<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guichet extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'couleur',
        'montant_min',
        'montant_max',
        'is_active',
        'is_form_active',
    ];

    protected $casts = [
        'montant_min' => 'decimal:2',
        'montant_max' => 'decimal:2',
        'is_active' => 'boolean',
        'is_form_active' => 'boolean',
    ];
}
