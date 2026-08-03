<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowDecisionOutcome extends Model
{
    protected $table = 'workflow_decision_outcome';
    
    public $timestamps = false;
    
    protected $fillable = [
        'code',
        'label',
    ];
}
