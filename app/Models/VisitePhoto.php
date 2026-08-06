<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitePhoto extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'exploitation_id',
        'photo_url',
        'description',
        'prise_le',
        'prise_par_id',
    ];

    protected $casts = [
        'prise_le' => 'datetime',
    ];

    public function exploitation()
    {
        return $this->belongsTo(Exploitation::class, 'exploitation_id');
    }

    public function prisePar()
    {
        return $this->belongsTo(Personnel::class, 'prise_par_id');
    }
}
