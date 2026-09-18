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

     public function permissions()
    {
        return $this->role ? $this->role->permissions : collect();
    }

    public function hasPermission(string $module, string $action = null): bool
    {
        if (!$this->role) {
            return false;
        }
 
        $permission = $this->role->permissions()->where('module', $module)->first();
 
        if (!$permission) {
            return false;
        }
 
        if ($permission->full_access) {
            return true;
        }
 
        if ($action === null) {
            return $permission->autorise;
        }
 
        $acces = json_decode($permission->acces, true) ?? [];
 
        return in_array($action, $acces);
    }
 
    public function getAllPermissions(): array
    {
        if (!$this->role) {
            return [];
        }
 
        return $this->role->permissions->map(function ($permission) {
            return [
                'module' => $permission->module,
                'autorise' => $permission->autorise,
                'acces' => json_decode($permission->acces, true) ?? [],
                'full_access' => $permission->full_access,
            ];
        })->toArray();
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
