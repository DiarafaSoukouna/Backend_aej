<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtapeRole extends Model
{
    protected $table = 'workflow_etapes_roles';
    
    public $timestamps = false;
    
    protected $fillable = [
        'etape_code',
        'role_code',
        'action',
    ];

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'etape_code', 'code');
    }

    public function role()
    {
        return $this->belongsTo(WorkflowRole::class, 'role_code', 'code');
    }
}
