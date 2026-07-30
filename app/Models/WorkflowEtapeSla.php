<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtapeSla extends Model
{
    protected $fillable = [
        'etape_code',
        'duration_value',
        'duration_unit',
        'delay_type',
        'description',
    ];

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class);
    }
}
