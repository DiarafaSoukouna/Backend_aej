<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ville extends Model
{
    use HasFactory;

    protected $table = 'villes';

    protected $fillable = [
        'commune_id',
        'code',
        'nom',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public $timestamps = false;

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}