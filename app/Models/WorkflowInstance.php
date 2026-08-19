<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkflowInstance extends Model
{
    use HasFactory;

    protected $table = 'workflow_instance';

    public $timestamps = false;

    protected $fillable = [
        'micro_projet_id',
        'workflow_version',
        'current_etape_code',
        'next_etape_code',
        'statut',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function history()
    {
        return $this->hasMany(WorkflowInstanceHistory::class, 'workflow_instance_id');
    }

    public function deliverables()
    {
        return $this->hasMany(WorkflowInstanceDeliverable::class, 'workflow_instance_id');
    }

    public function comments()
    {
        return $this->hasMany(WorkflowInstanceComment::class, 'workflow_instance_id');
    }

    public function currentEtape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'current_etape_code', 'code');
    }

    public function nextEtape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'next_etape_code', 'code');
    }
}
