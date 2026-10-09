<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Structure extends Model
{
    use HasFactory;

    protected $table = 'structures';

    protected $fillable = [
        'nom',
        'code',
        'description',
        'niveau_id',
        'parent_id',
    ];

    public $timestamps = false;

    public function niveauHierarchie()
    {
        return $this->belongsTo(NiveauHierarchie::class, 'niveau_id');
    }

    public function parent()
    {
        return $this->belongsTo(Structure::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Structure::class, 'parent_id');
    }

    public function fonctions()
    {
        return $this->hasMany(Fonction::class, 'structure_id');
    }

    public function personnels()
    {
        return $this->hasMany(Personnel::class, 'structure_id');
    }
}