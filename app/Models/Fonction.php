<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fonction extends Model
{
    use HasFactory;

    protected $table = 'fonctions';

    protected $fillable = [
        'nom',
        'code',
        'description',
        'structure_id',
    ];
    
    public $timestamps = false;

    public function structure()
    {
        return $this->belongsTo(Structure::class, 'structure_id');
    }

    public function personnels()
    {
        return $this->hasMany(Personnel::class, 'fonction_id');
    }
}
