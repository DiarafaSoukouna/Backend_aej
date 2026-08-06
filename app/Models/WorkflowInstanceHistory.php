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
        'performed_by_id',
        'entered_at',
        'exited_at',
        'comments',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'etape_code', 'code');
    }

    public function performedBy()
    {
        return $this->belongsTo(Personnel::class, 'performed_by_id');
    }
}
