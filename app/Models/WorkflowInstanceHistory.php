<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkflowInstanceHistory extends Model
{
    use HasFactory;

    protected $table = 'workflow_instance_history';

    public $timestamps = false;

    protected $fillable = [
        'workflow_instance_id',
        'etape_code',
        'role_code',
        'action',
        'comment',
        'acted_by',
        'acted_at',
        'observation',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'etape_code', 'code');
    }

    public function actedBy()
    {
        return $this->belongsTo(Personnel::class, 'acted_by');
    }
}
