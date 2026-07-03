<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'code',
        'libelle',
        'description',
    ];

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
