<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Secteur;
use App\Models\AgenceRegionale;
use App\Models\Guichet;
use App\Models\Dispositif;
use App\Models\OrganismeFinancement;
use App\Models\Commune;
use App\Models\Role;
use App\Models\Promoteur;

class ReferentielCacheService
{
    private const TTL = 86400; // 24 heures
    private const SHORT_TTL = 3600; // 1 heure

    /**
     * Obtenir tous les secteurs
     */
    public function getSecteurs()
    {
        return Cache::remember('referentiels:secteurs.all', self::TTL, function () {
            return Secteur::select('id', 'code', 'nom', 'libelle')->get();
        });
    }

    /**
     * Obtenir une agence par ID
     */
    public function getAgence($id)
    {
        return Cache::remember("referentiels:agences.{$id}", self::TTL, function () use ($id) {
            return AgenceRegionale::find($id);
        });
    }

    /**
     * Obtenir toutes les agences
     */
    public function getAgences()
    {
        return Cache::remember('referentiels:agences.all', self::TTL, function () {
            return AgenceRegionale::select('id', 'code', 'nom', 'contact')->get();
        });
    }

    /**
     * Obtenir les agences par région
     */
    public function getAgencesByRegion($regionId)
    {
        return Cache::remember("referentiels:agences.region.{$regionId}", self::TTL, function () use ($regionId) {
            return AgenceRegionale::where('region_id', $regionId)->get();
        });
    }

    /**
     * Obtenir tous les guichets
     */
    public function getGuichets()
    {
        return Cache::remember('referentiels:guichets.all', self::TTL, function () {
            return Guichet::select('id', 'code', 'nom', 'type')->get();
        });
    }

    /**
     * Obtenir tous les dispositifs
     */
    public function getDispositifs()
    {
        return Cache::remember('referentiels:dispositifs.all', self::TTL, function () {
            return Dispositif::select('id', 'code', 'nom')->get();
        });
    }

    /**
     * Obtenir tous les organismes de financement
     */
    public function getOrganismes()
    {
        return Cache::remember('referentiels:organismes.all', self::TTL, function () {
            return OrganismeFinancement::select('id', 'nom', 'sigle')->get();
        });
    }

    /**
     * Obtenir toutes les communes
     */
    public function getCommunes()
    {
        return Cache::remember('referentiels:communes.all', self::TTL, function () {
            return Commune::select('id', 'code', 'nom')->get();
        });
    }

    /**
     * Obtenir tous les rôles
     */
    public function getRoles()
    {
        return Cache::remember('referentiels:roles.all', self::TTL, function () {
            return Role::select('id', 'code', 'libelle')->get();
        });
    }

    /**
     * Obtenir le nombre total de micro-projets (mis en cache)
     */
    public function getMicroProjetsCount()
    {
        return Cache::remember('statistics:micro_projets.count', self::SHORT_TTL, function () {
            return \App\Models\MicroProjet::count();
        });
    }

    /**
     * Obtenir le nombre total de promoteurs (mis en cache)
     */
    public function getPromoteursCount()
    {
        return Cache::remember('statistics:promoteurs.count', self::SHORT_TTL, function () {
            return Promoteur::count();
        });
    }

    /**
     * Obtenir le nombre total de personnels (mis en cache)
     */
    public function getPersonnelsCount()
    {
        return Cache::remember('statistics:personnels.count', self::SHORT_TTL, function () {
            return \App\Models\Personnel::count();
        });
    }

    /**
     * Invalider le cache des secteurs
     */
    public function clearSecteurs()
    {
        Cache::forget('referentiels:secteurs.all');
    }

    /**
     * Invalider le cache des agences
     */
    public function clearAgences()
    {
        Cache::forget('referentiels:agences.all');
        // Invalider aussi les caches par région
        Cache::forget('referentiels:agences.region.*');
    }

    /**
     * Invalider le cache d'une agence spécifique
     */
    public function clearAgence($id)
    {
        Cache::forget("referentiels:agences.{$id}");
        Cache::forget('referentiels:agences.all');
    }

    /**
     * Invalider le cache des guichets
     */
    public function clearGuichets()
    {
        Cache::forget('referentiels:guichets.all');
    }

    /**
     * Invalider le cache des dispositifs
     */
    public function clearDispositifs()
    {
        Cache::forget('referentiels:dispositifs.all');
    }

    /**
     * Invalider le cache des organismes
     */
    public function clearOrganismes()
    {
        Cache::forget('referentiels:organismes.all');
    }

    /**
     * Invalider le cache des communes
     */
    public function clearCommunes()
    {
        Cache::forget('referentiels:communes.all');
    }

    /**
     * Invalider le cache des rôles
     */
    public function clearRoles()
    {
        Cache::forget('referentiels:roles.all');
    }

    /**
     * Invalider les statistiques
     */
    public function clearStatistics()
    {
        Cache::forget('statistics:micro_projets.count');
        Cache::forget('statistics:promoteurs.count');
        Cache::forget('statistics:personnels.count');
    }

    /**
     * Invalider tout le cache des référentiels
     */
    public function clearAll()
    {
        $this->clearSecteurs();
        $this->clearAgences();
        $this->clearGuichets();
        $this->clearDispositifs();
        $this->clearOrganismes();
        $this->clearCommunes();
        $this->clearRoles();
        $this->clearStatistics();
    }
}
