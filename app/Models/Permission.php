<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'role_id',
        'module',
        'autorise',
        'acces',
        'full_access',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
