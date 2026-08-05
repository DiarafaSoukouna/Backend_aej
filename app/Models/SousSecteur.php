<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SousSecteur extends Model
{
    protected $table = 'sous_secteurs';

    protected $fillable = [
        'secteur_id',
        'libelle',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    public function secteurActivite()
    {
        return $this->belongsTo(Secteur::class, 'secteur_id');
    }
}
