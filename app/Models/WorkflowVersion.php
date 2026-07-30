<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowVersion extends Model
{
    protected $fillable = [
        'workflow_code',
        'name',
        'description',
        'version',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function etapes()
    {
        return $this->hasMany(WorkflowEtape::class);
    }

    public function transitions()
    {
        return $this->hasMany(WorkflowEtapeTransition::class);
    }
}
