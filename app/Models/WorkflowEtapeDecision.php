<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtapeDecision extends Model
{
    protected $table = 'workflow_etapes_decision';
    
    public $timestamps = false;
    
    protected $fillable = [
        'etape_code',
        'code',
        'name',
        'description',
        'outcomes',
    ];

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class);
    }
}
