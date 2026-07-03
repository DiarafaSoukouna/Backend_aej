<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'description',
        'direction_id',
    ];

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }
}
