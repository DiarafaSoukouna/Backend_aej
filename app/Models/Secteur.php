<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secteur extends Model
{
    protected $table = 'secteurs';

    protected $fillable = [
        'nom',
        'libelle',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    public function sousSecteurs()
    {
        return $this->hasMany(SousSecteur::class, 'secteur_id');
    }
}
