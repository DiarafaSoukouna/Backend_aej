<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NiveauHierarchie extends Model
{
    use HasFactory;

    protected $table = 'niveau_hierarchie';

    protected $fillable = [
        'libelle',
        'niveau',
        'description',
    ];

    public $timestamps = false;

    public function structures()
    {
        return $this->hasMany(Structure::class, 'niveau_id');
    }
}