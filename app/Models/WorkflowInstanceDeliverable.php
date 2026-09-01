<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkflowInstanceDeliverable extends Model
{
    use HasFactory;

    protected $table = 'workflow_instance_deliverable';

    public $timestamps = false;

    protected $fillable = [
        'workflow_instance_id',
        'deliverable_code',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'observations',
        'produced_at',
        'produced_by_id',
    ];

    protected $casts = [
        'produced_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function workflowInstance()
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function deliverable()
    {
        return $this->belongsTo(WorkflowDeliverable::class, 'deliverable_code', 'code');
    }

    public function producedBy()
    {
        return $this->belongsTo(Personnel::class, 'produced_by_id');
    }
}
