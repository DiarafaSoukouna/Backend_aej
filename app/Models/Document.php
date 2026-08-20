<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'name',
        'path',
        'type',
        'size',
        'url',
        'created_by',
        'micro_projet_id',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_by' => 'integer',
        'micro_projet_id' => 'integer',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Personnel::class, 'created_by');
    }
}
