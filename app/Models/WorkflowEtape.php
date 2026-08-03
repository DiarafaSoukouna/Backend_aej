<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtape extends Model
{
    protected $fillable = [
        'workflow_version',
        'parent_etape_code',
        'code',
        'name',
        'impact',
        'statut',
        'description',
        'order',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function workflowVersion()
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version', 'code');
    }

    public function parentEtape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'parent_etape_code');
    }

    public function children()
    {
        return $this->hasMany(WorkflowEtape::class, 'parent_etape_code');
    }

    public function sla()
    {
        return $this->hasOne(WorkflowEtapeSla::class, 'etape_code', 'code');
    }

    public function deliverables()
    {
        return $this->hasMany(WorkflowEtapeDeliverable::class, 'etape_code', 'code');
    }

    public function roles()
    {
        return $this->hasMany(WorkflowEtapeRole::class, 'etape_code', 'code');
    }

    public function decision()
    {
        return $this->hasOne(WorkflowEtapeDecision::class, 'etape_code', 'code');
    }

    // Transitions table is commented out in schema
    // public function transitionsFrom()
    // {
    //     return $this->hasMany(WorkflowEtapeTransition::class, 'from_etape_code');
    // }

    // public function transitionsTo()
    // {
    //     return $this->hasMany(WorkflowEtapeTransition::class, 'to_etape_code');
    // }
}
