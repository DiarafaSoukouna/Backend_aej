<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Personnel extends Authenticatable

{
      use HasApiTokens;
    protected $fillable = [
        'nom', 
        'prenom',
        'email',
        'telephone',
        'adresse',
        'role_id',
        'is_active',
        'fonction_id',
        'mot_de_passe',
        'remember_token'
        
    ];
      protected $hidden = [

        'mot_de_passe',

        'remember_token',

    ];
          
}
