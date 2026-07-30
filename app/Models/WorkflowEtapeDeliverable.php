<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtapeDeliverable extends Model
{
    protected $fillable = [
        'etape_code',
        'name',
        'description',
        'is_mandatory',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class);
    }
}
