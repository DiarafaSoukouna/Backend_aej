<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtapeDeliverable extends Model
{
    protected $table = 'workflow_etapes_deliverable';

    public $timestamps = false;

    protected $fillable = [
        'etape_code',
        'deliverable_code',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function etape()
    {
        return $this->belongsTo(
            WorkflowEtape::class,
            'etape_code',
            'code'
        );
    }

    public function deliverable()
    {
        return $this->belongsTo(WorkflowDeliverable::class, 'deliverable_code', 'code');
    }
}
