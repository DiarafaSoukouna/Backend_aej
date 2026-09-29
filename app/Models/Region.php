<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{
    use HasFactory;

    protected $table = 'regions';

    protected $fillable = [
        'code',
        'nom',
    ];

    public $timestamps = false;

    public function departements()
    {
        return $this->hasMany(Departement::class);
    }

    public function entreprises()
    {
        return $this->hasMany(Entreprise::class);
    }
}