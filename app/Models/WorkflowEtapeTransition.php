<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtapeTransition extends Model
{
    protected $fillable = [
        'version',
        'from_etape_code',
        'to_etape_code',
        'transition_type',
        'sequence_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function version()
    {
        return $this->belongsTo(WorkflowVersion::class);
    }

    public function fromEtape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'from_etape_code');
    }

    public function toEtape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'to_etape_code');
    }
}
