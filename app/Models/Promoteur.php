<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promoteur extends Model
{
    use HasFactory;
    protected $fillable = [
        'profile',
        'nom',
        'prenom',
        'email',
        'telephone',
        'tranche_age',
        'datenaissance',
        'lieunaissance',
        'matriculeaej',
        'numerocni',
        'numerocmu',
        'numerocnps',
        'raison_sociale',
        'handicap',
        'nomdupere',
        'nomdelamere',
        'sexe_id',
        'personnel_id',
        'lieuhabitation_id',
        'agenceregionale_id',
        'secteuractivite_id',
        'soussecteuractivite_id',
        'situationmatrimoniale_id',
        'typesituationhandicap_id',
        'typepieceidentite_id',
        'niveauetude_id',
        'paysnationalite_id',
    ];

    protected $casts = [
        'datenaissance' => 'date',
        'statut' => 'boolean',
    ];

    public function sexe()
    {
        return $this->belongsTo(Sexe::class, 'sexe_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function lieuHabitation()
    {
        return $this->belongsTo(LieuHabitation::class, 'lieuhabitation_id');
    }

    public function agenceRegionale()
    {
        return $this->belongsTo(AgenceRegionale::class, 'agenceregionale_id');
    }

    public function secteurActivite()
    {
        return $this->belongsTo(Secteur::class, 'secteuractivite_id');
    }

    public function sousSecteurActivite()
    {
        return $this->belongsTo(SousSecteur::class, 'soussecteuractivite_id');
    }

    public function situationMatrimoniale()
    {
        return $this->belongsTo(SituationMatrimoniale::class, 'situationmatrimoniale_id');
    }

    public function typeSituationHandicap()
    {
        return $this->belongsTo(TypeSituationHandicap::class, 'typesituationhandicap_id');
    }

    public function typePieceIdentite()
    {
        return $this->belongsTo(TypePieceIdentite::class, 'typepieceidentite_id');
    }

    public function niveauEtude()
    {
        return $this->belongsTo(NiveauEtude::class, 'niveauetude_id');
    }

    public function paysNationalite()
    {
        return $this->belongsTo(Pays::class, 'paysnationalite_id');
    }

    public function microProjets()
    {
        return $this->hasMany(MicroProjet::class, 'promoteur_id');
    }
}
