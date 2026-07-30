<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowDecisionOutcome extends Model
{
    protected $fillable = [
        'decision_id',
        'code',
        'label',
        'next_etape_code',
    ];

    public function decision()
    {
        return $this->belongsTo(WorkflowEtapeDecision::class);
    }

    public function nextEtape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'next_etape_code');
    }
}
