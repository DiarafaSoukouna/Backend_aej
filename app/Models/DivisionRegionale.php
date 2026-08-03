<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisionRegionale extends Model
{
    protected $table = 'division_regionale';

    protected $fillable = [
        'external_id',
        'code',
        'nom',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function communes()
    {
        return $this->hasMany(Commune::class, 'divisionregionaleaej_id');
    }
}
