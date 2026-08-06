<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkflowInstanceComment extends Model
{
    use HasFactory;

    protected $table = 'workflow_instance_comment';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'workflow_instance_id',
        'etape_code',
        'commented_by_id',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'etape_code', 'code');
    }

    public function commentedBy()
    {
        return $this->belongsTo(Personnel::class, 'commented_by_id');
    }
}
