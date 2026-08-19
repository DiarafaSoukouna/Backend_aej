<?php

namespace App\Models;

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
        'agence_id',
        'fonction_id',
        'organisme_id',
        'mot_de_passe',
        'remember_token'
        
    ];
      protected $hidden = [

        'mot_de_passe',

        'remember_token',

    ];
          
    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    public function agence()
    {
        return $this->belongsTo(AgenceRegionale::class);
    }
    
    public function fonction()
    {
        return $this->belongsTo(Fonction::class);
    }
    
    public function organisme()
    {
        return $this->belongsTo(OrganismeFinancement::class);
    }
}
