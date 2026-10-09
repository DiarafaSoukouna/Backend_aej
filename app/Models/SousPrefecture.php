<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SousPrefecture extends Model
{
    use HasFactory;

    protected $table = 'sous_prefectures';

    protected $fillable = [
        'departement_id',
        'code',
        'nom',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public $timestamps = false;

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function communes()
    {
        return $this->hasMany(Commune::class);
    }
}