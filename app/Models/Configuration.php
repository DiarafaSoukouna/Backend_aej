<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $fillable = [
        'logo_systeme',
        'sigle_systeme',
        'intitule_systeme',
        'sigle_structure',
        'intitule_structure',
        'logo_structure',
        'adresse_sociale_structure',
        'email_structure',
        'whatsapp_structure',
        'telephone_structure',
        'sigle_monnaie_pays',
        'sigle_devise_principale',
        'taux_devise_principale',
        'mise_en_maintenance',
        'delai_inactivite_minutes',
        'nombre_session_possible',
        'nombre_tentatives_connexion',
        'delai_code_otp_minutes',
        'delai_changement_mdp_mois',
        'delai_suppression_secondes',
        'code_instance_whatsapp',
        'token_instance_whatsapp',
        'email_notifications',
        'mot_de_passe_email_notifications',
        'smtp_email_notifications',
        'smtp_host_notifications',
        'smtp_port_notifications',
        'smtp_encrypt_notifications',
    ];
}
