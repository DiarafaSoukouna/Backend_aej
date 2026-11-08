<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RappelHistorique extends Model
{
    use HasFactory;

    protected $table = 'rappel_historiques';

    protected $fillable = [
        'micro_projet_id',
        'promoteur_id',
        'personnel_id',
        'type',
        'canal',
        'numero_rappel',
        'statut',
        'titre',
        'description',
        'date_action',
        'date_promesse',
        'montant_promis',
        'montant_concerne',
        'echeances_concernees',
        'destinataire_email',
        'fichier_joint',
        'metadata',
    ];

    protected $casts = [
        'date_action'          => 'datetime',
        'date_promesse'        => 'date',
        'montant_promis'       => 'decimal:2',
        'montant_concerne'     => 'decimal:2',
        'numero_rappel'        => 'integer',
        'echeances_concernees' => 'array',
        'metadata'             => 'array',
    ];

    /**
     * Projet concerné par ce rappel / historique
     */
    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    /**
     * Promoteur du projet
     */
    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class, 'promoteur_id');
    }

    /**
     * Agent / utilisateur ayant enregistré l'événement (null pour les rappels automatiques système)
     */
    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }
}

