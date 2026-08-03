<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtapeSla extends Model
{
    protected $table = 'workflow_etapes_sla';
    
    public $timestamps = false;
    
    protected $fillable = [
        'etape_code',
        'duration_value',
        'duration_unit',
        'description',
    ];

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class);
    }
}
