<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEtapeRole extends Model
{
    protected $fillable = [
        'etape_code',
        'role_code',
        'responsibility',
    ];

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
