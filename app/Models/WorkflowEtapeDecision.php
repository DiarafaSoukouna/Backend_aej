<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtapeDecision extends Model
{
    protected $fillable = [
        'etape_code',
        'name',
        'description',
    ];

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class);
    }

    public function outcomes()
    {
        return $this->hasMany(WorkflowDecisionOutcome::class);
    }
}
