<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'personnel_id',
        'titre',
        'message',
        'lue',
    ];

    protected $casts = [
        'lue' => 'boolean',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }
}
