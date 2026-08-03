<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Services\AejApiService;

use App\Models\TypePieceIdentite;
use App\Models\SituationMatrimoniale;
use App\Models\Secteur;
use App\Models\SousSecteur;
use App\Models\NiveauEtude;
use App\Models\AgenceRegionale;
use App\Models\Sexe;
use App\Models\LieuHabitation;
use App\Models\Pays;
use App\Models\TypeSituationHandicap;
use App\Models\Commune;
use App\Models\DivisionRegionale;
use App\Models\Ville;

class SyncAejReferentielsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $referentiel = 'all'
    ) {}

    public function handle(AejApiService $aejApiService): void
    {
        try {
            match ($this->referentiel) {
                'types_pieces_identites' => $this->syncTypesPiecesIdentites($aejApiService),
                'situations_matrimoniale' => $this->syncSituationsMatrimoniale($aejApiService),
                'situations_handicaps' => $this->syncSituationsHandicaps($aejApiService),
                'secteurs' => $this->syncSecteurs($aejApiService),
                'sous_secteurs' => $this->syncSousSecteurs($aejApiService),
                'niveaux_etudes' => $this->syncNiveauxEtudes($aejApiService),
                'agences_regionales' => $this->syncAgencesRegionales($aejApiService),
                'sexes' => $this->syncSexes($aejApiService),
                'lieu_habitations' => $this->syncLieuHabitations($aejApiService),
                'pays' => $this->syncPays($aejApiService),
                'division_regionale' => $this->syncDivisionRegionale($aejApiService),
                'villes' => $this->syncVilles($aejApiService),
                'communes' => $this->syncCommunes($aejApiService),
                'all' => $this->syncAll($aejApiService),
                default => Log::warning("Unknown referentiel: {$this->referentiel}"),
            };

            $aejApiService->clearCache();

        } catch (\Exception $e) {
            Log::error('AEJ Sync Job Error', [
                'referentiel' => $this->referentiel,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function syncTypesPiecesIdentites(AejApiService $service): void
    {
        $data = $service->getTypesPiecesIdentites();
        
        foreach ($data as $item) {
            TypePieceIdentite::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'libelle' => $item->libelle,
                    'description' => $item->description,
                    'actif' => $item->actif,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Types pièces identités synchronized successfully');
    }

    protected function syncSituationsMatrimoniale(AejApiService $service): void
    {
        $data = $service->getSituationsMatrimoniale();
        
        foreach ($data as $item) {
            SituationMatrimoniale::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'libelle' => $item->libelle,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Situations matrimoniales synchronized successfully');
    }

    protected function syncSecteurs(AejApiService $service): void
    {
        $data = $service->getSecteurs();
        
        foreach ($data as $item) {
            Secteur::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'libelle' => $item->libelle,
                    'nom' => $item->nom,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Secteurs synchronized successfully');
    }

    protected function syncSousSecteurs(AejApiService $service): void
    {
        $data = $service->getSousSecteurs();
        
        foreach ($data as $item) {
            SousSecteur::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'libelle' => $item->libelle,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Sous-secteurs synchronized successfully');
    }

    protected function syncNiveauxEtudes(AejApiService $service): void
    {
        $data = $service->getNiveauxEtudes();
        
        foreach ($data as $item) {
            NiveauEtude::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'libelle' => $item->libelle,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Niveaux études synchronized successfully');
    }

    protected function syncAgencesRegionales(AejApiService $service): void
    {
        $data = $service->getAgencesRegionales();
        
        foreach ($data as $item) {
            AgenceRegionale::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'code' => $item->code,
                    'nom' => $item->nom,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Agences régionales synchronized successfully');
    }

    protected function syncSexes(AejApiService $service): void
    {
        $data = $service->getSexes();
        
        foreach ($data as $item) {
            Sexe::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'libelle' => $item->libelle,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Sexes synchronized successfully');
    }

    protected function syncLieuHabitations(AejApiService $service): void
    {
        $data = $service->getLieuHabitations();
        
        foreach ($data as $item) {
            LieuHabitation::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'nom' => $item->nom,
                    'ville_id' => $item->ville_id,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Lieu habitations synchronized successfully');
    }

    protected function syncPays(AejApiService $service): void
    {
        $data = $service->getPays();
        
        foreach ($data as $item) {
            Pays::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'code_iso' => $item->code_iso,
                    'nom' => $item->nom,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Pays synchronized successfully');
    }

    protected function syncSituationsHandicaps(AejApiService $service): void
    {
        $data = $service->getSituationsHandicaps();
        
        foreach ($data as $item) {
            TypeSituationHandicap::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'code' => $item->code,
                    'libelle' => $item->libelle,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Situations handicaps synchronized successfully');
    }

    protected function syncCommunes(AejApiService $service): void
    {
        $data = $service->getCommunes();
        
        foreach ($data as $item) {
            Commune::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'nom' => $item->nom,
                    'ville_id' => $item->ville_id,
                    'divisionregionaleaej_id' => $item->divisionregionaleaej_id,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Communes synchronized successfully');
    }

    protected function syncDivisionRegionale(AejApiService $service): void
    {
        $data = $service->getDivisionRegionale();
        
        foreach ($data as $item) {
            DivisionRegionale::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'code' => $item->code,
                    'nom' => $item->nom,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Division regionale synchronized successfully');
    }

    protected function syncVilles(AejApiService $service): void
    {
        $data = $service->getVilles();
        
        foreach ($data as $item) {
            Ville::updateOrCreate(
                ['external_id' => $item->id],
                [
                    'departement_id' => $item->departement_id,
                    'code' => $item->code,
                    'nom' => $item->nom,
                    'synced_at' => now(),
                ]
            );
        }

        Log::info('Villes synchronized successfully');
    }

    protected function syncAll(AejApiService $service): void
    {
        $this->syncTypesPiecesIdentites($service);
        $this->syncSituationsMatrimoniale($service);
        $this->syncSituationsHandicaps($service);
        $this->syncSecteurs($service);
        $this->syncSousSecteurs($service);
        $this->syncNiveauxEtudes($service);
        $this->syncAgencesRegionales($service);
        $this->syncSexes($service);
        $this->syncLieuHabitations($service);
        $this->syncPays($service);
        $this->syncDivisionRegionale($service);
        $this->syncVilles($service);
        $this->syncCommunes($service);

        Log::info('All AEJ referentiels synchronized successfully');
    }
}
