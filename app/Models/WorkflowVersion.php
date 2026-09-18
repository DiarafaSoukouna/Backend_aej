<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowVersion extends Model
{
    protected $fillable = [
        'workflow_code',
        'version',
        'code',
        'name',
        'description',
        'etape_start_code',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_code', 'code');
    }

    public function etapes()
    {
        return $this->hasMany(WorkflowEtape::class, 'workflow_version', 'code');
    }

    public function etape_start()
    {
        return $this->hasOne(WorkflowEtape::class, 'code', 'etape_start_code');
    }
}
