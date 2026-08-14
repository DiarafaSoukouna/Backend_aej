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

    public $tries = 1;
    public $timeout = 300;

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
            $model = TypePieceIdentite::find($item->id);
            if (!$model) {
                $model = new TypePieceIdentite();
                $model->id = $item->id;
            }
            $model->libelle = $item->libelle;
            $model->description = $item->description;
            $model->actif = $item->actif;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Types pièces identités synchronized successfully');
    }

    protected function syncSituationsMatrimoniale(AejApiService $service): void
    {
        $data = $service->getSituationsMatrimoniale();
        
        foreach ($data as $item) {
            $model = SituationMatrimoniale::find($item->id);
            if (!$model) {
                $model = new SituationMatrimoniale();
                $model->id = $item->id;
            }
            $model->libelle = $item->libelle;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Situations matrimoniales synchronized successfully');
    }

    protected function syncSecteurs(AejApiService $service): void
    {
        $data = $service->getSecteurs();
        
        foreach ($data as $item) {
            $model = Secteur::find($item->id);
            if (!$model) {
                $model = new Secteur();
                $model->id = $item->id;
            }
            $model->libelle = $item->libelle;
            $model->nom = $item->nom;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Secteurs synchronized successfully');
    }

    protected function syncSousSecteurs(AejApiService $service): void
    {
        $data = $service->getSousSecteurs();

        foreach ($data as $item) {
            $model = SousSecteur::find($item->id);
            if (!$model) {
                $model = new SousSecteur();
                $model->id = $item->id;
            }
            $model->secteur_id = $item->secteur_id ?? null;
            $model->libelle = $item->libelle;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Sous-secteurs synchronized successfully');
    }

    protected function syncNiveauxEtudes(AejApiService $service): void
    {
        $data = $service->getNiveauxEtudes();
        
        foreach ($data as $item) {
            $model = NiveauEtude::find($item->id);
            if (!$model) {
                $model = new NiveauEtude();
                $model->id = $item->id;
            }
            $model->libelle = $item->libelle;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Niveaux études synchronized successfully');
    }

    protected function syncAgencesRegionales(AejApiService $service): void
    {
        $data = $service->getAgencesRegionales();
        
        foreach ($data as $item) {
            $model = AgenceRegionale::find($item->id);
            if (!$model) {
                $model = new AgenceRegionale();
                $model->id = $item->id;
            }
            $model->code = $item->code;
            $model->nom = $item->nom;
            $model->latitude = $item->latitude ?? null;
            $model->longitude = $item->longitude ?? null;
            $model->contact = $item->contact ?? null;
            $model->localisation = $item->localisation ?? null;
            $model->adresse = $item->adresse ?? null;
            $model->telephone = $item->telephone ?? null;
            $model->email = $item->email ?? null;
            $model->chef_agence_id = $item->chef_agence_id ?? null;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Agences régionales synchronized successfully');
    }

    protected function syncSexes(AejApiService $service): void
    {
        $data = $service->getSexes();
        
        foreach ($data as $item) {
            $model = Sexe::find($item->id);
            if (!$model) {
                $model = new Sexe();
                $model->id = $item->id;
            }
            $model->libelle = $item->libelle;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Sexes synchronized successfully');
    }

    protected function syncLieuHabitations(AejApiService $service): void
    {
        $data = $service->getLieuHabitations();

        foreach ($data as $item) {
            $model = LieuHabitation::find($item->id);
            if (!$model) {
                $model = new LieuHabitation();
                $model->id = $item->id;
            }
            $model->nom = $item->nom;
            $model->ville_id = $item->ville_id ?? null;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Lieu habitations synchronized successfully');
    }

    protected function syncPays(AejApiService $service): void
    {
        $data = $service->getPays();
        
        foreach ($data as $item) {
            $model = Pays::find($item->id);
            if (!$model) {
                $model = new Pays();
                $model->id = $item->id;
            }
            $model->code_iso = $item->code_iso;
            $model->nom = $item->nom;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Pays synchronized successfully');
    }

    protected function syncSituationsHandicaps(AejApiService $service): void
    {
        $data = $service->getSituationsHandicaps();
        
        foreach ($data as $item) {
            $model = TypeSituationHandicap::find($item->id);
            if (!$model) {
                $model = new TypeSituationHandicap();
                $model->id = $item->id;
            }
            $model->libelle = $item->libelle;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Situations handicaps synchronized successfully');
    }

    protected function syncCommunes(AejApiService $service): void
    {
        $data = $service->getCommunes();

        foreach ($data as $item) {
            $model = Commune::find($item->id);
            if (!$model) {
                $model = new Commune();
                $model->id = $item->id;
            }
            $model->nom = $item->nom;
            $model->ville_id = $item->ville_id ?? null;
            $model->divisionregionaleaej_id = $item->divisionregionaleaej_id ?? null;
            $model->guichetemploi_id = $item->guichetemploi_id ?? null;
            $model->code = $item->code ?? null;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Communes synchronized successfully');
    }

    protected function syncDivisionRegionale(AejApiService $service): void
    {
        $data = $service->getDivisionRegionale();
        
        foreach ($data as $item) {
            $model = DivisionRegionale::find($item->id);
            if (!$model) {
                $model = new DivisionRegionale();
                $model->id = $item->id;
            }
            $model->code = $item->code;
            $model->nom = $item->nom;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Division regionale synchronized successfully');
    }

    protected function syncVilles(AejApiService $service): void
    {
        $data = $service->getVilles();

        foreach ($data as $item) {
            $model = Ville::find($item->id);
            if (!$model) {
                $model = new Ville();
                $model->id = $item->id;
            }
            $model->departement_id = $item->departement_id ?? null;
            $model->code = $item->code ?? null;
            $model->nom = $item->nom;
            $model->synced_at = now();
            $model->save();
        }

        Log::info('Villes synchronized successfully');
    }

    protected function syncAll(AejApiService $service): void
    {
        // Sync parent tables first
        $this->syncTypesPiecesIdentites($service);
        $this->syncSituationsMatrimoniale($service);
        $this->syncSituationsHandicaps($service);
        $this->syncSecteurs($service);
        $this->syncNiveauxEtudes($service);
        $this->syncAgencesRegionales($service);
        $this->syncSexes($service);
        $this->syncPays($service);
        $this->syncDivisionRegionale($service);
        $this->syncVilles($service);
        
        // Sync dependent tables after parents
        $this->syncSousSecteurs($service);
        $this->syncCommunes($service);
        $this->syncLieuHabitations($service);

        Log::info('All AEJ referentiels synchronized successfully');
    }
}
