<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Services\AejApiService;
use App\Http\Resources\TypePieceIdentiteResource;
use App\Http\Resources\SituationMatrimonialeResource;
use App\Http\Resources\SecteurResource;
use App\Http\Resources\SousSecteurResource;
use App\Http\Resources\NiveauEtudeResource;
use App\Http\Resources\AgenceRegionaleResource;
use App\Http\Resources\ProjetParameterResource;
use App\Http\Resources\SexeResource;
use App\Http\Resources\LieuHabitationResource;
use App\Http\Resources\PaysResource;
use App\Http\Resources\TypeSituationHandicapResource;
use App\Http\Resources\CommuneResource;
use App\Http\Resources\DivisionRegionaleResource;
use App\Http\Resources\VilleResource;
use App\Exceptions\AejApiException;

class AejApiController extends Controller
{
    public function __construct(
        protected AejApiService $aejApiService
    ) {}

    public function getTypesPiecesIdentites(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getTypesPiecesIdentites();
            return new JsonResponse([
                'message' => 'Types pièces identités retrieved successfully',
                'data' => TypePieceIdentiteResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching types pièces identités',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getSituationsMatrimoniale(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getSituationsMatrimoniale();
            return new JsonResponse([
                'message' => 'Situations matrimoniales retrieved successfully',
                'data' => SituationMatrimonialeResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching situations matrimoniales',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getSecteurs(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getSecteurs();
            return new JsonResponse([
                'message' => 'Secteurs retrieved successfully',
                'data' => SecteurResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching secteurs',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getSousSecteurs(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getSousSecteurs();
            return new JsonResponse([
                'message' => 'Sous-secteurs retrieved successfully',
                'data' => SousSecteurResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching sous-secteurs',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getNiveauxEtudes(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getNiveauxEtudes();
            return new JsonResponse([
                'message' => 'Niveaux études retrieved successfully',
                'data' => NiveauEtudeResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching niveaux études',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getAgencesRegionales(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getAgencesRegionales();
            return new JsonResponse([
                'message' => 'Agences régionales retrieved successfully',
                'data' => AgenceRegionaleResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching agences régionales',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getAllReferentiels(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getAllReferentiels();
            return new JsonResponse([
                'message' => 'All referentiels retrieved successfully',
                'data' => [
                    'sexes' => SexeResource::collection($data['sexes']),
                    'niveaux_etudes' => NiveauEtudeResource::collection($data['niveaux_etudes']),
                    'types_pieces_identites' => TypePieceIdentiteResource::collection($data['types_pieces_identites']),
                    'situations_matrimoniale' => SituationMatrimonialeResource::collection($data['situations_matrimoniale']),
                    'situations_handicaps' => TypeSituationHandicapResource::collection($data['situations_handicaps']),
                    'secteurs' => SecteurResource::collection($data['secteurs']),
                    'sous_secteurs' => SousSecteurResource::collection($data['sous_secteurs']),
                    'lieu_habitations' => LieuHabitationResource::collection($data['lieu_habitations']),
                    'agences_regionales' => AgenceRegionaleResource::collection($data['agences_regionales']),
                    'pays' => PaysResource::collection($data['pays']),
                    'division_regionale' => DivisionRegionaleResource::collection($data['division_regionale']),
                    'villes' => VilleResource::collection($data['villes']),
                    'communes' => CommuneResource::collection($data['communes']),
                ],
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching all referentiels',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getSexes(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getSexes();
            return new JsonResponse([
                'message' => 'Sexes retrieved successfully',
                'data' => SexeResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching sexes',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getLieuHabitations(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getLieuHabitations();
            return new JsonResponse([
                'message' => 'Lieu habitations retrieved successfully',
                'data' => LieuHabitationResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching lieu habitations',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getPays(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getPays();
            return new JsonResponse([
                'message' => 'Pays retrieved successfully',
                'data' => PaysResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching pays',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getSituationsHandicaps(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getSituationsHandicaps();
            return new JsonResponse([
                'message' => 'Situations handicaps retrieved successfully',
                'data' => TypeSituationHandicapResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching situations handicaps',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getCommunes(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getCommunes();
            return new JsonResponse([
                'message' => 'Communes retrieved successfully',
                'data' => CommuneResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching communes',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getDivisionRegionale(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getDivisionRegionale();
            return new JsonResponse([
                'message' => 'Division regionale retrieved successfully',
                'data' => DivisionRegionaleResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching division regionale',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function getVilles(): JsonResponse
    {
        try {
            $data = $this->aejApiService->getVilles();
            return new JsonResponse([
                'message' => 'Villes retrieved successfully',
                'data' => VilleResource::collection($data),
            ], 200);
        } catch (AejApiException $e) {
            return new JsonResponse([
                'message' => 'Error fetching villes',
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function clearCache(): JsonResponse
    {
        try {
            $this->aejApiService->clearCache();
            return new JsonResponse([
                'message' => 'AEJ API cache cleared successfully',
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error clearing cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
